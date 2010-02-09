<?php

namespace I18n\Backend;

class Base
{
	private $initialized = false;

	public function load_translations(array $filenames)
	{

	}

	public function store_translations($locale, $data, $options = array())
	{

	}

	public function translate($locale, $key, $options = array())
	{

	}

	public function localize($locale, $object, $format = 'DEFAULT', $options = array())
	{

	}

	public function is_initilized()
	{
		return $this->initialized;
	}

	public function available_locales()
	{

	}

	public function reload()
	{

	}

	public function lookup($locale, $key, $scope = array(), $options = array())
	{

	}

	public function default($locale, $object, $subject, $options = array())
	{

	}

	public function resolve($locale, $object, $subject, $options = null)
	{

	}

	public function pluralize($locale, $entry, $count)
	{

	}

	public function interpolate($locale, $string, $values = array())
	{

	}

	public function preserve_encoding($string)
	{

	}

	public function interpolate_lamba($object, $string, $key)
	{

	}

	public function load_file($filename)
	{

	}

	public function load_php($filename)
	{

	}

	public function load_yml($filename)
	{

	}

	public function merge_translations($locale, $data, $options = array())
	{

	}
}

?>