<?php
namespace ActiveSupport\CoreExt; 

class Time extends DateTime{
	
	public static function now(){
		$called_class = get_called_class();
		return new $called_class('now');
	}
	
	public static function utc($year, $month, $day, $hour, $minute, $second){
		return new Time('@' . mktime($hour, $minute, $second, $month, $day, $year), new \DateTimeZone('UTC'));
	}
	
	public static function mktime($year, $month, $day, $hour, $minute, $second){
		return new Time(date('r', mktime($hour, $minute, $second, $month, $day, $year)));
	}

	public function in_time_zone($timezone){
		$timezone = $timezone instanceof \DateTimeZone ? $timezone : new \DateTimeZone($timezone);
		return $this->setTimezone($timezone);
	}
	
}