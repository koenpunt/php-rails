<?php

use I18n\I18n;
use I18n\Symbol;

function t($key, $options = array())
{
	return I18n::translate($key, $options);
}

function _s($value = null)
{
	return new Symbol($value);
}


?>