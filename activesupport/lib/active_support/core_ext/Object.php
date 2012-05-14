<?php 
namespace ActiveSupport\CoreExt;

#require 'active_support/core_ext/object/acts_like'
#require 'active_support/core_ext/object/blank'
#require 'active_support/core_ext/object/duplicable'
#require 'active_support/core_ext/object/try'
#require 'active_support/core_ext/object/inclusion'
#
#require 'active_support/core_ext/object/conversions'
#require 'active_support/core_ext/object/instance_variables'
#
#require 'active_support/core_ext/object/to_json'
#require 'active_support/core_ext/object/to_param'
#require 'active_support/core_ext/object/to_query'
#require 'active_support/core_ext/object/with_options'

require_once 'active_support/OptionMerger.php';

class Object{
	
	protected $_object = null;
	
	public function __construct($object){
		$this->_object = $object;
	}

	# A duck-type assistant method. For example, Active Support extends Date
	# to define an acts_like_date? method, and extends Time to define
	# acts_like_time?. As a result, we can do "x.acts_like?(:time)" and
	# "x.acts_like?(:date)" to do duck-type-safe comparisons, since classes that
	# we want to act like Time simply need to define an acts_like_time? method.
	public function acts_like__($duck){
		respond_to__("acts_like_{$duck}?"); # :"acts_like_{$duck}?"
	}

	# acts_like.rb
	#require 'active_support/core_ext/string/encoding'

	# An object is blank if it's false, empty, or a whitespace string.
	# For example, "", "   ", +nil+, [], and {} are all blank.
	#
	# This simplifies:
	#
	#   if address.nil? || address.empty?
	#
	# ...to:
	#
	#   if address.blank?
	public function blank__(){
		return respond_to__($this->empty__()) ? $this->empty__() : is_null($this->_object);
	}

	# An object is present if it's not <tt>blank?</tt>.
	public function present__(){
		return !$this->blank__();
	}

	# Returns object if it's <tt>present?</tt> otherwise returns +nil+.
	# <tt>object.presence</tt> is equivalent to <tt>object.present? ? object : nil</tt>.
	#
	# This is handy for any representation of objects where blank is the same
	# as not present at all. For example, this simplifies a common check for
	# HTTP POST/query parameters:
	#
	#   state   = params[:state]   if params[:state].present?
	#   country = params[:country] if params[:country].present?
	#   region  = state || country || 'US'
	#
	# ...becomes:
	#
	#   region = params[:state].presence || params[:country].presence || 'US'
	public function presence(){
		if($this->present__()){
			return $this;
		}
	}
	
	#conversions.php
	
	#require 'active_support/core_ext/object/to_param'
	#require 'active_support/core_ext/object/to_query'
	#require 'active_support/core_ext/array/conversions'
	#require 'active_support/core_ext/hash/conversions'

	# duplicable.rb

	#--
	# Most objects are cloneable, but not all. For example you can't dup +nil+:
	#
	#   nil.dup # => TypeError: can't dup NilClass
	#
	# Classes may signal their instances are not duplicable removing +dup+/+clone+
	# or raising exceptions from them. So, to dup an arbitrary object you normally
	# use an optimistic approach and are ready to catch an exception, say:
	#
	#   arbitrary_object.dup rescue object
	#
	# Rails dups objects in a few critical spots where they are not that arbitrary.
	# That rescue is very expensive (like 40 times slower than a predicate), and it
	# is often triggered.
	#
	# That's why we hardcode the following cases and check duplicable? instead of
	# using that rescue idiom.
	#++
	# Can you safely dup this object?
	#
	# False for +nil+, +false+, +true+, symbols, numbers, class and module objects;
	# true otherwise.
	public function duplicable__(){
		return true;
	}
	
	
	# Returns true if this object is included in the argument. Argument must be
	# any object which responds to +#include?+. Usage:
	#
	#   characters = ["Konata", "Kagami", "Tsukasa"]
	#   "Konata".in?(characters) # => true
	#
	# This will throw an ArgumentError if the argument doesn't respond
	# to +#include?+.
	public function in__($another_object){
		try{
			return $another_object->include__($this);
		}catch(NoMethodError $e){
			throw new ArgumentError("The parameter passed to #in? must respond to #include?");
		}
	}
	
	# instance_variables.rb
	
	# Returns a hash that maps instance variable names without "@" to their
	# corresponding values. Keys are strings both in Ruby 1.8 and 1.9.
	#
	#   class C
	#     def initialize(x, y)
	#       @x, @y = x, y
	#     end
	#   end
	#
	#   C.new(0, 1).instance_values # => {"x" => 0, "y" => 1}
	public function instance_values(){ #:nodoc:
		#Hash[instance_variables.map { |name| [name.to_s[1..-1], instance_variable_get(name)] }]
	}

	# Returns an array of instance variable names including "@". They are strings
	# both in Ruby 1.8 and 1.9.
	#
	#   class C
	#     def initialize(x, y)
	#       @x, @y = x, y
	#     end
	#   end
	#
	#   C.new(0, 1).instance_variable_names # => ["@y", "@x"]
	#if RUBY_VERSION >= '1.9'
	public function instance_variable_names(){
		return $instance_variables->map(function($var){ return $var->to_s(); });
	}
	#else
	#  alias_method :instance_variable_names, :instance_variables
	#end
		
	# to_param.rb
	
	# Alias of <tt>to_s</tt>.
	public function to_param(){
		return $this->to_s();
	}

	# Converts an object into a string suitable for use as a URL query string, using the given <tt>key</tt> as the
	# param name.
	#
	# Note: This method is defined as a default implementation for all Objects for Hash#to_query to work.
	public function to_query($key){
		$key = CGI::escape($key->to_param());
		$value = CGI::escape($this->to_param()->to_s());
		return "{$key}=#{$value}";
	}
	
	# with_options.rb
	
	# require 'active_support/option_merger'

	# An elegant way to factor duplication out of options passed to a series of
	# method calls. Each method called in the block, with the block variable as
	# the receiver, will have its options merged with the default +options+ hash
	# provided. Each method called on the block variable must take an options
	# hash as its final argument.
	#
	# Without <tt>with_options></tt>, this code contains duplication:
	#
	#   class Account < ActiveRecord::Base
	#     has_many :customers, :dependent => :destroy
	#     has_many :products,  :dependent => :destroy
	#     has_many :invoices,  :dependent => :destroy
	#     has_many :expenses,  :dependent => :destroy
	#   end
	#
	# Using <tt>with_options</tt>, we can remove the duplication:
	#
	#   class Account < ActiveRecord::Base
	#     with_options :dependent => :destroy do |assoc|
	#       assoc.has_many :customers
	#       assoc.has_many :products
	#       assoc.has_many :invoices
	#       assoc.has_many :expenses
	#     end
	#   end
	#
	# It can also be used with an explicit receiver:
	#
	#   I18n.with_options :locale => user.locale, :scope => "newsletter" do |i18n|
	#     subject i18n.t :subject
	#     body    i18n.t :body, :user_name => user.name
	#   end
	#
	# <tt>with_options</tt> can also be nested since the call is forwarded to its receiver.
	# Each nesting level will merge inherited defaults in addition to their own.
	#
	public static function with_options($options, \Closure $yield){
		return $yield(new \ActiveSupport\OptionMerger(get_called_class(), $options));
	}
}