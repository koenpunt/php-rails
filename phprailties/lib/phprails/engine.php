<?php 

namespace PHPRails;

#\PHPRails::import('rails/railtie');
#\PHPRails::import('active_support/core_ext/module/delegation');
#\PHPRails::import('pathname');
#\PHPRails::import('rbconfig');
#\PHPRails::import('rails/engine/railties');

# <tt>Rails::Engine</tt> allows you to wrap a specific Rails application or subset of
# functionality and share it with other applications. Since Rails 3.0, every
# <tt>Rails::Application</tt> is just an engine, which allows for simple
# feature and application sharing.
#
# Any <tt>Rails::Engine</tt> is also a <tt>Rails::Railtie</tt>, so the same
# methods (like <tt>rake_tasks</tt> and +generators+) and configuration
# options that are available in railties can also be used in engines.
#
# == Creating an Engine
#
# In Rails versions prior to 3.0, your gems automatically behaved as engines, however,
# this coupled Rails to Rubygems. Since Rails 3.0, if you want a gem to automatically
# behave as an engine, you have to specify an +Engine+ for it somewhere inside
# your plugin's +lib+ folder (similar to how we specify a +Railtie+):
#
#   # lib/my_engine.rb
#   module MyEngine
#     class Engine < Rails::Engine
#     end
#   end
#
# Then ensure that this file is loaded at the top of your <tt>config/application.rb</tt>
# (or in your +Gemfile+) and it will automatically load models, controllers and helpers
# inside +app+, load routes at <tt>config/routes.rb</tt>, load locales at
# <tt>config/locales/*</tt>, and load tasks at <tt>lib/tasks/*</tt>.
#
# == Configuration
#
# Besides the +Railtie+ configuration which is shared across the application, in a
# <tt>Rails::Engine</tt> you can access <tt>autoload_paths</tt>, <tt>eager_load_paths</tt>
# and <tt>autoload_once_paths</tt>, which, differently from a <tt>Railtie</tt>, are scoped to
# the current engine.
#
# Example:
#
#   class MyEngine < Rails::Engine
#     # Add a load path for this specific Engine
#     config.autoload_paths << File.expand_path("../lib/some/path", __FILE__)
#
#     initializer "my_engine.add_middleware" do |app|
#       app.middleware.use MyEngine::Middleware
#     end
#   end
#
# == Generators
#
# You can set up generators for engines with <tt>config.generators</tt> method:
#
#   class MyEngine < Rails::Engine
#     config.generators do |g|
#       g.orm             :active_record
#       g.template_engine :erb
#       g.test_framework  :test_unit
#     end
#   end
#
# You can also set generators for an application by using <tt>config.app_generators</tt>:
#
#   class MyEngine < Rails::Engine
#     # note that you can also pass block to app_generators in the same way you
#     # can pass it to generators method
#     config.app_generators.orm :datamapper
#   end
#
# == Paths
#
# Since Rails 3.0, applications and engines have more flexible path configuration (as
# opposed to the previous hardcoded path configuration). This means that you are not
# required to place your controllers at <tt>app/controllers</tt>, but in any place
# which you find convenient.
#
# For example, let's suppose you want to place your controllers in <tt>lib/controllers</tt>.
# You can set that as an option:
#
#   class MyEngine < Rails::Engine
#     paths["app/controllers"] = "lib/controllers"
#   end
#
# You can also have your controllers loaded from both <tt>app/controllers</tt> and
# <tt>lib/controllers</tt>:
#
#   class MyEngine < Rails::Engine
#     paths["app/controllers"] << "lib/controllers"
#   end
#
# The available paths in an engine are:
#
#   class MyEngine < Rails::Engine
#     paths["app"]                 # => ["app"]
#     paths["app/controllers"]     # => ["app/controllers"]
#     paths["app/helpers"]         # => ["app/helpers"]
#     paths["app/models"]          # => ["app/models"]
#     paths["app/views"]           # => ["app/views"]
#     paths["lib"]                 # => ["lib"]
#     paths["lib/tasks"]           # => ["lib/tasks"]
#     paths["config"]              # => ["config"]
#     paths["config/initializers"] # => ["config/initializers"]
#     paths["config/locales"]      # => ["config/locales"]
#     paths["config/routes"]       # => ["config/routes.rb"]
#   end
#
# The <tt>Application</tt> class adds a couple more paths to this set. And as in your
# <tt>Application</tt>, all folders under +app+ are automatically added to the load path.
# If you have an <tt>app/observers</tt> folder for example, it will be added by default.
#
# == Endpoint
#
# An engine can be also a rack application. It can be useful if you have a rack application that
# you would like to wrap with +Engine+ and provide some of the +Engine+'s features.
#
# To do that, use the +endpoint+ method:
#
#   module MyEngine
#     class Engine < Rails::Engine
#       endpoint MyRackApplication
#     end
#   end
#
# Now you can mount your engine in application's routes just like that:
#
#   MyRailsApp::Application.routes.draw do
#     mount MyEngine::Engine => "/engine"
#   end
#
# == Middleware stack
#
# As an engine can now be a rack endpoint, it can also have a middleware
# stack. The usage is exactly the same as in <tt>Application</tt>:
#
#   module MyEngine
#     class Engine < Rails::Engine
#       middleware.use SomeMiddleware
#     end
#   end
#
# == Routes
#
# If you don't specify an endpoint, routes will be used as the default
# endpoint. You can use them just like you use an application's routes:
#
#   # ENGINE/config/routes.rb
#   MyEngine::Engine.routes.draw do
#     get "/" => "posts#index"
#   end
#
# == Mount priority
#
# Note that now there can be more than one router in your application, and it's better to avoid
# passing requests through many routers. Consider this situation:
#
#   MyRailsApp::Application.routes.draw do
#     mount MyEngine::Engine => "/blog"
#     get "/blog/omg" => "main#omg"
#   end
#
# +MyEngine+ is mounted at <tt>/blog</tt>, and <tt>/blog/omg</tt> points to application's
# controller. In such a situation, requests to <tt>/blog/omg</tt> will go through +MyEngine+,
# and if there is no such route in +Engine+'s routes, it will be dispatched to <tt>main#omg</tt>.
# It's much better to swap that:
#
#   MyRailsApp::Application.routes.draw do
#     get "/blog/omg" => "main#omg"
#     mount MyEngine::Engine => "/blog"
#   end
#
# Now, +Engine+ will get only requests that were not handled by +Application+.
#
# == Engine name
#
# There are some places where an Engine's name is used:
#
# * routes: when you mount an Engine with <tt>mount(MyEngine::Engine => '/my_engine')</tt>,
#   it's used as default :as option
# * some of the rake tasks are based on engine name, e.g. <tt>my_engine:install:migrations</tt>,
#   <tt>my_engine:install:assets</tt>
#
# Engine name is set by default based on class name. For <tt>MyEngine::Engine</tt> it will be
# <tt>my_engine_engine</tt>. You can change it manually using the <tt>engine_name</tt> method:
#
#   module MyEngine
#     class Engine < Rails::Engine
#       engine_name "my_engine"
#     end
#   end
#
# == Isolated Engine
#
# Normally when you create controllers, helpers and models inside an engine, they are treated
# as if they were created inside the application itself. This means that all helpers and
# named routes from the application will be available to your engine's controllers as well.
#
# However, sometimes you want to isolate your engine from the application, especially if your engine
# has its own router. To do that, you simply need to call +isolate_namespace+. This method requires
# you to pass a module where all your controllers, helpers and models should be nested to:
#
#   module MyEngine
#     class Engine < Rails::Engine
#       isolate_namespace MyEngine
#     end
#   end
#
# With such an engine, everything that is inside the +MyEngine+ module will be isolated from
# the application.
#
# Consider such controller:
#
#   module MyEngine
#     class FooController < ActionController::Base
#     end
#   end
#
# If an engine is marked as isolated, +FooController+ has access only to helpers from +Engine+ and
# <tt>url_helpers</tt> from <tt>MyEngine::Engine.routes</tt>.
#
# The next thing that changes in isolated engines is the behavior of routes. Normally, when you namespace
# your controllers, you also need to do namespace all your routes. With an isolated engine,
# the namespace is applied by default, so you can ignore it in routes:
#
#   MyEngine::Engine.routes.draw do
#     resources :articles
#   end
#
# The routes above will automatically point to <tt>MyEngine::ArticlesController</tt>. Furthermore, you don't
# need to use longer url helpers like <tt>my_engine_articles_path</tt>. Instead, you should simply use
# <tt>articles_path</tt> as you would do with your application.
#
# To make that behavior consistent with other parts of the framework, an isolated engine also has influence on
# <tt>ActiveModel::Naming</tt>. When you use a namespaced model, like <tt>MyEngine::Article</tt>, it will normally
# use the prefix "my_engine". In an isolated engine, the prefix will be omitted in url helpers and
# form fields for convenience.
#
#   polymorphic_url(MyEngine::Article.new) # => "articles_path"
#
#   form_for(MyEngine::Article.new) do
#     text_field :title # => <input type="text" name="article[title]" id="article_title" />
#   end
#
# Additionally, an isolated engine will set its name according to namespace, so
# MyEngine::Engine.engine_name will be "my_engine". It will also set MyEngine.table_name_prefix
# to "my_engine_", changing the MyEngine::Article model to use the my_engine_articles table.
#
# == Using Engine's routes outside Engine
#
# Since you can now mount an engine inside application's routes, you do not have direct access to +Engine+'s
# <tt>url_helpers</tt> inside +Application+. When you mount an engine in an application's routes, a special helper is
# created to allow you to do that. Consider such a scenario:
#
#   # config/routes.rb
#   MyApplication::Application.routes.draw do
#     mount MyEngine::Engine => "/my_engine", :as => "my_engine"
#     get "/foo" => "foo#index"
#   end
#
# Now, you can use the <tt>my_engine</tt> helper inside your application:
#
#   class FooController < ApplicationController
#     def index
#       my_engine.root_url #=> /my_engine/
#     end
#   end
#
# There is also a <tt>main_app</tt> helper that gives you access to application's routes inside Engine:
#
#   module MyEngine
#     class BarController
#       def index
#         main_app.foo_path #=> /foo
#       end
#     end
#   end
#
# Note that the <tt>:as</tt> option given to mount takes the <tt>engine_name</tt> as default, so most of the time
# you can simply omit it.
#
# Finally, if you want to generate a url to an engine's route using
# <tt>polymorphic_url</tt>, you also need to pass the engine helper. Let's
# say that you want to create a form pointing to one of the engine's routes.
# All you need to do is pass the helper as the first element in array with
# attributes for url:
#
#   form_for([my_engine, @user])
#
# This code will use <tt>my_engine.user_path(@user)</tt> to generate the proper route.
#
# == Isolated engine's helpers
#
# Sometimes you may want to isolate engine, but use helpers that are defined for it.
# If you want to share just a few specific helpers you can add them to application's
# helpers in ApplicationController:
#
#   class ApplicationController < ActionController::Base
#     helper MyEngine::SharedEngineHelper
#   end
#
# If you want to include all of the engine's helpers, you can use #helper method on an engine's
# instance:
#
#   class ApplicationController < ActionController::Base
#     helper MyEngine::Engine.helpers
#   end
#
# It will include all of the helpers from engine's directory. Take into account that this does
# not include helpers defined in controllers with helper_method or other similar solutions,
# only helpers defined in the helpers directory will be included.
#
# == Migrations & seed data
#
# Engines can have their own migrations. The default path for migrations is exactly the same
# as in application: <tt>db/migrate</tt>
#
# To use engine's migrations in application you can use rake task, which copies them to
# application's dir:
#
#   rake ENGINE_NAME:install:migrations
#
# Note that some of the migrations may be skipped if a migration with the same name already exists
# in application. In such a situation you must decide whether to leave that migration or rename the
# migration in the application and rerun copying migrations.
#
# If your engine has migrations, you may also want to prepare data for the database in
# the <tt>db/seeds.rb</tt> file. You can load that data using the <tt>load_seed</tt> method, e.g.
#
#   MyEngine::Engine.load_seed
#
# == Loading priority
#
# In order to change engine's priority you can use +config.railties_order+ in main application.
# It will affect the priority of loading views, helpers, assets and all the other files
# related to engine or application.
#
# Example:
#
#   # load Blog::Engine with highest priority, followed by application and other railties
#   config.railties_order = [Blog::Engine, :main_app, :all]
#
class Engine extends Railtie{
	#autoload :Configuration, "rails/engine/configuration"
	#autoload :Railties,      "rails/engine/railties"

