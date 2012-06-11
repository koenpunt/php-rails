<?php

namespace ActiveSupport\CoreExt; 


class TimeSpan{
	
	private $date_interval;
	
	public function __construct($date_string){
		/*
		$date = new Time($date_string);
		
		$now = new Time('now');
		
		return 
		*/
		$this->date_interval = \DateInterval::createFromDateString($date_string);
		
	}
	
	public function to_time(){
		$seconds = ($this->date_interval->s)
				 + ($this->date_interval->i * 60)
				 + ($this->date_interval->h * 60 * 60)
				 + ($this->date_interval->d * 60 * 60 * 24)
				 + ($this->date_interval->m * 60 * 60 * 24 * 30)
				 + ($this->date_interval->y * 60 * 60 * 24 * 365);
		
		return $seconds;
	}
	
}