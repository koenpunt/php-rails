<?php 

namespace PHPRails;
	
#require 'fileutils';
require 'phprails/engine';
	
# In Rails 3.0, a Rails::Application object was introduced which is nothing more than
# an Engine but with the responsibility of coordinating the whole boot process.
#
# == Initialization
#
# Rails::Application is responsible for executing all railties and engines
# initializers. It also executes some bootstrap initializers (check
# Rails::Application::Bootstrap) and finishing initializers, after all the others
# are executed (check Rails::Application::Finisher).
#
# == Configuration
#
# Besides providing the same configuration as Rails::Engine and Rails::Railtie,
# the application object has several specific configurations, for example
# "allow_concurrency", "cache_classes", "consider_all_requests_local", "filter_parameters",
# "logger" and so forth.
#
# Check Rails::Application::Configuration to see them all.
#
# == Routes
#
# The application object is also responsible for holding the routes and reloading routes
# whenever the files change in development.
#
# == Middlewares
#
# The Application is also responsible for building the middleware stack.
#
# == Booting process
#
# The application is also responsible for setting up and executing the booting
# process. From the moment you require "config/application.rb" in your app,
# the booting process goes like this:
#
#   1)  require "config/boot.rb" to setup load paths
#   2)  require railties and engines
#   3)  Define Rails.application as "class MyApp::Application < Rails::Application"
#   4)  Run config.before_configuration callbacks
#   5)  Load config/environments/ENV.rb
#   6)  Run config.before_initialize callbacks
#   7)  Run Railtie#initializer defined by railties, engines and application.
#       One by one, each engine sets up its load paths, routes and runs its config/initializers/* files.
#   9)  Custom Railtie#initializers added by railties, engines and applications are executed
#   10) Build the middleware stack and run to_prepare callbacks
#   11) Run config.before_eager_load and eager_load if cache classes is true
#   12) Run config.after_initialize callbacks
#
class Application extends Engine{
	#autoload :Bootstrap,      'rails/application/bootstrap'
	#autoload :Configuration,  'rails/application/configuration'
	#autoload :Finisher,       'rails/application/finisher'
	#autoload :Railties,       'rails/application/railties'
	#autoload :RoutesReloader, 'rails/application/routes_reloader'

	protected $initialized = null;
	
	protected $reloaders = null;
	
	public function __construct(){
		parent::__construct();
		$this->initialized = false;
		$this->reloaders   = array();
		
		if(get_class($this) !== __CLASS__){
			if( \PHPRails::$application ){
				throw new Exception("You cannot have more than one PHPRails\\Application");
			}
			\PHPRails::$application = base.instance
			\PHPRails::$application->add_lib_to_load_path_();
			\ActiveSupport::run_load_hooks('before_configuration', base.instance);
		}
	}

	#attr_accessor :assets, :sandbox, :queue_consumer
	#alias_method :sandbox?, :sandbox
	#attr_reader :reloaders
	#attr_writer :queue

	#delegate :default_url_options, :default_url_options=, :to => :routes

	# This method is called just after an application inherits from Rails::Application,
	# allowing the developer to load classes in lib and use them during application
	# configuration.
	#
	#   class MyApplication < Rails::Application
	#     require "my_backend" # in lib/my_backend
	#     config.i18n.backend = MyBackend
	#   end
	#
	# Notice this method takes into consideration the default root path. So if you
	# are changing config.root inside your application definition or having a custom
	# Rails application, you will need to add lib to $LOAD_PATH on your own in case
	# you need to load files in lib/ during the application configuration as well.
	public function add_lib_to_load_path__(){ #:nodoc:
		# path = config.root.join('lib').to_s
		# $LOAD_PATH.unshift(path) if File.exists?(path)
		$path = RFile::join($this->config->root, 'lib');
		if( RFile::exists($path) ){
			set_include_path(
				$path . PS .
				get_include_path()
			);
		}
	}

