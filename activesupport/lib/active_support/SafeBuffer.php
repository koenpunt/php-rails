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
	
}