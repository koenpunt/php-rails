<?php

namespace ActiveSupport\Multibyte;

\PHPRails::uses('active_support/multibyte');

class Unicode{
	
	# Replaces all ISO-8859-1 or CP1252 characters by their UTF-8 equivalent resulting in a valid UTF-8 string.
	#
	# Passing +true+ will forcibly tidy all bytes, assuming that the string's encoding is entirely CP1252 or ISO-8859-1.
	public static function tidy_bytes($data){
		/*
			TODO Port ruby source
		*/
		return utf8_encode($data);
		
		#if force
		#  return string.unpack("C*").map do |b|
		#    tidy_byte(b)
		#  end.flatten.compact.pack("C*").unpack("U*").pack("U*")
		#end
        #
		#bytes = string.unpack("C*")
		#conts_expected = 0
		#last_lead = 0
        #
		#bytes.each_index do |i|
        #
		#  byte          = bytes[i]
		#  is_cont       = byte > 127 && byte < 192
		#  is_lead       = byte > 191 && byte < 245
		#  is_unused     = byte > 240
		#  is_restricted = byte > 244
        #
		#  # Impossible or highly unlikely byte? Clean it.
		#  if is_unused || is_restricted
		#    bytes[i] = tidy_byte(byte)
		#  elsif is_cont
		#    # Not expecting continuation byte? Clean up. Otherwise, now expect one less.
		#    conts_expected == 0 ? bytes[i] = tidy_byte(byte) : conts_expected -= 1
		#  else
		#    if conts_expected > 0
		#      # Expected continuation, but got ASCII or leading? Clean backwards up to
		#      # the leading byte.
		#      (1..(i - last_lead)).each {|j| bytes[i - j] = tidy_byte(bytes[i - j])}
		#      conts_expected = 0
		#    end
		#    if is_lead
		#      # Final byte is leading? Clean it.
		#      if i == bytes.length - 1
		#        bytes[i] = tidy_byte(bytes.last)
		#      else
		#        # Valid leading byte? Expect continuations determined by position of
		#        # first zero bit, with max of 3.
		#        conts_expected = byte < 224 ? 1 : byte < 240 ? 2 : 3
		#        last_lead = i
		#      end
		#    end
		#  end
		#end
		#bytes.empty? ? "" : bytes.flatten.compact.pack("C*").unpack("U*").pack("U*")
	}
	
	# Returns the KC normalization of the string by default. NFKC is considered the best normalization form for
	# passing strings to databases and validations.
	#
	# * <tt>string</tt> - The string to perform normalization on.
	# * <tt>form</tt> - The form you want to normalize in. Should be one of the following:
	#   <tt>:c</tt>, <tt>:kc</tt>, <tt>:d</tt>, or <tt>:kd</tt>. Default is
	#   ActiveSupport::Multibyte.default_normalization_form
	public static function normalize($data){
		$form = $form ?: \ActiveSupport\Multibyte::$default_normalization_form;
		# See http://www.unicode.org/reports/tr15, Table 1
		switch($form){
			case 'd':
				return normalizer_normalize($data, \Normalizer::FORM_D);
			case 'c':
				return normalizer_normalize($data, \Normalizer::FORM_C);
			case 'kd':
				return normalizer_normalize($data, \Normalizer::FORM_KD);
			case 'kc':
				return normalizer_normalize($data, \Normalizer::FORM_KC);
			default:
				throw new \InvalidArgumentException("{$form} is not a valid normalization variant"); //, caller
		}
	}
}
	
	