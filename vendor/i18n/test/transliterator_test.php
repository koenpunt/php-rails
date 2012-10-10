<?php

require_once(dirname(__FILE__) . '/../I18n.php');

function uchr($codes) {
	if (is_scalar($codes)) $codes= func_get_args();
	$str= '';
	foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
	return $str;
}

class I18nBackendTransliterator_Test extends PHPUnit_Framework_TestCase{
  
	public function setUp(){
		I18n\I18n::set_backend(new I18n\Backend\Simple());
		$this->proc = function($n){ return strtoupper($n); };
		$this->hash = array("ü" => "ue", "ö" => "oe");
		$this->transliterator = I18n\Backend\Transliterator::get();
	}

	public function test_transliteration_rule_can_be_a_proc(){
		I18n\I18n::get_backend()->store_translations('xx', array('i18n' => array('transliterate' => array('rule' => $this->proc))));
		$this->assertEquals( "HELLO", I18n\I18n::get_backend()->transliterate('xx', "hello") );
	}

	public function test_transliteration_rule_can_be_a_hash(){
		I18n\I18n::get_backend()->store_translations('xx', array('i18n' => array('transliterate' => array('rule' => $this->hash))));
		$this->assertEquals( "ue", I18n\I18n::get_backend()->transliterate('xx', "ü") );
	}
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_transliteration_rule_must_be_a_proc_or_hash(){
		I18n\I18n::get_backend()->store_translations('xx', array('i18n' => array('transliterate' => array('rule' => ""))));
		I18n\I18n::get_backend()->transliterate('xx', "ü");
	}

	public function test_transliterator_defaults_to_latin_to_ascii_when_no_rule_is_given(){
		$this->assertEquals( "AEroskobing", I18n\I18n::get_backend()->transliterate('xx', "Ærøskøbing") );
	}

	public function test_default_transliterator_should_not_modify_ascii_characters(){
		for($byte = 0; $byte < 127; $byte ++){
			$char = uchr($byte);
			$this->assertEquals( $char, $this->transliterator->transliterate($char) );
		}
	}

	public function test_default_transliterator_correctly_transliterates_latin_characters(){
		# create string with range of Unicode's western characters with
		# diacritics, excluding the division and multiplication signs which for
		# some reason or other are floating in the middle of all the letters.
		#
		#	string = (0xC0..0x17E).to_a.reject {|c| [0xD7, 0xF7].include? c}.pack("U*");
		#	chars = string.split(//)
		$chars = array_map('uchr', array_filter(range(0xC0, 0x17E), function($c){
			return !in_array($c, array(0xD7, 0xF7));
		}));
		foreach($chars as $index => $char){
			$this->assertRegExp( "/^[a-zA-Z']*$/", $this->transliterator->transliterate($char) );
		}
	}

	public function test_should_replace_non_ASCII_chars_not_in_map_with_a_replacement_char(){
		$this->assertEquals( "abc?", $this->transliterator->transliterate("abcſ") );
	}

	public function test_can_replace_non_ASCII_chars_not_in_map_with_a_custom_replacement_string(){
		$this->assertEquals( "abc#", $this->transliterator->transliterate("abcſ", "#"));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_default_transliterator_raises_errors_for_invalid_UTF_8(){
		$this->transliterator->transliterate("a\x92b");
	}

	public function test_I18n_transliterate_should_transliterate_using_a_default_transliterator(){
		$this->assertEquals( "aeo", I18n\I18n::transliterate("áèö") );
	}

	public function test_I18n_transliterate_should_transliterate_using_a_locale(){
		I18n\I18n::get_backend()->store_translations('xx', array('i18n' => 
			array('transliterate' => array('rule' => $this->hash))
		));
		$this->assertEquals( "ue", I18n\I18n::transliterate("ü", array('locale' => 'xx')) );
	}

	public function test_default_transliterator_fails_with_custom_rules_with_uncomposed_input(){
		$char = uchr(117, 776); # "ü" as ASCII "u" plus COMBINING DIAERESIS
		$transliterator = I18n\Backend\Transliterator::get($this->hash);
		$this->assertNotEquals( "ue", $transliterator->transliterate($char) );
	}
	
}
