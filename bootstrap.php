<?php

# PHP Rails bootstrap
if(!defined('PS'))
	define('PS', PATH_SEPARATOR);
if(!defined('DS'))
	define('DS', PATH_SEPARATOR);

set_include_path(
	get_include_path() . PS . 
	__DIR__ . DS . 'activesupport' . DS . 'lib' . PS .
	__DIR__ . DS . 'actionpack' . DS . 'lib'
);