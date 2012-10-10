<?php

namespace ActiveSupport\CoreExt; 

\PHPRails::import('active_support/core_ext/date_time');

class Date extends DateTime{
	
	public function __construct(){
		if(func_num_args() == 3){
			return parent::__construct(implode('/', func_get_args()));
		}
		return call_user_func_array('parent::__construct', func_get_args());
	}

	public static function leap__($year){
		return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year %400) == 0)));
	}
		
	/*
    $days = ($month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year %400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31)); //returns days in the given month
	*/
	
}
