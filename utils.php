<?php

namespace PHPRails;
use ActiveSupport\SafeBuffer;

# args.extract_options!
	# Returns associative array of options or false 
function extract_options(&$arguments){
	if(\PHPRails\is_hash( end($arguments) )){
		return array_pop($arguments);
	}
	return false;
}

# Extract block (callable) from arguments
function block_given__(&$arguments){
	if(is_callable(end($arguments))){
		return array_pop($arguments);
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

function fetch(array $data, $key, $alternative=false){
	if(array_key_exists($key, $data)){
		return $data[$key];
	}
	if($alternative === false){
		throw new KeyErrorException(); 
	}
	if(is_callable($alternative)){
		return call_user_func($alternative, $key);
	}
	return $alternative;
}

function get(array &$data, $key){
	if(array_key_exists($key, $data)){
		return $data[$key];
	}
	return false;
}

function to_sym($content){
	return new RSymbol($content);
}

function html_safe($content){
	return new SafeBuffer($content);
}
# html_safe?
function html_safe__($value){
	return $value instanceof SafeBuffer;
}

function capture(Closure &$block){
	return call_user_func($block);
}


function assert_valid_keys(array $data, $valid_keys){
	$valid_keys = \PHPRails\array_flatten($valid_keys);
	foreach($data as $k => $v){
		if(!in_array($k, $valid_keys)){
			throw new InvalidArgumentError("Unknown key: {$k}");
		}
	}
}

function array_flatten(array $array){
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
				$base[$key] = \PHPRails\array_merge_recursive_distinct($base[$key], $append[$key]);
			} else if(is_numeric($key)) {
				if(!in_array($value, $base)) $base[] = $value;
			} else {
				$base[$key] = $value;
			}
		}
	}
	return $base;
}

function array_delete($value, &$array){
	$key = array_search($value, $array);
	if($key !== false){
		unset($array[$key]);
	}
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

function acts_like__($object, $type){
	$class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
	if(class_exists($class_name)){
		try{
			$object = new $class_name($object);
			return true;
		}catch(Exception $e){
		}
	}
	return false;
}
/**
 * Move to date-utils.php or something alike
 *
 * @author Koen Punt
 */

function between($value, $start, $end){
	return $value >= $start && $value <= $end;
}

function seconds($seconds){
	return $seconds;
}

function minutes($minutes){
	return (int)$minutes * 60;
}

function hour(){
	return \PHPRails\hours(1);
}

function hours($hours){
	return (int)$hours * 3600;
}

function day(){
	return \PHPRails\days(1);
}

function days($days){
	return (int)$days * 86400;
}

function month(){
	return \PHPRails\months(1);
}

function months($months){
	return (int)$months * 2592000;
}

function year(){
	return \PHPRails\years(1);
}

function years($years){
	return (int)$years * 31557600;
}

function ago($time){
	return time() - $time;
}


