<?php

namespace ActionPack;

class VERSION{
	const MAJOR = 4;
	const MINOR = 0;
	const TINY  = 0;
	const PRE   = "beta";
	
	#STRING = [MAJOR, MINOR, TINY, PRE].compact.join('.')
		
	public function __toString(){
		return implode('.', self::MAJOR, self::MINOR, self::TINY, self::PRE);
	}
}

