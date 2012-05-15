<?php
namespace I18n;
#namespace ActiveSupport\CoreExt;

class DateTime extends \DateTime{

	
	public static function calculate($time, $add){
		$called_class = get_called_class();
		$time = is_numeric($time) ? "@$time" : $time;
		$date = $time instanceof \DateTime ? clone $time : new $called_class($time);
		return $date->modify($add);
	}
	
	public function __toString(){
		return $this->format('Y-m-d H:i:s');
	}
	
	public function update($time){
		return self::calculate($this, $time);
	}
	
	public function year(){
		return (int)$this->format('Y');
	}
	
	public function mon(){
		return (int)$this->format('n');
	}
	
	public function day(){
		return (int)$this->format('j');
	}
	
	public function wday(){
		return (int)$this->format('w');
	}
	
	public function to_time(){
		return $this->getTimestamp();
	}
	
	public function strftime($format){
		return strftime($format, $this->to_time());
	}
	
	public function dup(){
		$called_class = get_called_class();
		return clone $this;
		#return new $called_class("@".$this->to_time(), new \DateTimeZone($this->timezone));
	}


}