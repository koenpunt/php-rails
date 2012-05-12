<?php

# args.extract_options!
function extract_options(&$arguments){
	$_arguments = $arguments;
	$options = array_pop($_arguments);
	if(is_hash($options)){
		$arguments = $_arguments;
		return $options;
	}
	return false;
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
 * Somewhat naive way to determine if an array is a hash.
 */
function is_hash(&$array){
	if (!is_array($array))
		return false;

	$keys = array_keys($array);
	return @is_string($keys[0]) ? true : false;
}
