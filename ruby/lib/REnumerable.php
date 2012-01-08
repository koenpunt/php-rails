<?php 

class REnumerable implements Countable{
		
	protected 
		$_enumerable = null;
	
	public function __construct($enumerable){
		$this->_enumerable = $enumerable;
	}
	
	#all?
	#any?
	#chunk
	#collect
	#collect_concat
	#count
	#cycle
	#detect
	#drop
	#drop_while
	#each_cons
	#each_entry
	#each_slice
	#each_with_index
	#each_with_object
	#entries
	#find
	#find_all
	#find_index
	#first
	#flat_map
	#grep
	#group_by
	#include?
	#inject
	#map
	#max
	#max_by
	#member?
	#min
	#min_by
	#minmax
	#minmax_by
	#none?
	#one?
	#partition
	#reduce
	#reject
	#reverse_each
	#select
	#slice_before
	#sort
	#sort_by
	#take
	#take_while
	#to_a
	#zip
	
	
	public function all__($block = false){
		if(!$block){
			$block = function($entry){
				return (!is_null($entry) && $entry !== false);
			};
		}
		$all = array_filter($this->_enumerable, $block);
		return count($all) == count($this->_enumerable);
	}
	
	public function any__($block = false){
		if(!$block){
			$block = function($entry){
				return (!is_null($entry) && $entry !== false);
			};
		}
		$all = array_filter($this->_enumerable, $block);
		return count($all) > 0;
	}
	
	public function chunk($initial_state_or_block, $block = false){
		/*
			TODO Implement initial_state
		*/
		
		if(!$block){
			$block = $initial_state_or_block;
		}else{
			$initial_state = $initial_state_or_block;
		}
		$chunks = array();
		$chunk = null;
		$last_value = null;
		foreach($this->_enumerable as $entry){
			$current_value = call_user_func($block, $entry);
			if($last_value != $current_value){
				$last_value = $current_value;
				if($chunk){
					array_push($chunks, $chunk);
				}
				$chunk = array();
			}
			array_push($chunk, $entry);
		}
		array_push($chunks, $chunk);
		
		return new REnumerable($chunks);
	}
	
	public function collect($block = false){
		if(!$block){
			return $this;
		}
		
		return array_map($block, $this->_enumerable);
	}
	
	public function collect_concat($block = false){
		$result = array();
		$stack = array();
		array_push($stack, array("", $this->_enumerable));

		while (count($stack) > 0) {
			list($prefix, $array) = array_pop($stack);
			foreach ($array as $key => $value) {
				$new_key = $prefix . strval($key);
				if (is_array($value)){
					array_unshift($stack, array($new_key, $value));
				}else{
					array_push($result, $value);
				}
			}
		}
		if(!$block){
			return new REnumerable($result);
		}
		
		return $result;
	}
	
	public function count(){
		return count($this->_enumerable);
	}
	
	public function cycle($cycles_or_block, $block = false){
		if(!$block){
			$cycles = -1;
			$block = $cycles_or_block;
		}else{
			$cycles = $cycles_or_block;
		}
		$entries = $this->_enumerable;
		$result = array();
		for($i = $cycles ; $i !== 0 ; $i--){
			foreach($entries as $entry){
				array_push($result, call_user_func($block, $entry));
			}
		}
		return $result;
	}
	
	public function detect(){
		
	}
	public function drop(){
		
	}
	public function drop_while(){
		
	}
	public function each_cons(){
		
	}
	public function each_entry(){
		
	}
	public function each_slice($size, $block = false){
		
		
		if($size <= 0)throw new ArgumentError('invalid slice slice');
		
		
		
		# long size = NUM2LONG(n);
		# VALUE args[2], ary;
		# 
		# if (size <= 0) rb_raise(rb_eArgError, "invalid slice size");
		# RETURN_ENUMERATOR(obj, 1, &n);
		# args[0] = rb_ary_new2(size);
		# args[1] = (VALUE)size;
		# 
		# rb_block_call(obj, id_each, 0, 0, each_slice_i, (VALUE)args);
		# 
		# ary = args[0];
		# if (RARRAY_LEN(ary) > 0) rb_yield(ary);
		# 
		# return Qnil;
		
		
	}
	public function each_with_index(){
		
	}
	public function each_with_object(){
		
	}
	public function entries(){
		
	}
	public function find(){
		
	}
	public function find_all(){
		
	}
	public function find_index(){
		
	}
	public function first(){
		
	}
	public function flat_map(){
		
	}
	public function grep(){
		
	}
	public function group_by(){
		
	}
	public function include__(){
		
	}
	public function inject(){
		
	}
	public function map(){
		
	}
	public function max(){
		
	}
	public function max_by(){
		
	}
	public function member__(){
		
	}
	public function min(){
		
	}
	public function min_by(){
		
	}
	public function minmax(){
		
	}
	public function minmax_by(){
		
	}
	public function none__(){
		
	}
	public function one__(){
		
	}
	public function partition(){
		
	}
	public function reduce(){
		
	}
	public function reject(){
		
	}
	public function reverse_each(){
		
	}
	public function select(){
		
	}
	public function slice_before(){
		
	}
	public function sort(){
		
	}
	public function sort_by(){
		
	}
	public function take(){
		
	}
	public function take_while(){
		
	}
	public function to_a(){
		
	}
	public function zip(){
		
	}
	
}