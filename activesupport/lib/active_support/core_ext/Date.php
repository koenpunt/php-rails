<?php
namespace ActiveSupport\CoreExt; 

require_once 'active_support/core_ext/DateTime.php';

class Date extends DateTime{
	
	public static function leap__($year){
		return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year %400) == 0)));
	}
		
	/*
    $days = ($month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year %400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31)); //returns days in the given month
	*/
	
}
