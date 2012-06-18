<?php

class RTempfile extends RFile{

	public $path;
	
	public function __construct($basename, $tmpdir = null){
		$options = \PHPRails\extract_options(func_get_args());
		list($prefix, $postfix) = is_array($basename) ? $basename : array($basename, '');
		$tmpdir = is_null($tmpdir) ? RDir::tmpdir() : $tmpdir;
		$this->path = tempnam($tmpdir, $prefix) . $postfix;
		$this->_handle = fopen($this->path, 'w+');
	}
	
}