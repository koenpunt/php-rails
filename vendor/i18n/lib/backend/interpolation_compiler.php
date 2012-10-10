<?php
namespace I18n\Backend;

use \I18n\MissingInterpolationArgument;
use \I18n\ReservedInterpolationKey;

# The InterpolationCompiler module contains optimizations that can tremendously
# speed up the interpolation process on the Simple backend.
#
# It works by defining a pre-compiled method on stored translation Strings that
# already bring all the knowledge about contained interpolation variables etc.
# so that the actual recurring interpolation will be very fast.
#
# To enable pre-compiled interpolations you can simply include the
# InterpolationCompiler module to the Simple backend:
#
#   I18n::Backend::Simple.include(I18n::Backend::InterpolationCompiler)
#
# Note that InterpolationCompiler does not yield meaningful results and consequently
# should not be used with Ruby 1.9 (YARV) but improves performance everywhere else
# (jRuby, Rubinius and 1.8.7).

class InterpolationCompiler{
	
	
	public static function interpolate($string, $values){
		if (!is_string($string) || empty($values)) {
			return $string;
		}
		preg_match('/\%{(.*?)}/', $string, $results);
		array_shift($results);

		foreach ($results as $key) {
			if (in_array($key, Base::$RESERVED_KEYS)) {
				throw new ReservedInterpolationKey($key, $string);
			}
		}

		// if value is an array, we cannot consider it a usable value
		foreach ($values as $key => $value) {
			if (is_array($value)) {
				unset($values[$key]);
			}
		}

		$difference = array_diff($results, array_keys($values));
		if (!empty($difference)) {
			throw new MissingInterpolationArgument($values, $string);
		}

		$keys = $vals = array();
		foreach ($values as $key => $value) {
			$keys[] = '%{' . $key . '}';
			$vals[] = $value;
		}

		return str_replace($keys, $vals, $string);
	}
}