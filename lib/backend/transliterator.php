<?php

namespace I18n\Backend;

class Transliterator{
	const DEFAULT_REPLACEMENT_CHAR = "?";

	protected $transliterators = null;
	
	# Given a locale and a UTF-8 string, return the locale's ASCII
	# approximation for the string.
	public function transliterate($locale, $string, $replacement = null){
		$this->transliterators = $this->transliterators ?: array();
		$this->transliterators[$locale] = $this->transliterators[$locale] ?: Transliterator::get( \I18n\I18n::t('i18n.transliterate.rule',
			array('locale' => $locale, 'resolve' => false, 'default' => array())));
		return $this->transliterators[$locale]->transliterate($string, $replacement);
	}

	# Get a transliterator instance.
	public static function get($rule = null){
		if( !$rule || Helpers\is_hash($rule) ){
			return new HashTransliterator($rule);
		}elseif( $rule instanceof Closure ){
			return new ProcTransliterator($rule);
		}else{
			throw new \InvalidArgumentException("Transliteration rule must be a proc or a hash.");
		}
	}
}

# A transliterator which accepts a Proc as its transliteration rule.
class ProcTransliterator{
	
	protected $rule;

	public function __construct($rule){
		$this->rule = $rule;
	}

	public function transliterate($string, $replacement = null){
		return call_user_func($this->rule, $string);
	}
}

# A transliterator which accepts a Hash of characters as its translation
# rule.
class HashTransliterator{
	
	protected $approximations = null;
	
	protected $rule;
	
	static $DEFAULT_APPROXIMATIONS = array(
		"À"=>"A", "Á"=>"A", "Â"=>"A", "Ã"=>"A", "Ä"=>"A", "Å"=>"A", "Æ"=>"AE",
		"Ç"=>"C", "È"=>"E", "É"=>"E", "Ê"=>"E", "Ë"=>"E", "Ì"=>"I", "Í"=>"I",
		"Î"=>"I", "Ï"=>"I", "Ð"=>"D", "Ñ"=>"N", "Ò"=>"O", "Ó"=>"O", "Ô"=>"O",
		"Õ"=>"O", "Ö"=>"O", "×"=>"x", "Ø"=>"O", "Ù"=>"U", "Ú"=>"U", "Û"=>"U",
		"Ü"=>"U", "Ý"=>"Y", "Þ"=>"Th", "ß"=>"ss", "à"=>"a", "á"=>"a", "â"=>"a",
		"ã"=>"a", "ä"=>"a", "å"=>"a", "æ"=>"ae", "ç"=>"c", "è"=>"e", "é"=>"e",
		"ê"=>"e", "ë"=>"e", "ì"=>"i", "í"=>"i", "î"=>"i", "ï"=>"i", "ð"=>"d",
		"ñ"=>"n", "ò"=>"o", "ó"=>"o", "ô"=>"o", "õ"=>"o", "ö"=>"o", "ø"=>"o",
		"ù"=>"u", "ú"=>"u", "û"=>"u", "ü"=>"u", "ý"=>"y", "þ"=>"th", "ÿ"=>"y",
		"Ā"=>"A", "ā"=>"a", "Ă"=>"A", "ă"=>"a", "Ą"=>"A", "ą"=>"a", "Ć"=>"C",
		"ć"=>"c", "Ĉ"=>"C", "ĉ"=>"c", "Ċ"=>"C", "ċ"=>"c", "Č"=>"C", "č"=>"c",
		"Ď"=>"D", "ď"=>"d", "Đ"=>"D", "đ"=>"d", "Ē"=>"E", "ē"=>"e", "Ĕ"=>"E",
		"ĕ"=>"e", "Ė"=>"E", "ė"=>"e", "Ę"=>"E", "ę"=>"e", "Ě"=>"E", "ě"=>"e",
		"Ĝ"=>"G", "ĝ"=>"g", "Ğ"=>"G", "ğ"=>"g", "Ġ"=>"G", "ġ"=>"g", "Ģ"=>"G",
		"ģ"=>"g", "Ĥ"=>"H", "ĥ"=>"h", "Ħ"=>"H", "ħ"=>"h", "Ĩ"=>"I", "ĩ"=>"i",
		"Ī"=>"I", "ī"=>"i", "Ĭ"=>"I", "ĭ"=>"i", "Į"=>"I", "į"=>"i", "İ"=>"I",
		"ı"=>"i", "Ĳ"=>"IJ", "ĳ"=>"ij", "Ĵ"=>"J", "ĵ"=>"j", "Ķ"=>"K", "ķ"=>"k",
		"ĸ"=>"k", "Ĺ"=>"L", "ĺ"=>"l", "Ļ"=>"L", "ļ"=>"l", "Ľ"=>"L", "ľ"=>"l",
		"Ŀ"=>"L", "ŀ"=>"l", "Ł"=>"L", "ł"=>"l", "Ń"=>"N", "ń"=>"n", "Ņ"=>"N",
		"ņ"=>"n", "Ň"=>"N", "ň"=>"n", "ŉ"=>"'n", "Ŋ"=>"NG", "ŋ"=>"ng",
		"Ō"=>"O", "ō"=>"o", "Ŏ"=>"O", "ŏ"=>"o", "Ő"=>"O", "ő"=>"o", "Œ"=>"OE",
		"œ"=>"oe", "Ŕ"=>"R", "ŕ"=>"r", "Ŗ"=>"R", "ŗ"=>"r", "Ř"=>"R", "ř"=>"r",
		"Ś"=>"S", "ś"=>"s", "Ŝ"=>"S", "ŝ"=>"s", "Ş"=>"S", "ş"=>"s", "Š"=>"S",
		"š"=>"s", "Ţ"=>"T", "ţ"=>"t", "Ť"=>"T", "ť"=>"t", "Ŧ"=>"T", "ŧ"=>"t",
		"Ũ"=>"U", "ũ"=>"u", "Ū"=>"U", "ū"=>"u", "Ŭ"=>"U", "ŭ"=>"u", "Ů"=>"U",
		"ů"=>"u", "Ű"=>"U", "ű"=>"u", "Ų"=>"U", "ų"=>"u", "Ŵ"=>"W", "ŵ"=>"w",
		"Ŷ"=>"Y", "ŷ"=>"y", "Ÿ"=>"Y", "Ź"=>"Z", "ź"=>"z", "Ż"=>"Z", "ż"=>"z",
		"Ž"=>"Z", "ž"=>"z"
	);

	public function __construct($rule = null){
		$this->rule = $rule;
		$this->add( self::$DEFAULT_APPROXIMATIONS );
		if( $rule ){
			$this->add( $rule );
		}
	}

	public function transliterate($string, $replacement = null){
		return preg_replace_callback('/[^\x00-\x7f]/u', function($char) use ($replacement){
			return $this->approximations[$char] ?: $replacement ?: Transliterator::DEFAULT_REPLACEMENT_CHAR;
		}, $string);
	}
	
	private function approximations(){
		$this->approximations = $this->approximations ?: array();
	}

	# Add transliteration rules to the approximations hash.
	private function add($hash){
		#$keys = array_keys($hash);
		#foreach($keys as $key){
		#	$hash[(string)$key] = (string)Helpers\delete($hash, $key);
		#}
		#hash.keys.each {|key| hash[key.to_s] = hash.delete(key).to_s}
		$this->approximations = array_merge($this->approximations, $hash);
	}
}
