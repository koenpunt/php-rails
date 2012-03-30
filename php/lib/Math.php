<?php

/**
 * Math methods explained: http://lua-users.org/wiki/MathLibraryTutorial
 */

class Math {

	/**
	 * Returns the value of flt*(2**int).
	 *
	 * @param string $flt 
	 * @param string $int 
	 * @return float
	 * @author Koen Punt
	 */
	public static function ldexp($flt, $int){
		return $flt * pow(2, $int);
	}

	/**
	 * Returns a two-element array containing the normalized fraction (a Float) and exponent (a Fixnum) of numeric.
	 *
	 * @param numeric $float
	 * @return array
	 * @author Koen Punt
	 */
	public static function frexp($float){
		#return $norm * pow(2, $exp);
		$exponent = ( floor(log($float, 2)) + 1 );
		$mantissa = ( $float * pow(2, -$exponent) );
		return array($mantissa, $exponent);
	}
	

}