	public function load_generators($app=self){
		$this->initialize_generators();
		# railties.all { |r| r.load_generators(app) }
		\PHPRails\Generators::configure_($app->config()->generators);
		parent::load_generators($app);
		return $this;
	}

	#class << self
		
	#attr_accessor :called_from, :isolated
	#alias :isolated? :isolated
	#alias :engine_name :railtie_name

	public function __construct(){
		if(get_class($this) !== __CLASS__){
			if(!$this->abstract_railtie__()){
				$this->called_from = function(){
					# Remove the line number from backtraces making sure we don't leave anything behind
					#call_stack = caller.map { |p| p.sub(/:\d+.*/, '') }
					#File.dirname(call_stack.detect { |p| p !~ %r[railties[\w.-]*/lib/rails|rack[\w.-]*/lib/rack] })
				};
			}
		}
		parent::__construct();
	}

	
	#def inherited(base)
	#  unless base.abstract_railtie?
	#    base.called_from = begin
	#      # Remove the line number from backtraces making sure we don't leave anything behind
	#      call_stack = caller.map { |p| p.sub(/:\d+.*/, '') }
	#      File.dirname(call_stack.detect { |p| p !~ %r[railties[\w.-]*/lib/rails|rack[\w.-]*/lib/rack] })
	#    end
	#  end
    #
	#  super
	#end

