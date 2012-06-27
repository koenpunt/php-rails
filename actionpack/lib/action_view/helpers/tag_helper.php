<?php 
/**
 * TagHelper
 * 
 * Rails source: https://github.com/rails/rails/blob/master/actionpack/lib/action_view/helpers/tag_helper.rb
 * @package PHP Rails
 * @author Koen Punt
 */

#require 'active_support/core_ext/object/blank'
#require 'active_support/core_ext/string/output_safety'
#require 'set'

namespace ActionView\Helpers;
#module ActionView
	# = Action View Form Tag Helpers
	#module Helpers #:nodoc:
		# Provides methods to generate HTML tags programmatically when you can't use
		# a Builder. By default, they output XHTML compliant tags.

class TagHelper {
	#extends \ActiveSupport\Concern{
	
	# include CaptureHelper
	
	public static $BOOLEAN_ATTRIBUTES = array('disabled', 'readonly', 'multiple', 'checked', 'autobuffer', 'autoplay', 'controls', 'loop', 'selected', 'hidden', 'scoped', 'async', 'defer', 'reversed', 'ismap', 'seemless', 'muted', 'required', 'autofocus', 'novalidate', 'formnovalidate', 'open', 'pubdate');
	
	public static $PRE_CONTENT_STRINGS = array('textarea' => "\n");

	# Returns an empty HTML tag of type +name+ which by default is XHTML
	# compliant. Set +open+ to true to create an open tag compatible
	# with HTML 4.0 and below. Add HTML attributes by passing an attributes
	# hash to +options+. Set +escape+ to false to disable attribute value
	# escaping.
	#
	# ==== Options
	# You can use symbols or strings for the attribute names.
	#
	# Use +true+ with boolean attributes that can render with no value, like
	# +disabled+ and +readonly+.
	#
	# HTML5 <tt>data-*</tt> attributes can be set with a single +data+ key
	# pointing to a hash of sub-attributes.
	#
	# To play nicely with JavaScript conventions sub-attributes are dasherized.
	# For example, a key +user_id+ would render as <tt>data-user-id</tt> and
	# thus accessed as <tt>dataset.userId</tt>.
	#
	# Values are encoded to JSON, with the exception of strings and symbols.
	# This may come in handy when using jQuery's HTML5-aware <tt>.data()<tt>
	# from 1.4.3.
	#
	# ==== Examples
	#   tag("br")
	#   # => <br />
	#
	#   tag("br", nil, true)
	#   # => <br>
	#
	#   tag("input", :type => 'text', :disabled => true)
	#   # => <input type="text" disabled="disabled" />
	#
	#   tag("img", :src => "open & shut.png")
	#   # => <img src="open &amp; shut.png" />
	#
	#   tag("img", {:src => "open &amp; shut.png"}, false, false)
	#   # => <img src="open &amp; shut.png" />
	#
	#   tag("div", :data => {:name => 'Stephen', :city_state => %w(Chicago IL)})
	#   # => <div data-name="Stephen" data-city-state="[&quot;Chicago&quot;,&quot;IL&quot;]" />
	public static function tag($name, $options = null, $open = false, $escape = true){
		$tag_options = $options ? static::tag_options($options, $escape) : "";
		$open = $open ? ">" : " />";
		return \PHPRails\html_safe("<{$name} {$tag_options} {$open}");
	}

	# Returns an HTML block tag of type +name+ surrounding the +content+. Add
	# HTML attributes by passing an attributes hash to +options+.
	# Instead of passing the content as an argument, you can also use a block
	# in which case, you pass your +options+ as the second parameter.
	# Set escape to false to disable attribute value escaping.
	#
	# ==== Options
	# The +options+ hash is used with attributes with no value like (<tt>disabled</tt> and
	# <tt>readonly</tt>), which you can give a value of true in the +options+ hash. You can use
	# symbols or strings for the attribute names.
	#
	# ==== Examples
	#   content_tag(:p, "Hello world!")
	#    # => <p>Hello world!</p>
	#   content_tag(:div, content_tag(:p, "Hello world!"), :class => "strong")
	#    # => <div class="strong"><p>Hello world!</p></div>
	#   content_tag("select", options, :multiple => true)
	#    # => <select multiple="multiple">...options...</select>
	#
	#   <%= content_tag :div, :class => "strong" do -%>
	#     Hello world!
	#   <% end -%>
	#    # => <div class="strong">Hello world!</div>
	public static function content_tag($name, $content_or_options_with_block = null, $options = null, $escape = true){
		if($block){
			if(\PHPRails\is_hash($content_or_options_with_block)){
				$options = $content_or_options_with_block;
				$block = $options;
			}
			return static::content_tag_string($name, call_user_func($block), $options, $escape);
		}else{
			return static::content_tag_string($name, $content_or_options_with_block, $options, $escape);
		}
	}

