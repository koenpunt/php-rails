<?php

use I18n\I18n;
use I18n\Symbol;

function to_sym($value = null)
{
	return new Symbol($value);
}

function array_flatten(array $array)
{
	$i = 0;
	$n = count($array);

	while ($i < $n) {
		if (is_array($array[$i])) {
			array_splice($array,$i,1,$array[$i]);
		} else {
			++$i;
		}
		$n = count($array);
	}
	return $array;
}


/**
 * Somewhat naive way to determine if an array is a hash.
 */
function is_hash(&$array){
	if (!is_array($array))
		return false;

	$keys = array_keys($array);
	return empty($array) || @is_string($keys[0]) ? true : false;
}

function get(array &$data, $key){
	if(array_key_exists($key, $data)){
		return $data[$key];
	}
}

function delete(array &$data, $key){
	if(array_key_exists($key, $data)){
		$value = $data[$key];
		unset($data[$key]);
		return $value;
	}
	return false;
	
}


?>