	public function require_environment_(){ #:nodoc:
		#environment = paths["config/environment"].existent.first
		#require environment if environment
	}

	# Reload application routes regardless if they changed or not.
	public function reload_routes_(){
		$this->routes_reloader()->reload_();
	}

	public function routes_reloader(){ #:nodoc:
		$this->routes_reloader = $this->routes_reloader ?: new RoutesReloader();
		return $this->routes_reloader;
	}

	# Returns an array of file paths appended with a hash of directories-extensions
	# suitable for ActiveSupport::FileUpdateChecker API.
	public function watchable_args(){
		list($files, $dirs) = array($this->config()->watchable_files, $this->config()->watchable_dirs);
		
		foreach(\ActiveSupport\Dependencies::$autoload_paths as $path){
			$dirs[(string)$path] = array('rb');
		}
		
		return array($files, $dirs);
	}

	# Initialize the application passing the given group. By default, the
	# group is :default but sprockets precompilation passes group equals
	# to assets if initialize_on_precompile is false to avoid booting the
	# whole app.
	public function initialize_($group='default'){ #:nodoc:
		if($this->initialized){
			throw new RuntimeException("Application has been already initialized.");
		}
		run_initializers($group, $this);
		$this->initialized = true;
		return $this;
	}

	public function initialized__(){
		return $this->initialized;
	}

	# Load the application and its railties tasks and invoke the registered hooks.
	# Check <tt>Rails::Railtie.rake_tasks</tt> for more info.
	public function load_tasks($app=self){
		$this->initialize_tasks();
		parent::load_tasks($app);
		return $this;
	}

	# Load the application console and invoke the registered hooks.
	# Check <tt>Rails::Railtie.console</tt> for more info.
	public function load_console($app=self){
		$this->initialize_console();
		parent::load_console($app);
		return $this;
	}

	# Stores some of the Rails initial environment parameters which
	# will be used by middlewares and engines to configure themselves.
	public function env_config(){
		$this->env_config = $this->env_config ?: array_merge(parent::env_config(), array(
			"action_dispatch.parameter_filter" => $this->config()->filter_parameters,
			"action_dispatch.secret_token" => $this->config()->secret_token,
			"action_dispatch.show_exceptions" => $this->config()->action_dispatch.show_exceptions,
			"action_dispatch.show_detailed_exceptions" => $this->config()->consider_all_requests_local,
			"action_dispatch.logger" => PHPRails::$logger,
			"action_dispatch.backtrace_cleaner" => PHPRails::$backtrace_cleaner
		));
		return $this->env_config;
	}

	# Returns the ordered railties for this application considering railties_order.
	public function ordered_railties(){ #:nodoc:
		$this->ordered_railties = $this->ordered_railties ?: function(){
			$order = array_map(function($railtie){
				if( $railtie == 'main_app' ){
					return $this;
				}elseif( method_exists($railtie, 'instance')){
					return $railtie->instance();
				}else{
					return $railtie;
				}
			}, $this->config()->railties_order);

			$all = array_diff($this->railties->all() - $order);
			if(! array_search($this, $all) ){
				array_push($all, $this);
			}
			if( array_search('all', $order) == false ){
				array_push($order, 'all');
			}

			$index = array_search('all', $order);
			$order[$index] = $this->all();
			return \PHPRails\array_flatten(array_reverse($order));
		};
	}

	public function initializers(){ #:nodoc:
		return array( 
			Bootstrap::initializers_for(self),
			super::initializers(),
			Finisher::initializers_for(self)
		);
	}

	public function config(){ #:nodoc:
		$this->config = $this->config ?: new Configuration($this->find_root_with_flag("config.ru", \RDir::pwd()));
		return $this->config;
	}

	public function queue(){ #:nodoc:
		$this->queue = $this->queue ?: $this->build_queue();
		return $this->queue;
	}

	public function build_queue(){ # :nodoc:
		$queue = $this->config->queue;
		return new $queue;
	}