	# Returns a CDATA section with the given +content+. CDATA sections
	# are used to escape blocks of text containing characters which would
	# otherwise be recognized as markup. CDATA sections begin with the string
	# <tt><![CDATA[</tt> and end with (and may not contain) the string <tt>]]></tt>.
	#
	# ==== Examples
	#   cdata_section("<hello world>")
	#   # => <![CDATA[<hello world>]]>
	#
	#   cdata_section(File.read("hello_world.txt"))
	#   # => <![CDATA[<hello from a text file]]>
	#
	#   cdata_section("hello]]>world")
	#   # => <![CDATA[hello]]]]><![CDATA[>world]]>
	public static function cdata_section($content){
		$splitted = str_replace(']]>', ']]]]><![CDATA[>', $content);
		return \PHPRails\html_safe("<![CDATA[{$splitted}]]>");
	}

	# Returns an escaped version of +html+ without affecting existing escaped entities.
	#
	# ==== Examples
	#   escape_once("1 < 2 &amp; 3")
	#   # => "1 &lt; 2 &amp; 3"
	#
	#   escape_once("&lt;&lt; Accept & Checkout")
	#   # => "&lt;&lt; Accept &amp; Checkout"
	public static function escape_once($html){
		return preg_replace('/&amp;([a-z]+|(#\d+)|(#x[\da-f]+));/i', '&$1;', htmlspecialchars($html, ENT_COMPAT));
		#ActiveSupport::Multibyte.clean(html.to_s).gsub(/[\"><]|&(?!([a-zA-Z]+|(#\d+));)/) { |special| ERB::Util::HTML_ESCAPE[special] }
		#ERB::Util.html_escape_once(html)
	}
	
	/**
	 * Not Rails default method
	 *
	 * @param string $raw 
	 * @return void
	 * @author Koen Punt
	 */
	public static function extract_tag_options($raw){
		$tag_options = array();
		if(preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]+)=(["\'])(.*?)\\2/', $raw, $raw_tag_options)){
			$tag_options = array_combine($raw_tag_options[1], $raw_tag_options[3]);
		}
		return $tag_options;
	}

	private static function content_tag_string($name, $content, $options, $escape = true){
		$tag_options = \PHPRails\is_hash($options) ? static::tag_options($options, $escape) : "";
		$content = \PHPRails\html_safe__($content) ? $content : ( $escape ? htmlspecialchars($content) : $content);
		$pre_content_string = isset(static::$PRE_CONTENT_STRINGS['name']) ? static::$PRE_CONTENT_STRINGS['name'] : '';
		return \PHPRails\html_safe("<{$name} {$tag_options}>{$pre_content_string}{$content}</{$name}>");
	}
	
	private static function tag_options($options, $escape = true){
		if(empty($options))return;
		$attrs = array();
		foreach($options as $key => $value){
			if($key == 'data' && is_array($value)){
				foreach($value as $k => $v){
					array_push($attrs, static::data_tag_option($k, $v, $escape));
				}
			}elseif(in_array($key, static::$BOOLEAN_ATTRIBUTES)){
				if($value){
					array_push($attrs, static::boolean_tag_option($key));
				}
			}elseif(!is_null($value)){
				array_push($attrs, static::tag_option($key, $value, $escape));
			}
		}
		sort($attrs);
		
		if(count($attrs)){
			return implode(" ", $attrs);
		}
	}
	
	private static function data_tag_option($key, $value, $escape){
		$key = 'data-' . \ActiveSupport\Inflector::dasherize($key);
		if(!is_string($v) && !is_object($value)){
			$value = json_encode($value);
		}
		return static::tag_option($key, $value, $escape);
	}
	
	private static function boolean_tag_option($key){
		return "{$key}=\"{$key}\"";
	}
	
	private static function tag_option($key, $value, $escape){
		$value = is_array($value) ? implode(" ", $value) : $value;
		$value = \PHPRails\html_safe__($value) ? $value : ( $escape ? htmlspecialchars($value) : $value);
		return "{$key}=\"{$value}\"";
	}

}
