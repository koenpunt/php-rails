<?php

namespace I18n;

use InvalidArgumentException;

class InvalidLocale extends InvalidArgumentException
{
	private $locale;

	public function __construct($locale)
	{
		$this->locale = $locale;
		parent::__construct("$locale is not a valid locale");
	}
}

class MissingTranslationData extends InvalidArgumentException
{
	private $key;
	private $locale;
	private $options;

	public function __construct($locale, $key, $options = array())
	{
		list($this->key, $this->locale, $this->options) = array($key, $locale, $options);
		$options['scope'] = isset($options['scope']) ? $options['scope'] : '';
		$keys = I18n::normalize_keys($locale, $key, $options['scope']);
		if (count($keys) < 2) {
			$keys[] = 'no key';
		}
		$keys = implode(', ', $keys);
		parent::__construct("translation missing: $keys");
	}
}

class InvalidPluralizationData extends InvalidArgumentException
{
	private $entry;
	private $count;

	public function __construct($entry, $count)
	{
		list($this->entry, $this->count) = array($entry, $count);
		$entry = var_dump($entry);
		parent::__construct("translation data $entry can not be used with count => $count");
	}
}

class MissingInterpolationArgument extends InvalidArgumentException
{
	private $values;
	private $string;

	public function __construct($values, $string)
	{
		list($this->values, $this->string) = array($values, $string);
		$string = var_dump($string);
		$values = var_dump($values);
		parent::__construct("missing interpolation argument in $string ($values given)");
	}
}

class ReservedInterpolationKey extends InvalidArgumentException
{
	private $key;
	private $string;

	public function __construct($key, $string)
	{
		list($this->key, $this->string) = array($key, $string);
		$key = var_dump($key);
		$string = var_dump($string);
		parent::__construct("reserved key $key used in $string");
	}
}

class UnknownFileType extends InvalidArgumentException
{
	private $type;
	private $filename;

	public function __construct($type, $filename)
	{
		list($this->type, $this->filename) = array($type, $filename);
		parent::__construct("can not load translations from $filename, the file type $type is not known");
	}
}

?>