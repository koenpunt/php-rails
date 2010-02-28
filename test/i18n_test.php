<?php

require_once(dirname(__FILE__) . '/../I18n.php');

use I18n\I18n;

class I18n_Test extends PHPUnit_Framework_TestCase
{
	private $path = array();

	public function __construct()
	{
		parent::__construct();
		$this->path = array(APP . '/test/test_data/locales/en.yml', APP . '/test/test_data/locales/fr.yml');
	}

	public function setUp()
	{
		parent::__construct();
		I18n::set_backend(null);
		I18n::get_backend()->reload();
		I18n::set_locale('en');
		I18n::set_default_locale('en');
		I18n::set_default_separator('.');
		I18n::set_load_path($this->path);
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
		$this->assertEquals($this->path, I18n::get_load_path());
	}

	public function test_set_load_path()
	{
		$expected = 'test_load_path';
		I18n::set_load_path($expected);
		$this->assertEquals($expected, I18n::get_load_path());
	}

	public function test_get_load_path_null()
	{
		I18n::set_load_path(null);
		$expected = array();
		$actual = I18n::get_load_path();
		$this->assertEquals($expected, $actual);
	}

	public function test_push_load_path()
	{
		I18n::set_load_path(null);
		$expected = 'test_load_path';
		I18n::push_load_path($expected);
		$expected = array($expected);
		$actual = I18n::get_load_path();
		$this->assertEquals($expected, $actual);
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

	public function test_translate_null_key()
	{
		$expected = null;
		$actual = I18n::translate(null);
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_exception_with_exception_handler()
	{
		$expected = 'translation missing: xx, hello';
		$actual = I18n::translate('hello', array('locale' => 'xx'));
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

	// public function test_localize()
	// {
	//
	// }

	public function test_normalize_keys()
	{
		$expected = array('en', 'activerecord', 'errors', 'messages', 'invalid');
		$actual = I18n::normalize_keys('en', 'invalid', array('activerecord', 'errors', 'messages'));
		$this->assertEquals($expected, $actual);
	}

	public function test_normalize_keys_with_string_scope()
	{
		$expected = array('en', 'activerecord', 'errors', 'messages', 'invalid');
		$actual = I18n::normalize_keys('en', 'invalid', 'activerecord.errors.messages');
		$this->assertEquals($expected, $actual);
	}

	public function test_normalize_keys_with_key_symbol()
	{
		$expected = array('en', 'activerecord', 'errors', 'messages', 'invalid');
		$actual = I18n::normalize_keys('en', _s('invalid'), 'activerecord.errors.messages');
		$this->assertEquals($expected, $actual);
	}
}

?>