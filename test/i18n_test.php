<?php

require_once(dirname(__FILE__) . '/../I18n.php');

use I18n\Helpers;
use I18n\I18n;
use I18n\Date;
use I18n\Time;

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
		$exception_handler = function($exception){
			# Do nothing
		};
		I18n::set_exception_handler($exception_handler);
		
		$this->assertEquals($exception_handler, I18n::get_exception_handler());
		
		I18n::set_exception_handler(null);
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
	
	public function test_translate_array()
	{
		$expected = array('Hello', 'Hello world');
		$this->assertEquals($expected, I18n::translate(array('hello', 'hello_to.world'), array('locale' => 'en')));
	}
	

	public function test_translate_with_locale_option()
	{
		$actual = I18n::translate('hello', array('locale' => 'fr'));
		$expected = 'Bonjour';
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_null_key()
	{
		$expected = null;
		$actual = I18n::translate(null);
		$this->assertEquals($expected, $actual);
	}

	public function test_translate_with_custom_exception_handler()
	{
		$expected = 'translation missing: xx, hello';
		$actual = '';
		I18n::set_exception_handler(function($exception) use (&$actual){
			$actual = $exception->getMessage();
		});
		
		$_actual = I18n::translate('hello', array('locale' => 'xx'));
		$this->assertEquals($expected, $actual);
		I18n::set_exception_handler(null);
	}

	/**
	 * @expectedException \I18n\MissingTranslation
	 */
	public function test_translate_with_exception()
	{
		$actual = I18n::translate('hello', array('locale' => 'xx'));
	}

	public function test_localize_time()
	{
		$object = Time::utc(2004, 6, 6, 21, 45, 0);
		$format = '%A, %B %e, %H:%M';
		$expected = 'Sunday, June  6, 21:45';
		$actual = I18n::localize($object, array('format' => $format));
		$this->assertEquals($expected, $actual);
	}
	
	public function test_localize_date()
	{
		$object = new Date('2004/06/06');
		$format = '%A, %B %e';
		$expected = 'Sunday, June  6';
		$actual = I18n::localize($object, array('format' => $format));
		$this->assertEquals($expected, $actual);
	}
	
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
		$actual = I18n::normalize_keys('en', Helpers\to_sym('invalid'), 'activerecord.errors.messages');
		$this->assertEquals($expected, $actual);
	}
	
	public function test_with_options()
	{
		$user = new stdClass();
		$user->locale = 'en';
		$user->name = 'Koen';
		
		
		$expected_subject = 'Greetings';
		$expected_body = 'Hi Koen';
		$subject = $body = null;
		
		I18n::with_options(array('locale' => $user->locale, 'scope' => 'newsletter'), function($i18n) use ($user, &$subject, &$body){
			$subject = $i18n->t('subject');
			$body    = $i18n->t('body', array('user_name' => $user->name));
		});
		
		$this->assertEquals($expected_subject, $subject);
		$this->assertEquals($expected_body, $body);
		
	}
}

?>