	public function self_endpoint($endpoint = null){
		$this->endpoint = $this->endpoint ?: null;
		if( $endpoint ){
			$this->endpoint = $endpoint;
		}
		return $this->endpoint;
	}

	public function isolate_namespace($mod){
		$this->engine_name($this->generate_railtie_name($mod));

		self::$routes->default_scope = array('module' => \ActiveSupport\Inflector::underscore($mod->name) );
		self::$isolated = true;

		if( !method_exists($mod, 'railtie_namespace') ){
			list($name, $railtie) = array($engine_name, self);

			#mod.singleton_class.instance_eval do
			#	define_method(:railtie_namespace) { railtie }
            #
			#	unless mod.respond_to?(:table_name_prefix)
			#		define_method(:table_name_prefix) { "#{name}_" }
			#	end
            #
			#	unless mod.respond_to?(:use_relative_model_naming?)
			#		class_eval "def use_relative_model_naming?; true; end", __FILE__, __LINE__
			#	end
            #
			#	unless mod.respond_to?(:railtie_helpers_paths)
			#		define_method(:railtie_helpers_paths) { railtie.helpers_paths }
			#	end
            #
			#	unless mod.respond_to?(:railtie_routes_url_helpers)
			#		define_method(:railtie_routes_url_helpers) { railtie.routes_url_helpers }
			#	end
			#end
		}
	}

