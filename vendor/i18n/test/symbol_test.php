<?php

require_once(dirname(__FILE__) . '/../I18n.php');

use I18n\I18n;
use I18n\Symbol;

class Symbol_Test extends PHPUnit_Framework_TestCase
{
	private $base = null;

	public function setUp()
	{

	}

	public function test_get_value()
	{
		$expected = 'a value';
		$symbol = new Symbol($expected);
		$actual = $symbol->get_value();
		$this->assertEquals($expected, $actual);
	}

	public function test_set_value()
	{
		$symbol = new Symbol(null);
		$this->assertEquals(null, $symbol->get_value());
		$expected = 'a value';
		$symbol->set_value($expected);
		$actual = $symbol->get_value();
		$this->assertEquals($expected, $actual);
	}

	public function test_set_value_using_a_symbol()
	{
		$expected = 'a value';
		$symbolA = new Symbol($expected);
		$this->assertEquals($expected, $symbolA->get_value());
		$symbolB = new Symbol(null);
		$this->assertEquals(null, $symbolB->get_value());
		$symbolB->set_value($symbolA);
		$actual = $symbolB->get_value();
		$this->assertEquals($expected, $actual);
	}

	public function test_to_string()
	{
		$expected = 'a value';
		$symbol = new Symbol($expected);
		$actual = $symbol->__toString();
		$this->assertEquals($expected, $actual);
	}
}

?>