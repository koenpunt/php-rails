<?php

namespace I18n;

class Symbol
{
	private $value;

	public function __construct($value = null)
	{
		$this->set_value($value);
	}
	
	public function __toString()
	{
		return $this->value;
	}
	
	public function get_value()
	{
		return $this->value;
	}

	public function set_value($value = null)
	{
		if ($value instanceof Symbol) {
			$value = $value->get_value();
		}
		$this->value = $value;
	}
}
