<?php

class RDir{
	
	protected 
		$_dir = null,
		$_dirname = null;
	
	public function __construct($dirname){ # new
		$this->_dirname = $dirname;
		if($dir = opendir($dirname)){
			$this->_dir = $dir;
		}
	}
	
	public static function tmpdir(){
		return sys_get_temp_dir();
	}
	
	public static function chdir($string, $block=false){
		$pwd = self::pwd();
		
		if($block){
			self::chdir($string);
			call_user_func($block);
			self::chdir($pwd);
		}else{
			return chdir($string);
		}
		
	}
	
	public static function chroot($string){
		return chroot($string);
	}
	
	public static function delete($string){
		return self::rmdir($string);
	}
	
	public static function entries($dirname){
		return scandir($dirname);
	}
	
	public static function directory__($file_name){
		return is_dir($file_name);
	}
	
	public static function exists__($file_name){
		return self::directory__($file_name);
	}
	
	# foreach
	public static function feach($dirname, $block=false){
		if($block){
			if($entries = self::entries($dirname)){
				foreach($entries as $entry){
					call_user_func($block, $entry);
				}
			}
		}else{
			return new REnumerable(self::entries($dirname));
		}
		
	}
	
	public static function getwd(){
		return getcwd();
	}
	
	/*
	var_dump('FLAG_1', !!($flags_or_block & Flag::FLAG_1));
	var_dump('FLAG_2', !!($flags_or_block & Flag::FLAG_2));
	var_dump('FLAG_3', !!($flags_or_block & Flag::FLAG_3));
	var_dump('FLAG_4', !!($flags_or_block & Flag::FLAG_4));
	*/
	
	public static function glob($pattern, $flags = null){
		$pattern_recursion = explode('**', $pattern, 2);
		
		if(count($pattern_recursion) == 1){
			return glob($pattern, $flags);
		}
		
		if($pattern_recursion[1] == ''){
			return glob($pattern_recursion[0] . '*', $flags);
		}
		
		if(substr($pattern, -1, 1) == '/'){
			$flags = $flags ? $flags | GLOB_ONLYDIR : GLOB_ONLYDIR;
		}
		$first_matches = glob($pattern_recursion[0], GLOB_ONLYDIR);
		$matches = $pattern_recursion[1] == '/' ? $first_matches : array();
		foreach($first_matches as $match){
			$matches = array_merge($matches, self::glob_recursion($match . '*' . $pattern_recursion[1], $flags));
		}
		if($yield = \PHPRails\block_given__(func_get_args())){
			foreach($matches as $match){
				$yield( $match );
			}
			return null;
		}else{
			return $matches;
		}
	}
	
	private static function glob_recursion($pattern, $flags = null){
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
		    $files = array_merge($files, self::glob_recursion($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}
		
	
	public static function home($username = null){
		/*
			TODO Get home directory of given $username 
		*/
		if(is_null($username)){
			return `echo $HOME`;
		}
		return;
	}
	
	public static function mkdir($pathname, $mode=0777){
		return mkdir($pathname, $mode);
	}
	
	public static function open($dirname, $block = false){
		$dir = new RDir($dirname);
		if($block){
			$block_value = call_user_func($block, $dir);
			$dir->close();
			return $block_value;
		}
		return $dir;
	}
	
	public function close(){
		return closedir($this->_dir);
	}
	
	public static function pwd(){
		return self::getwd();
	}
	
	public static function rmdir($dirname){
		return rmdir($dirname);
	}
	
	public static function unlink($dirname){
		return self::rmdir($dirname);
	}
	
	public function each($block = false){
		$entries = self::entries($this->_dirname);
		if($block){
			foreach($entries as $entry){
				call_user_func($block, $entry);
			}
		}else{
			return new REnumerable($entries);
		}
	}
	
	
	public function inspect(){
		/*
			TODO Implement from: http://ruby-doc.org/core-1.9.3/Dir.html#method-i-inspect
		*/
	}
	
	public function path(){
		return $this->_dirname;
	}
	
	#public function pos(){
	#	
	#}
	#
	#public function pos(){ # = setter
	#	
	#}
	
	public function read(){
		/*
			TODO Update internal pos (file pointer)
		*/
		$entry = readdir($this->_dir);
		return $entry ? $entry : null;
	}
	
	public function rewind(){
		/*
			TODO Update internal pos (file pointer)
		*/
		rewinddir($this->_dir);
		return $this;
	}
	
	public function seek(){
		/*
			TODO http://ruby-doc.org/core-1.9.3/Dir.html#method-i-seek
		*/
		return $this;
	}
	
	public function tell(){
		/*
			TODO http://ruby-doc.org/core-1.9.3/Dir.html#method-i-tell
		*/
	}
	
	public function to_path(){
		return $this->path();
	}
	
	
	
}