<?php

require_once('../I18n.php');

use I18n\Backend\Base;

class Base_Test  extends PHPUnit_Framework_TestCase
{
	private $base = null;

	public function setUp()
	{
		$this->base = new Base();
		$this->base->load_translations(array(APP . '/test/test_data/locales/en.yml', APP . '/test/test_data/locales/fr.yml'));
	}

	public function test_load_translations()
	{
		$this->base = null;
		$this->base = new Base();
		$this->assertEquals(array(), $this->base->available_locales());
		$this->base->load_translations(array(APP . '/test/test_data/locales/en.yml'));
		$this->assertNotEquals(array(), $this->base->available_locales());
	}

	/**
	 * @expectedException \I18n\UnknownFileType
	 */
	public function test_load_translations_invalid_extension()
	{
		$this->base = null;
		$this->base = new Base();
		$this->assertEquals(array(), $this->base->available_locales());
		$this->base->load_translations(array(APP . '/test/test_data/locales/invalid.ext'));
		$this->assertEquals(array(), $this->base->available_locales());
	}

	public function test_store_translations()
	{

	}

	public function test_translate()
	{
		$this->assertEquals('Hello', $this->base->translate('en', 'hello'));
	}

	public function test_translate_array()
	{
		$expected = array('Hello', 'Hello world');
		$this->assertEquals($expected, $this->base->translate('en', array('hello', 'hello_to.world')));
	}

	public function test_translate_with_pluralize()
	{
		$actual = $this->base->translate('en', 'too_long', array('scope' => 'activerecord.errors.messages', 'default' => '' , 'count' => 5));
		$expected = 'is too long (maximum is 5 characters)';
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_interpolation()
	{
		$actual = $this->base->translate('en', 'string_to_interpolate', array('scope' => '', 'default' => '', 'object' => 'banana', 'adjective' => 'yellow'));
		$expected = 'this banana is quite yellow';
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_invalid_locale()
	{
		$actual = $this->base->translate('invalid', 'hello');
		// $expected = 'this banana is quite yellow';
		// $this->assertEquals($expected, $actual);
	}

	public function test_translate_null_locale()
	{
		$actual = $this->base->translate(null, 'hello');
		// $expected = 'this banana is quite yellow';
		// $this->assertEquals($expected, $actual);
	}

	public function test_localize()
	{

	}

	public function test_is_initialized()
	{
		// to initialize
		$this->base->available_locales();
		$this->assertTrue($this->base->is_initialized());
	}

	public function test_available_locales()
	{
		$this->assertEquals(array('en', 'fr'), $this->base->available_locales());
	}

	public function test_reload()
	{
		$this->base->available_locales();
		$this->assertTrue($this->base->is_initialized());
		$this->base->reload();
		$this->assertFalse($this->base->is_initialized());
	}
}

?>