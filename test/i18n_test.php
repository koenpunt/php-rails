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
}

?>