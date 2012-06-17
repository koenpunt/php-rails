<?php 


namespace ActiveSupport\CoreExt;

class Hash{

	public function __construct($hash){
		ksort($hash);
		$this->_hash = $hash;
	
	}

}