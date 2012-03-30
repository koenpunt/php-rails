<?php

class RFile{
	
	const SEPARATOR = DIRECTORY_SEPARATOR;
	
	public static function join(){
		$parts = func_get_args();
		return implode(RFile::SEPARATOR, $parts);
	}
	
	public static function dirname($path){
		return dirname($path);
	}
	
	public static function read($file){
		$handle = fopen($file, 'r');
		$content = fread($handle, filesize($file));
		fclose($handle);
		
		return $content;
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