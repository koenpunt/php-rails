<?php

require_once('../I18n.php');

use I18n\I18n;

class I18n_Test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		parent::__construct();
		I18n::set_backend(null);
		I18n::get_backend()->reload();
		I18n::set_locale('en');
		I18n::set_default_locale('en');
		I18n::set_default_separator('.');
		I18n::set_load_path(array(APP . '/test/test_data/locales/en.yml', APP . '/test/test_data/locales/fr.yml'));
	}

	public function test_get_backend()
	{
		$this->assertEquals(new \I18n\Backend\Base(), I18n::get_backend());
	}

	public function test_set_backend()
	{
		I18n::set_backend('abackend');
		$this->assertEquals('abackend', I18n::get_backend());
	}

	public function test_get_default_locale()
	{
		$this->assertEquals('en', I18n::get_default_locale());
	}

	public function test_set_default_locale()
	{
		I18n::set_default_locale('fr');
		$this->assertEquals('fr', I18n::get_default_locale());
	}

	public function test_get_locale()
	{
		$this->assertEquals('en', I18n::get_locale());
	}

	public function test_get_locale_with_null()
	{
		I18n::set_locale(null);
		$this->assertEquals('en', I18n::get_locale());
	}

	public function test_set_locale()
	{
		I18n::set_locale('fr');
		$this->assertEquals('fr', I18n::get_locale());
	}

	public function test_get_available_locales()
	{
		$this->assertEquals(array('en', 'fr'), I18n::get_available_locales());
	}

	public function test_set_available_locales()
	{
		I18n::set_available_locales(array('fr'));
		$this->assertEquals(array('fr'), I18n::get_available_locales());
	}

	public function test_get_default_separator()
	{
		$this->assertEquals('.', I18n::get_default_separator());
	}

	public function test_set_default_separator()
	{
		I18n::set_default_separator('/');
		$this->assertEquals('/', I18n::get_default_separator());
	}

	public function test_set_exception_handler()
	{

	}

	public function test_get_load_path()
	{

	}

	public function test_set_load_path()
	{

	}

	public function test_translate()
	{
		$this->assertEquals('Hello', I18n::translate('hello'));
	}

	public function test_translate_with_locale_option()
	{
		$actual = I18n::translate('hello', array('locale' => 'fr'));
		$expected = 'Bonjour';
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_exception()
	{
		$actual = I18n::translate_exception('hello', array('locale' => 'fr'));
		$expected = 'Bonjour';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\MissingTranslationData
	 */
	public function test_translate_exception_with_throw()
	{
		$actual = I18n::translate_exception('hello', array('locale' => 'xx'));
	}

	public function test_localize()
	{

	}

	public function test_normalize_keys()
	{

	}
}

?>