	# Finds engine with given path
	public function find($path){
		$expanded_path = \RFile::expand_path( (string)$path );
		\PHPRails\Engine\Railties::engines()->find(function($engine){
			return \RFile::expand_path((string)$engine->root()) == $expanded_path;
		});
	}

	#delegate :middleware, :root, :paths, :to => :config
	#delegate :engine_name, :isolated?, :to => "self.class"

	#def load_tasks(app=self)
	#	railties.all { |r| r.load_tasks(app) }
	#	super
	#	paths["lib/tasks"].existent.sort.each { |ext| load(ext) }
	#end

	#def load_console(app=self)
	#  railties.all { |r| r.load_console(app) }
	#  super
	#end

	#def eager_load!
	#  railties.all(&:eager_load!)
    #
	#  config.eager_load_paths.each do |load_path|
	#    matcher = /\A#{Regexp.escape(load_path)}\/(.*)\.rb\Z/
	#    Dir.glob("#{load_path}/**/*.rb").sort.each do |file|
	#      require_dependency file.sub(matcher, '\1')
	#    end
	#  end
	#end

	public function railties(){
		$class = new ReflectionClass(get_class($this).'\Railties');
		$this->railties = $this->railties ?: $class->newInstance($this->config());
	}

	public function helpers(){
		$this->helpers = $this->helpers ?: function(){
			$helpers = new Module();
			$all = \ActionController\Base::all_helpers_from_path($this->helpers_paths());
			foreach(\ActionController\Base::modules_for_helpers($all) as $mod){
				call_user_func(array($helpers, 'include'), $mod);
			}
			return $helpers;
		};
		return $this->helpers;
	}

