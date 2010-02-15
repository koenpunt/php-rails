<?php

require_once('../I18n.php');

class I18n_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		parent::__construct();
		I18n\I18n::set_locale('en');
		// I18n::backend->store_translations('en');
	}

	// public function test_uses_simple_backend_set_by_default()
	// {
	// 	// $this->assertTrue
	// }

	public function test_base()
	{
		 // $this->assertEquals('Hello', I18n\I18n::translate('hello'));
	}
}

?>