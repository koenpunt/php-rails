<?php 

class REnumerator implements \Countable{

	protected 
		$_enumerator = null;
	
	public function __construct($enumerator){
		$this->_enumerator = $enumerator;
	}
	
	public function count(){
		return count($this->_enumerator);
	}
	

}