<?php 

class RFileUtils{
	
	public function mkdir_p($path){
		return @mkdir($path, 0777, true);
	}
	
}