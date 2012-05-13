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
 * Flattens an array, or returns FALSE on fail. 
 */ 
function array_flatten($array) { 
	if (!is_array($array)) {
		return false;
	} 
	$result = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$result = array_merge($result, array_flatten($value));
		} else {
			$result[$key] = $value;
		} 
	} 
	return $result; 
} 

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
				$base[$key] = AppHelper::array_merge_recursive_distinct($base[$key], $append[$key]);
			}else if(is_numeric($key)) {
				if(!in_array($value, $base)) $base[] = $value;
			} else {
				$base[$key] = $value;
			}
		}
	}
	return $base;
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

/**
 * Not implemented yet
 *
 * @return void
 * @author Koen Punt
 */
function acts_like(){

}