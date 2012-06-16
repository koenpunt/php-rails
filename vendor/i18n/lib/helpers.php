<?php

namespace I18n\Helpers;

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

function extract_options(&$arguments){
	if(is_hash( end($arguments) )){
		return array_pop($arguments);
	}
	return false;
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

/**
 * Merges any number of arrays / parameters recursively, replacing 
 * entries with string keys with values from latter arrays. 
 * If the entry or the next value to be assigned is an array, then it 
 * automagically treats both arguments as an array.
 * Numeric entries are appended, not replaced, but only if they are 
 * unique
 *
 * calling: result = array_merge_recursive_distinct(a1, a2, ... aN)
 */
function array_merge_recursive_distinct() {
	$arrays = func_get_args();
	$base = array_shift($arrays);
	if(!is_array($base)) $base = empty($base) ? array() : array($base);
	foreach($arrays as $append) {
		if(!is_array($append)) $append = array($append);
		foreach($append as $key => $value) {
			if(!array_key_exists($key, $base) and !is_numeric($key)) {
				$base[$key] = $append[$key];
				continue;
			}
			if(is_array($value) or is_array($base[$key])) {
				$base[$key] = array_merge_recursive_distinct($base[$key], $append[$key]);
			} else if(is_numeric($key)) {
				if(!in_array($value, $base)) $base[] = $value;
			} else {
				$base[$key] = $value;
			}
		}
	}
	return $base;
}



?>