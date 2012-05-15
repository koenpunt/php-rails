<?php

require_once(dirname(__FILE__) . '/../I18n.php');

use I18n\Helpers;
use I18n\I18n;
use I18n\Date;
use I18n\Time;
use I18n\Backend\Base;

class Base_Test  extends PHPUnit_Framework_TestCase
{
	private $base = null;

	public function setUp()
	{
		// $this->base = new Base();
		// $this->base->load_translations(array(APP . '/test/test_data/locales/en.yml', APP . '/test/test_data/locales/fr.yml'));
		I18n::set_backend(null);
		I18n::push_load_path(array(APP . '/test/test_data/locales/en.yml'));
		$this->base = I18n::get_backend();
	}

	public function test_load_translations()
	{
		$this->base = null;
		$this->base = new Base();
		$this->assertEquals(array('en'), $this->base->available_locales());
		$this->base->load_translations(array(APP . '/test/test_data/locales/fr.yml'));
		$this->assertEquals(array('en', 'fr'), $this->base->available_locales());
	}

	/**
	 * @expectedException \I18n\UnknownFileType
	 */
	public function test_load_translations_invalid_extension()
	{
		$this->base = null;
		$this->base = new Base();
		$this->assertEquals(array('en'), $this->base->available_locales());
		$this->base->load_translations(array(APP . '/test/test_data/locales/invalid.ext'));
		$this->assertEquals(array('en'), $this->base->available_locales());
	}

	public function test_store_translations()
	{

	}

	public function test_translate()
	{
		$this->assertEquals('Hello', $this->base->translate('en', 'hello'));
	}

	public function test_translate_with_pluralize()
	{
		$actual = $this->base->translate('en', 'too_long', array('scope' => 'activerecord.errors.messages', 'default' => '' , 'count' => 5));
		$expected = 'is too long (maximum is 5 characters)';
		$this->assertEquals($expected, $actual);
		
		$actual = $this->base->translate('en', 'too_long', array('scope' => 'activerecord.errors.messages', 'default' => '' , 'count' => 1));
		$expected = 'is too long (maximum is 1 character)';
		
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_interpolation()
	{
		$actual = $this->base->translate('en', 'string_to_interpolate', array('scope' => '', 'default' => '', 'item' => 'banana', 'adjective' => 'yellow'));
		$expected = 'this banana is quite yellow';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\MissingInterpolationArgument
	 */
	public function test_translate_with_interpolation_with_array_values()
	{
		$actual = $this->base->translate('en', 'string_to_interpolate', array('item' => array('banana', 'carrot')));
		$expected = 'this banana is quite yellow';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\MissingInterpolationArgument
	 */
	public function test_translate_with_interpolation_without_required_values()
	{
		$actual = $this->base->translate('en', 'string_to_interpolate', array('_object' => 'banana', '_adjective' => 'yellow'));
		$expected = 'this banana is quite yellow';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\ReservedInterpolationKey
	 */
	public function test_translate_with_interpolation_string_with_reserved_key()
	{
		$actual = $this->base->translate('en', 'string_to_interpolate_with_reserved_key', array('_object' => 'banana', '_adjective' => 'yellow'));
		$expected = 'this banana is quite yellow';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_invalid_locale()
	{
		$this->base->translate('invalid', 'hello');
	}

	/**
	 * @expectedException \I18n\InvalidLocale
	 */
	public function test_translate_null_locale()
	{
		$this->base->translate(null, 'hello');
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_null_key()
	{
		$this->base->translate('en', null);
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_null_key_with_options()
	{
		$this->base->translate('en', null, array('scope' => '', 'anoption' => 'value'));
	}

	public function test_translate_with_array_scope()
	{
		$expected = 'is not included in the list';
		$actual = $this->base->translate('en', 'inclusion', array('scope' => array('activerecord', 'errors', 'messages')));
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_default_message_as_string()
	{
		$expected = 'this is a custom message';
		$actual = $this->base->translate('en', 'inclusion', array('default' => 'this is a custom message'));
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_default_message_as_symbol()
	{
		$expected = 'this is a custom message';
		$actual = $this->base->translate('en', 'inclusion', array('default' => Helpers\to_sym('custom_message')));
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_default_message_as_symbol_without_resolving()
	{
		$expected = Helpers\to_sym('this is a custom message');
		$actual = $this->base->translate('en', 'inclusion', array('default' => Helpers\to_sym('this is a custom message'), 'resolve' => false));
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_default_messages()
	{
		$expected = 'this is a custom message';
		$actual = $this->base->translate('en', 'inclusion', array('default' => array(Helpers\to_sym('another_custom_message'), Helpers\to_sym('custom_message'))));
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_with_default_messages_none_found()
	{
		$this->base->translate('en', 'inclusion', array('default' => array(Helpers\to_sym('another_custom_message'), Helpers\to_sym('custom_message_not_working'))));
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_with_default_message_and_incorrect_scope()
	{
		$this->base->translate('en', 'inclusion', array('default' => Helpers\to_sym('custom_message'), 'scope' => array('activerecord', 'errors', 'message')));
	}
	
	public function test_localize()
	{
		$object = Time::utc(2004, 6, 6, 21, 45, 0);
		$format = '%A, %B %e, %H:%M';
		$expected = 'Sunday, June  6, 21:45';
		$actual = $this->base->localize('en', $object, $format);
		$this->assertEquals($expected, $actual);
	}

	public function test_is_initialized()
	{
		// to initialize
		$this->base->available_locales();
		$this->assertTrue($this->base->is_initialized());
	}

	public function test_available_locales()
	{
		$this->assertEquals(array('en'), $this->base->available_locales());
	}

	public function test_available_locales_no_locales()
	{
		// $this->base->reload();
		I18n::set_load_path(null);
		$this->assertEquals(array(), $this->base->available_locales());
	}

	public function test_reload()
	{
		$this->base->available_locales();
		$this->assertTrue($this->base->is_initialized());
		$this->base->reload();
		$this->assertFalse($this->base->is_initialized());
	}

	public function test_load_php()
	{
		$this->base = null;
		$this->base = new Base();
		$this->assertEquals(array('en'), $this->base->available_locales());
		$this->base->load_translations(array(APP . '/test/test_data/locales/en.php'));
		$this->assertEquals(array('en'), $this->base->available_locales());
		$this->assertEquals('Hello 2', $this->base->translate('en', 'hello2'));
	}

	public function test_merge_translations()
	{
		$this->base = null;
		$this->base = new Base();
		$this->assertEquals(array('en'), $this->base->available_locales());
		$this->base->load_translations(array(APP . '/test/test_data/locales/en.yml', APP . '/test/test_data/locales/en.php'));
		$this->assertEquals(array('en'), $this->base->available_locales());
		$this->assertEquals('Hello 2', $this->base->translate('en', 'hello2'));
	}
}

?>