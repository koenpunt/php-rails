<?php
require_once '../lib/Math.php';

class MathTest extends PHPUnit_Framework_TestCase{
	
	public function testFrexp(){
		$this->assertEquals(array(0.5, 2), Math::frexp(2));
		
		$this->assertEquals(array(0.75, 2), Math::frexp(3));
		
		$this->assertEquals(array(0.5, 8), Math::frexp(128));
		
		$this->assertEquals(array(0.785398175, 2), Math::frexp(3.1415927));
	}
	
	public function testLdexp(){
		$this->assertEquals(3.14, Math::ldexp(0.785,2));
		
		$this->assertEquals(128, Math::ldexp(0.5,8));
	}
	
}