	public function helpers_paths(){
	#	return paths["app/helpers"].existent
	}

	public function routes_url_helpers(){
		$this->routes()->url_helpers();
	}

	public function app(){
		$this->app = $this->app ?: function(){
			$this->config()->middleware = array_merge($this->default_middleware_stack(), $this->config()->middleware);
			$this->config()->middleware->build($endpoint);
		};
		return $this->app;
	}

	public function endpoint(){
		$class = get_class($this);
		return $class::$endpoint ?: $this->routes();
		#self.class.endpoint || routes
	}

	public function call($env){
		$this->app()->call(array_merge($env, $this-env_config()));
	}

	public function env_config(){
		$this->env_config = $this->env_config ?: array(
			'action_dispatch.routes' => $this->routes()
		);
	}

	public function routes(){
		$args = func_get_args();
		if(!$this->routes){
			$route_set = new \ActionDispatch\Routing\RouteSet();
			$this->routes = $route_set->tap(function($routes){
#				$routes->draw_paths()->concat( paths["config/routes"].paths);
			});
		}
			
		if( \PHPRails\block_given($args) ){
			$this->routes->append(new Closure());
		}
		return $this->routes;
	}

	public function ordered_railties(){
	#	railties.all + [self]
	}

	public function initializers(){
		$initializers = array();
		foreach($this->ordered_railties() as $r){
			if( $r == $this ){
				array_push($initializers, super);
			}else{
				array_push($initializers, $r->initializers());
			}
		}
		return $initializers;
	}

	public function config(){
		$this->config = $this->config ?: new Engine\Configuration($this->find_root_with_flag("lib"));
		return $this->config;
	}

	# Load data from db/seeds.rb file. It can be used in to load engines'
	# seeds, e.g.:
	#
	# Blog::Engine.load_seed
	public function load_seed(){
		#$seed_file = paths["db/seeds.rb"].existent.first
		#if( $seed_file ){
		#	load($seed_file);
		#}
	}

	# Add configured load paths to ruby load paths and remove duplicates.
	#initializer :set_load_path, :before => :bootstrap_hook do
	#  _all_load_paths.reverse_each do |path|
	#    $LOAD_PATH.unshift(path) if File.directory?(path)
	#  end
	#  $LOAD_PATH.uniq!
	#end

	# Set the paths from which Rails will automatically load source files,
	# and the load_once paths.
	#
	# This needs to be an initializer, since it needs to run once
	# per engine and get the engine as a block parameter
	#initializer :set_autoload_paths, :before => :bootstrap_hook do |app|
	#  ActiveSupport::Dependencies.autoload_paths.unshift(*_all_autoload_paths)
	#  ActiveSupport::Dependencies.autoload_once_paths.unshift(*_all_autoload_once_paths)
    #
	#  # Freeze so future modifications will fail rather than do nothing mysteriously
	#  config.autoload_paths.freeze
	#  config.eager_load_paths.freeze
	#  config.autoload_once_paths.freeze
	#end

