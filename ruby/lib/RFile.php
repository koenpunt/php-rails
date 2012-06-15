<?php

class RFile{
	
	const SEPARATOR = DIRECTORY_SEPARATOR;
	
	protected $_handle;
	
	public static function join(){
		$parts = func_get_args();
		return implode(RFile::SEPARATOR, $parts);
	}
	
	public static function dirname($path){
		return dirname($path);
	}
	
	public static function read($file){
		$this->_handle = fopen($file, 'r');
		$content = fread($this->_handle, filesize($file));
		fclose($this->_handle);
		
		return $content;
	}
	
	public function write($content){
		fwrite($this->_handle, $content);
	}
	
	public function close(){
		fclose($this->_handle);
	}
	
	public static function atomic_write($file_name, $temp_dir = null){
		$temp_dir = is_null($temp_dir) ? RDir::tmpdir() : $temp_dir;
		$temp_file = new RTempfile(RFile::basename($file_name), $temp_dir);
		if($yield = \PHPRails\block_given__(func_get_args())){
			$yield( $temp_file );
		}
		$temp_file->close();
		RFile::rename($temp_file->path, $file_name);
	}
	
	public static function mtime($file){
		return filemtime($file);
	}
	
	public static function utime($atime, $mtime, $file){
		return @touch($file, $mtime, $atime);
	}
	
	public static function extname($file){
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		return $extension;
	}
	
	public static function exist($file){
		return file_exists($file);
	}
}