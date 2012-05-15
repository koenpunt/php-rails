<?php

namespace I18n;

class OptionMerger{ #:nodoc:
	/*
	instance_methods.each do |method|
		undef_method(method) if method !~ /^(__|instance_eval|class|object_id)/
	end
	*/

	public function __construct($context, $options){
		list($this->context, $this->options) = array($context, $options);
	}

	public function __call($method, $arguments){ // ,&$block){
		$arguments_last = end($arguments);
		if($arguments_last instanceof Closure){ // Proc
			$proc = array_pop($arguments);
			/*
				TODO Correctly handling closure (ruby Proc)
			*/
			#$arguments << lambda { |*args| @options.deep_merge(proc.call(*args)) }
		}else{
			array_push($arguments, (is_hash($arguments_last) ? array_merge_recursive($this->options, array_pop($arguments)) : $this->options));
		}

		return call_user_func_array(array($this->context, $method), $arguments); //, &$block)
	}

	
}