	#initializer :add_routing_paths do |app|
	#  paths = self.paths["config/routes.rb"].existent
	#  external_paths = self.paths["config/routes"].paths
    #
	#  if routes? || paths.any?
	#    app.routes_reloader.paths.unshift(*paths)
	#    app.routes_reloader.route_sets << routes
	#    app.routes_reloader.external_routes.unshift(*external_paths)
	#  end
	#end

	# I18n load paths are a special case since the ones added
	# later have higher priority.
	#initializer :add_locales do
	#  config.i18n.railties_load_path.concat(paths["config/locales"].existent)
	#end

	#initializer :add_view_paths do
	#  views = paths["app/views"].existent
	#  unless views.empty?
	#    ActiveSupport.on_load(:action_controller){ prepend_view_path(views) if respond_to?(:prepend_view_path) }
	#    ActiveSupport.on_load(:action_mailer){ prepend_view_path(views) }
	#  end
	#end

	#initializer :load_environment_config, :before => :load_environment_hook, :group => :all do
	#  environment = paths["config/environments"].existent.first
	#  require environment if environment
	#end

	#initializer :append_assets_path, :group => :all do |app|
	#  app.config.assets.paths.unshift(*paths["vendor/assets"].existent_directories)
	#  app.config.assets.paths.unshift(*paths["lib/assets"].existent_directories)
	#  app.config.assets.paths.unshift(*paths["app/assets"].existent_directories)
	#end

	#initializer :prepend_helpers_path do |app|
	#  if !isolated? || (app == self)
	#    app.config.helpers_paths.unshift(*paths["app/helpers"].existent)
	#  end
	#end

	#initializer :load_config_initializers do
	#  config.paths["config/initializers"].existent.sort.each do |initializer|
	#    load(initializer)
	#  end
	#end

	#initializer :engines_blank_point do
	#  # We need this initializer so all extra initializers added in engines are
	#  # consistently executed after all the initializers above across all engines.
	#end

	#rake_tasks do
	#  next if self.is_a?(Rails::Application)
	#  next unless has_migrations?
    #
	#  namespace railtie_name do
	#    namespace :install do
	#      desc "Copy migrations from #{railtie_name} to application"
	#      task :migrations do
	#        ENV["FROM"] = railtie_name
	#        if Rake::Task.task_defined?("railties:install:migrations")
	#          Rake::Task["railties:install:migrations"].invoke
	#        else
	#          Rake::Task["app:railties:install:migrations"].invoke
	#        end
    #
	#      end
	#    end
	#  end
	#end

	protected function initialize_generators(){
		\PHPRails::import("rails/generators");
	}

	protected function routes__(){
		return $this->routes;
		#defined?(@routes) && @routes
	}

	protected function has_migrations__(){
		#paths["db/migrate"].existent.any?
	}

	protected function find_root_with_flag($flag, $default=null){
		#root_path = self.class.called_from
        #
		#while root_path && File.directory?(root_path) && !File.exist?("#{root_path}/#{flag}")
		#  parent = File.dirname(root_path)
		#  root_path = parent != root_path && parent
		#end
        #
		#root = File.exist?("#{root_path}/#{flag}") ? root_path : default
		#raise "Could not find root path for #{self}" unless root
        #
		#RbConfig::CONFIG['host_os'] =~ /mswin|mingw/ ?
		#  Pathname.new(root).expand_path : Pathname.new(root).realpath
	}

	protected function default_middleware_stack(){
		return new \ActionDispatch\MiddlewareStack();
	}

	protected function _all_autoload_once_paths(){
		return $this->config()->autoload_once_paths;
	}

	protected function _all_autoload_paths(){
		$this->_all_autoload_paths = $this->_all_autoload_paths ?: array_unique(array_merge($this->config()->autoload_paths,  $this->config()->eager_load_paths, $this->config()->autoload_once_paths));
		return $this->_all_autoload_paths;
	}

	protected function _all_load_paths(){
		$this->_all_load_paths = $this->_all_load_paths ?: array_unique(array_merge($this->config->paths->load_paths, $this->_all_autoload_paths()));
		return $this->_all_load_paths;
	}
}
