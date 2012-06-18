<?php 

\PHPRails::import('rails/ruby_version_check');

\PHPRails::import('pathname');

\PHPRails::import('active_support');
\PHPRails::import('active_support/core_ext/kernel/reporting');
\PHPRails::import('active_support/core_ext/array/extract_options');

\PHPRails::import('rails/application');
\PHPRails::import('rails/version');
\PHPRails::import('rails/deprecation');

\PHPRails::import('active_support/railtie');
\PHPRails::import('action_dispatch/railtie');

# For Ruby 1.9, UTF-8 is the default internal and external encoding.
#silence_warnings do
#  Encoding.default_external = Encoding::UTF_8
#  Encoding.default_internal = Encoding::UTF_8
#end
	  
class PHPRails{
	#autoload :Info, 'rails/info'
	#autoload :InfoController, 'rails/info_controller'
	#autoload :Queueing, 'rails/queueing'

	public function application(){
		$this->application = $this->application ?: null;
		return $this->application;
	}

	public function set_application($application){
		$this->application = $application;
	}

	# The Configuration instance used to configure the Rails environment
	public function configuration(){
		$this->application()->config();
	}

	# Rails.queue is the application's queue. You can push a job onto
	# the queue by:
	#
	#   Rails.queue.push job
	#
	# A job is an object that responds to +run+. Queue consumers will
	# pop jobs off of the queue and invoke the queue's +run+ method.
	#
	# Note that depending on your queue implementation, jobs may not
	# be executed in the same process as they were created in, and
	# are never executed in the same thread as they were created in.
	#
	# If necessary, a queue implementation may need to serialize your
	# job for distribution to another process. The documentation of
	# your queue will specify the requirements for that serialization.
	def queue
	  application.queue
	end

	def initialize!
	  application.initialize!
	end

	def initialized?
	  application.initialized?
	end

	def logger
	  @logger ||= nil
	end

	def logger=(logger)
	  @logger = logger
	end

	def backtrace_cleaner
	  @backtrace_cleaner ||= begin
	    # Relies on Active Support, so we have to lazy load to postpone definition until AS has been loaded
	    require 'rails/backtrace_cleaner'
	    Rails::BacktraceCleaner.new
	  end
	end

	def root
	  application && application.config.root
	end

	def env
	  @_env ||= ActiveSupport::StringInquirer.new(ENV["RAILS_ENV"] || ENV["RACK_ENV"] || "development")
	end

	def env=(environment)
	  @_env = ActiveSupport::StringInquirer.new(environment)
	end

	def cache
	  @cache ||= nil
	end

	def cache=(cache)
	  @cache = cache
	end

	# Returns all rails groups for loading based on:
	#
	# * The Rails environment;
	# * The environment variable RAILS_GROUPS;
	# * The optional envs given as argument and the hash with group dependencies;
	#
	# == Examples
	#
	#   groups :assets => [:development, :test]
	#
	#   # Returns
	#   # => [:default, :development, :assets] for Rails.env == "development"
	#   # => [:default, :production]           for Rails.env == "production"
	#
	def groups(*groups)
	  hash = groups.extract_options!
	  env = Rails.env
	  groups.unshift(:default, env)
	  groups.concat ENV["RAILS_GROUPS"].to_s.split(",")
	  groups.concat hash.map { |k,v| k if v.map(&:to_s).include?(env) }
	  groups.compact!
	  groups.uniq!
	  groups
	end

	def version
	  VERSION::STRING
	end

	def public_path
	  application && application.paths["public"].first
	end
end
