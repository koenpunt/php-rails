<?php

require_once('../I18n.php');

class I18n_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		parent::__construct();
		I18n\I18n::set_locale('en');
		I18n\I18n::set_load_path(array(APP . '/test/test_data/locales/en.yml', APP . '/test/test_data/locales/fr.yml'));
	}

	public function test_base()
	{
		$this->assertEquals('Hello', I18n\I18n::translate('hello'));
		$this->assertEquals('Bonjour', I18n\I18n::translate('hello', array('locale' => 'fr')));
	}

	public function test_dot()
	{
		$this->assertEquals('Hello world', I18n\I18n::translate('hello_to.world'));
	}

	public function test_with_attribute()
	{
		$this->assertEquals('is too long (maximum is 5 characters)', I18n\I18n::translate('too_long', array('scope' => 'activerecord.errors.messages', 'default' => '' , 'count' => 5)));
		$this->assertEquals('this banana is quite yellow', I18n\I18n::translate('string_to_interpolate', array('scope' => '', 'default' => '', 'object' => 'banana', 'adjective' => 'yellow')));
	}
}

?>