	public function to_app(){
		# self
		return $this;
	}

	public function helpers_paths(){ #:nodoc:
		$this->config()->helpers_paths;
	}

	public function call($env){
		$_ENV["ORIGINAL_FULLPATH"] = $this->build_original_fullpath($env);
		parent::call($env);
	}

	#protected

	#alias :build_middleware_stack :app

	protected function reload_dependencies__(){
		return; #config.reload_classes_only_on_change != true || reloaders.map(&:updated?).any?
	}

	#def default_middleware_stack
	#  ActionDispatch::MiddlewareStack.new.tap do |middleware|
	#    if rack_cache = config.action_controller.perform_caching && config.action_dispatch.rack_cache
	#      require "action_dispatch/http/rack_cache"
	#      middleware.use ::Rack::Cache, rack_cache
	#    end
    #
	#    if config.force_ssl
	#      middleware.use ::ActionDispatch::SSL, config.ssl_options
	#    end
    #
	#    if config.action_dispatch.x_sendfile_header.present?
	#      middleware.use ::Rack::Sendfile, config.action_dispatch.x_sendfile_header
	#    end
    #
	#    if config.serve_static_assets
	#      middleware.use ::ActionDispatch::Static, paths["public"].first, config.static_cache_control
	#    end
    #
	#    middleware.use ::Rack::Lock unless config.allow_concurrency
	#    middleware.use ::Rack::Runtime
	#    middleware.use ::Rack::MethodOverride
	#    middleware.use ::ActionDispatch::RequestId
	#    middleware.use ::Rails::Rack::Logger, config.log_tags # must come after Rack::MethodOverride to properly log overridden methods
	#    middleware.use ::ActionDispatch::ShowExceptions, config.exceptions_app || ActionDispatch::PublicExceptions.new(Rails.public_path)
	#    middleware.use ::ActionDispatch::DebugExceptions
	#    middleware.use ::ActionDispatch::RemoteIp, config.action_dispatch.ip_spoofing_check, config.action_dispatch.trusted_proxies
    #
	#    unless config.cache_classes
	#      app = self
	#      middleware.use ::ActionDispatch::Reloader, lambda { app.reload_dependencies? }
	#    end
    #
	#    middleware.use ::ActionDispatch::Callbacks
	#    middleware.use ::ActionDispatch::Cookies
    #
	#    if config.session_store
	#      if config.force_ssl && !config.session_options.key?(:secure)
	#        config.session_options[:secure] = true
	#      end
	#      middleware.use config.session_store, config.session_options
	#      middleware.use ::ActionDispatch::Flash
	#    end
    #
	#    middleware.use ::ActionDispatch::ParamsParser
	#    middleware.use ::ActionDispatch::Head
	#    middleware.use ::Rack::ConditionalGet
	#    middleware.use ::Rack::ETag, "no-cache"
    #
	#    if config.action_dispatch.best_standards_support
	#      middleware.use ::ActionDispatch::BestStandardsSupport, config.action_dispatch.best_standards_support
	#    end
	#  end
	#end

	protected function initialize_tasks(){ #:nodoc:
		$class = get_class($this);
		$class::rake_tasks(function(){
			\PHPRails::import("rails/tasks");
			#task :environment do
			#	$rails_rake_task = true
			#	require_environment!
			#end
		});
	}

	#def initialize_console #:nodoc:
	#  require "pp"
	#  require "rails/console/app"
	#  require "rails/console/helpers"
	#end

	protected function build_original_fullpath($env){
		$path_info    = $_SERVER["PATH_INFO"];
		$query_string = $_SERVER["QUERY_STRING"];
		$script_name  = $_SERVER["SCRIPT_NAME"];
		
		if( !empty($query_string) ){
			return "{$script_name}{$path_info}?{$query_string}";
		}else{
			return "{$script_name}{$path_info}";
		}
	}
}
