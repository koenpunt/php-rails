<?php 

class RHash{

	public function __construct($hash){
		ksort($hash);
		$this->_hash = $hash;
	
	}

}