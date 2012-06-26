<?php

namespace ActiveSupport;

class SafeBuffer{

	private $content;

	public function __construct($content){
		$this->content = (string)$content;
	}
	
	public function __toString(){
		return $this->content;
	}
	
	public function append( $content ){
		$this->content .= (string)$content;
		return $this;
	}
	
	public function safe_concat( $value ){
		if(!\PHPRails\html_safe__($value)){
			throw new SafeConcatError();
		}
		return $this->original_concat($value);
	}
	
	private function original_concat( $value ){
		$this->content .= (string)$value;
		return $this;
	}
	
	
}