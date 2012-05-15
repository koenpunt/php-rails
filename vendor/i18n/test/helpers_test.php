<?php

require_once(dirname(__FILE__) . '/../I18n.php');

use I18n\Helpers;

class Helpers_Test extends PHPUnit_Framework_TestCase
{
	public function test_array_flatten()
	{
		$this->assertEquals(array(), Helpers\array_flatten(array()));
		$this->assertEquals(array(1), Helpers\array_flatten(array(1)));
		$this->assertEquals(array(1), Helpers\array_flatten(array(array(1))));
		$this->assertEquals(array(1, 2), Helpers\array_flatten(array(array(1, 2))));
		$this->assertEquals(array(1, 2), Helpers\array_flatten(array(array(1), 2)));
		$this->assertEquals(array(1, 2), Helpers\array_flatten(array(1, array(2))));
		$this->assertEquals(array(1, 2, 3), Helpers\array_flatten(array(1, array(2), 3)));
		$this->assertEquals(array(1, 2, 3, 4), Helpers\array_flatten(array(1, array(2, 3), 4)));
		$this->assertEquals(array(1, 2, 3, 4, 5), Helpers\array_flatten(array(array(1, 2, 3), array(4, 5))));
	}

}
?>