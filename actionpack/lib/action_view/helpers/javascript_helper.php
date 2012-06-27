<?php 

namespace ActionView\Helpers;

\PHPRails::import('action_view/helpers/tag_helper');


class JavaScriptHelper{
	static $JS_ESCAPE_MAP = array(
		'\\'    => '\\\\',
		'</'    => '<\/',
		"\r\n"  => '\n',
		"\n"    => '\n',
		"\r"    => '\n',
		'"'     => '\\"',
		"'"     => "\\'"
	);

	#JS_ESCAPE_MAP["\342\200\250".force_encoding('UTF-8').encode!] = '&#x2028;'
	#JS_ESCAPE_MAP["\342\200\251".force_encoding('UTF-8').encode!] = '&#x2029;'

	# Escapes carriage returns and single and double quotes for JavaScript segments.
	#
	# Also available through the alias j(). This is particularly helpful in JavaScript responses, like:
	#
	#   $('some_element').replaceWith('<%=j render 'some/element_template' %>');
	public static function escape_javascript($javascript){
		if( $javascript ){
			$result = preg_replace_callback('/(\\|<\/|\r\n|\342\200\250|\342\200\251|[\n\r"\'])/u', function($match){
				return static::$JS_ESCAPE_MAP[$match];
			}, $javascript);
			return \PHPRails\html_safe__($javascript) ? \PHPRails\html_safe($result) : $result;
		}else{
			return '';
		}
	}
	# alias
	public static function j($javascript){
		return static::escape_javascript($javascript);
	}

	# Returns a JavaScript tag with the +content+ inside. Example:
	#   javascript_tag "alert('All is good')"
	#
	# Returns:
	#   <script>
	#   //<![CDATA[
	#   alert('All is good')
	#   //]]>
	#   </script>
	#
	# +html_options+ may be a hash of attributes for the <tt>\<script></tt>
	# tag. Example:
	#   javascript_tag "alert('All is good')", :defer => 'defer'
	#   # => <script defer="defer">alert('All is good')</script>
	#
	# Instead of passing the content as an argument, you can also use a block
	# in which case, you pass your +html_options+ as the first parameter.
	#   <%= javascript_tag :defer => 'defer' do -%>
	#     alert('All is good')
	#   <% end -%>
	public static function javascript_tag($content_or_options_with_block = null, $html_options = array()/*, &$block*/){
		$args = func_get_args();
		if($block = \PHPRails\block_given__($args)){
			if( \PHPRails\is_hash($content_or_options_with_block) ){
				$html_options = $content_or_options_with_block;
			}
			$content = call_user_func($block);
		}else{
			$content = $content_or_options_with_block;
		}
		return TagHelper::content_tag('script', static::javascript_cdata_section($content), $html_options);
	}

	public static function javascript_cdata_section($content){
		$cdata_content = TagHelper::cdata_section("\n{$content}\n//");
		return \PHPRails\html_safe("\n//{$cdata_content}\n");
	}
}
