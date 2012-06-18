<?php 

#--
# Copyright (c) 2005-2012 David Heinemeier Hansson
#
# Permission is hereby granted, free of charge, to any person obtaining
# a copy of this software and associated documentation files (the
# "Software"), to deal in the Software without restriction, including
# without limitation the rights to use, copy, modify, merge, publish,
# distribute, sublicense, and/or sell copies of the Software, and to
# permit persons to whom the Software is furnished to do so, subject to
# the following conditions:
#
# The above copyright notice and this permission notice shall be
# included in all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#++

#require 'securerandom'
#require "active_support/dependencies/autoload"
#require "active_support/version"
#require "active_support/logger"

#namespace ActiveSupport;
#  extend ActiveSupport::Autoload

#  autoload :Concern
#  autoload :Dependencies
#  autoload :DescendantsTracker
#  autoload :FileUpdateChecker
#  autoload :LogSubscriber
#  autoload :Notifications
#
#  # TODO: Narrow this list down
#  eager_autoload do
#    autoload :BacktraceCleaner
#    autoload :BasicObject
#    autoload :Benchmarkable
#    autoload :Cache
#    autoload :Callbacks
#    autoload :Configurable
#    autoload :Deprecation
#    autoload :Gzip
#    autoload :Inflector
#    autoload :JSON
#    autoload :MessageEncryptor
#    autoload :MessageVerifier
#    autoload :Multibyte
#    autoload :OptionMerger
#    autoload :OrderedHash
#    autoload :OrderedOptions
#    autoload :Rescuable
#    autoload :StringInquirer
#    autoload :TaggedLogging
#    autoload :XmlMini
#  end
#
PHPRails::import('active_support/safe_buffer');
#  autoload :SafeBuffer, "active_support/core_ext/string/output_safety"
#  autoload :TestCase
#
#autoload :I18n, "active_support/i18n"


class ActiveSupport{
	# lazy_load_hooks allows rails to lazily load a lot of components and thus making the app boot faster. Because of
	# this feature now there is no need to require <tt>ActiveRecord::Base</tt> at boot time purely to apply configuration. Instead
	# a hook is registered that applies configuration once <tt>ActiveRecord::Base</tt> is loaded. Here <tt>ActiveRecord::Base</tt> is used
	# as example but this feature can be applied elsewhere too.
	#
	# Here is an example where +on_load+ method is called to register a hook.
	#
	#   initializer "active_record.initialize_timezone" do
	#     ActiveSupport.on_load(:active_record) do
	#       self.time_zone_aware_attributes = true
	#       self.default_timezone = :utc
	#     end
	#   end
	#
	# When the entirety of +activerecord/lib/active_record/base.rb+ has been evaluated then +run_load_hooks+ is invoked.
	# The very last line of +activerecord/lib/active_record/base.rb+ is:
	#
	#   ActiveSupport.run_load_hooks(:active_record, ActiveRecord::Base)
	#
	static $load_hooks = array(); # Hash.new { |h,k| h[k] = [] }
	static $loaded = array(); # Hash.new { |h,k| h[k] = [] }

	public static function on_load($name, $options = array(), $block = null){
		if(self::$loaded[$name]){
			foreach(self::$loaded[$name] as $base){
				self::execute_hook($base, $options, $block);
			}
		}
		self::$load_hooks[$name] = self::$load_hooks[$name] ?: array();
		array_push(self::$load_hooks[$name], array($block, $options));
	}

	public static function execute_hook($base, $options, $block){
		if( \PHPRails\get($options, 'yield') ){
			return call_user_func($block, $base);
		}else{
			# https://gist.github.com/1502322
#			base.instance_eval(&block)
		}
	}

	public static function run_load_hooks($name, $base = null){
		self::$loaded[$name] = self::$loaded[$name] ?: array();
		array_push(self::$loaded[$name], $base);
		foreach(self::$load_hooks[$name] as $hook_options){
			list($hook, $options) = $hook_options;
			self::execute_hook($base, $options, $hook);
		}
	}
}

