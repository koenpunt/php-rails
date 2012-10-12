<?php 
/**
 * FormTagHelper
 * 
 * Rails source: https://github.com/rails/rails/blob/master/actionpack/lib/action_view/helpers/form_tag_helper.rb
 * @package PHP Rails
 * @author Koen Punt
 */

namespace ActionView\Helpers;

#if(!class_exists('\UrlHelper')){
use UrlHelper as UrlHelper;
#}

#require 'cgi'
\PHPRails::import('action_view/helpers/tag_helper');
#require 'active_support/core_ext/object/blank'
#require 'active_support/core_ext/string/output_safety'
#require 'active_support/core_ext/module/attribute_accessors'

#module ActionView
	# = Action View Form Tag Helpers
	#module Helpers
		# Provides a number of methods for creating form tags that doesn't rely on an Active Record object assigned to the template like
		# FormHelper does. Instead, you provide the names and values manually.
		#
		# NOTE: The HTML options <tt>disabled</tt>, <tt>readonly</tt>, and <tt>multiple</tt> can all be treated as booleans. So specifying
		# <tt>:disabled => true</tt> will give <tt>disabled="disabled"</tt>.

class FormTagHelper{
#	extends \ActiveSupport\Concern{
		
	#include UrlHelper
	#include TextHelper

	#mattr_accessor :embed_authenticity_token_in_remote_forms
	static $embed_authenticity_token_in_remote_forms = false;

	# Starts a form tag that points the action to an url configured with <tt>url_for_options</tt> just like
	# ActionController::Base#url_for. The method for the form defaults to POST.
	#
	# ==== Options
	# * <tt>:multipart</tt> - If set to true, the enctype is set to "multipart/form-data".
	# * <tt>:method</tt> - The method to use when submitting the form, usually either "get" or "post".
	#   If "put", "delete", or another verb is used, a hidden input with name <tt>_method</tt>
	#   is added to simulate the verb over post.
	# * <tt>:authenticity_token</tt> - Authenticity token to use in the form. Use only if you need to
	#   pass custom authenticity token string, or to not add authenticity_token field at all
	#   (by passing <tt>false</tt>).  Remote forms may omit the embedded authenticity token
	#   by setting <tt>config.action_view.embed_authenticity_token_in_remote_forms = false</tt>.
	#   This is helpful when you're fragment-caching the form. Remote forms get the
	#   authenticity from the <tt>meta</tt> tag, so embedding is unnecessary unless you
	#   support browsers without JavaScript.
	# * A list of parameters to feed to the URL the form will be posted to.
	# * <tt>:remote</tt> - If set to true, will allow the Unobtrusive JavaScript drivers to control the
	#   submit behavior. By default this behavior is an ajax submit.
	#
	# ==== Examples
	#   form_tag('/posts')
	#   # => <form action="/posts" method="post">
	#
	#   form_tag('/posts/1', :method => :put)
	#   # => <form action="/posts/1" method="post"> ... <input name="_method" type="hidden" value="put" /> ...
	#
	#   form_tag('/upload', :multipart => true)
	#   # => <form action="/upload" method="post" enctype="multipart/form-data">
	#
	#   <%= form_tag('/posts') do -%>
	#     <div><%= submit_tag 'Save' %></div>
	#   <% end -%>
	#   # => <form action="/posts" method="post"><div><input type="submit" name="submit" value="Save" /></div></form>
	#
	#   <%= form_tag('/posts', :remote => true) %>
	#   # => <form action="/posts" method="post" data-remote="true">
	#
	#   form_tag('http://far.away.com/form', :authenticity_token => false)
	#   # form without authenticity token
	#
	#   form_tag('http://far.away.com/form', :authenticity_token => "cf50faa3fe97702ca1ae")
	#   # form with custom authenticity token
	#
	public static function form_tag($url_for_options = array(), $options = array()/*, &$block */){
		$args = func_get_args();
		$html_options = static::html_options_for_form($url_for_options, $options);
		if($block = \PHPRails\block_given__($args)){
			return static::form_tag_in_block($html_options, $block);
		}else{
			return static::form_tag_html($html_options);
		}
	}

	# Creates a dropdown selection box, or if the <tt>:multiple</tt> option is set to true, a multiple
	# choice selection box.
	#
	# Helpers::FormOptions can be used to create common select boxes such as countries, time zones, or
	# associated records. <tt>option_tags</tt> is a string containing the option tags for the select box.
	#
	# ==== Options
	# * <tt>:multiple</tt> - If set to true the selection will allow multiple choices.
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * <tt>:include_blank</tt> - If set to true, an empty option will be create
	# * <tt>:prompt</tt> - Create a prompt option with blank value and the text asking user to select something
	# * Any other key creates standard HTML attributes for the tag.
	#
	# ==== Examples
	#   select_tag "people", options_from_collection_for_select(@people, "id", "name")
	#   # <select id="people" name="people"><option value="1">David</option></select>
	#
	#   select_tag "people", "<option>David</option>".html_safe
	#   # => <select id="people" name="people"><option>David</option></select>
	#
	#   select_tag "count", "<option>1</option><option>2</option><option>3</option><option>4</option>".html_safe
	#   # => <select id="count" name="count"><option>1</option><option>2</option>
	#   #    <option>3</option><option>4</option></select>
	#
	#   select_tag "colors", "<option>Red</option><option>Green</option><option>Blue</option>".html_safe, :multiple => true
	#   # => <select id="colors" multiple="multiple" name="colors[]"><option>Red</option>
	#   #    <option>Green</option><option>Blue</option></select>
	#
	#   select_tag "locations", "<option>Home</option><option selected="selected">Work</option><option>Out</option>".html_safe
	#   # => <select id="locations" name="locations"><option>Home</option><option selected='selected'>Work</option>
	#   #    <option>Out</option></select>
	#
	#   select_tag "access", "<option>Read</option><option>Write</option>".html_safe, :multiple => true, :class => 'form_input'
	#   # => <select class="form_input" id="access" multiple="multiple" name="access[]"><option>Read</option>
	#   #    <option>Write</option></select>
	#
	#   select_tag "people", options_from_collection_for_select(@people, "id", "name"), :include_blank => true
	#   # => <select id="people" name="people"><option value=""></option><option value="1">David</option></select>
	#
	#   select_tag "people", options_from_collection_for_select(@people, "id", "name"), :prompt => "Select something"
	#   # => <select id="people" name="people"><option value="">Select something</option><option value="1">David</option></select>
	#
	#   select_tag "destination", "<option>NYC</option><option>Paris</option><option>Rome</option>".html_safe, :disabled => true
	#   # => <select disabled="disabled" id="destination" name="destination"><option>NYC</option>
	#   #    <option>Paris</option><option>Rome</option></select>
	public static function select_tag($name, $option_tags = null, $options = array()){
		$html_name = $options['multiple'] == true && substr($name, -2) !== '[]' ? "{$name}[]" : $name;

		if(\PHPRails\delete($options, 'include_blank')){
			$option_tags = TagHelper::content_tag('option', '', array('value' => '')) . $option_tags;
		}

		if(($prompt = \PHPRails\delete($options, 'prompt'))){
			$option_tags = TagHelper::content_tag('option', $prompt, array('value' => '')) . $option_tags;
		}

		return TagHelper::content_tag('select', \PHPRails\html_safe($option_tags), array_merge(array("name" => $html_name, "id" => static::sanitize_to_id($name)), $options));
	}

	# Creates a standard text field; use these text fields to input smaller chunks of text like a username
	# or a search query.
	#
	# ==== Options
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * <tt>:size</tt> - The number of visible characters that will fit in the input.
	# * <tt>:maxlength</tt> - The maximum number of characters that the browser will allow the user to enter.
	# * <tt>:placeholder</tt> - The text contained in the field by default which is removed when the field receives focus.
	# * Any other key creates standard HTML attributes for the tag.
	#
	# ==== Examples
	#   text_field_tag 'name'
	#   # => <input id="name" name="name" type="text" />
	#
	#   text_field_tag 'query', 'Enter your search query here'
	#   # => <input id="query" name="query" type="text" value="Enter your search query here" />
	#
	#   text_field_tag 'search', nil, :placeholder => 'Enter search term...'
	#   # => <input id="search" name="search" placeholder="Enter search term..." type="text" />
	#
	#   text_field_tag 'request', nil, :class => 'special_input'
	#   # => <input class="special_input" id="request" name="request" type="text" />
	#
	#   text_field_tag 'address', '', :size => 75
	#   # => <input id="address" name="address" size="75" type="text" value="" />
	#
	#   text_field_tag 'zip', nil, :maxlength => 5
	#   # => <input id="zip" maxlength="5" name="zip" type="text" />
	#
	#   text_field_tag 'payment_amount', '$0.00', :disabled => true
	#   # => <input disabled="disabled" id="payment_amount" name="payment_amount" type="text" value="$0.00" />
	#
	#   text_field_tag 'ip', '0.0.0.0', :maxlength => 15, :size => 20, :class => "ip-input"
	#   # => <input class="ip-input" id="ip" maxlength="15" name="ip" size="20" type="text" value="0.0.0.0" />
	public static function text_field_tag($name, $value = null, $options = array()){
		return TagHelper::tag('input', array_merge(array( "type" => "text", "name" => $name, "id" => static::sanitize_to_id($name), "value" => $value), $options));
	}

	# Creates a label element. Accepts a block.
	#
	# ==== Options
	# * Creates standard HTML attributes for the tag.
	#
	# ==== Examples
	#   label_tag 'name'
	#   # => <label for="name">Name</label>
	#
	#   label_tag 'name', 'Your name'
	#   # => <label for="name">Your Name</label>
	#
	#   label_tag 'name', nil, :class => 'small_label'
	#   # => <label for="name" class="small_label">Name</label>
	public static function label_tag($name = null, $content_or_options = null, $options = null/*, &$block */){
		$args = func_get_args();
		if($block = \PHPRails\block_given__($args) && \PHPRails\is_hash($content_or_options)){
			$options = $content_or_options;
		}else{
			$options = $options ?: array();
		}
		if(!(array_key_exists('for', $options) || empty($name))){
			$options["for"] = static::sanitize_to_id($name);
		} 
		return TagHelper::content_tag('label', ($content_or_options ?: Inflector::humanize($name)), $options, $block);
	}

	# Creates a hidden form input field used to transmit data that would be lost due to HTTP's statelessness or
	# data that should be hidden from the user.
	#
	# ==== Options
	# * Creates standard HTML attributes for the tag.
	#
	# ==== Examples
	#   hidden_field_tag 'tags_list'
	#   # => <input id="tags_list" name="tags_list" type="hidden" />
	#
	#   hidden_field_tag 'token', 'VUBJKB23UIVI1UU1VOBVI@'
	#   # => <input id="token" name="token" type="hidden" value="VUBJKB23UIVI1UU1VOBVI@" />
	#
	#   hidden_field_tag 'collected_input', '', :onchange => "alert('Input collected!')"
	#   # => <input id="collected_input" name="collected_input" onchange="alert('Input collected!')"
	#   #    type="hidden" value="" />
	public static function hidden_field_tag($name, $value = null, $options = array()){
		return static::text_field_tag($name, $value, array_merge($options, array("type" => "hidden")));
	}

	# Creates a file upload field. If you are using file uploads then you will also need
	# to set the multipart option for the form tag:
	#
	#   <%= form_tag '/upload', :multipart => true do %>
	#     <label for="file">File to Upload</label> <%= file_field_tag "file" %>
	#     <%= submit_tag %>
	#   <% end %>
	#
	# The specified URL will then be passed a File object containing the selected file, or if the field
	# was left blank, a StringIO object.
	#
	# ==== Options
	# * Creates standard HTML attributes for the tag.
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	#
	# ==== Examples
	#   file_field_tag 'attachment'
	#   # => <input id="attachment" name="attachment" type="file" />
	#
	#   file_field_tag 'avatar', :class => 'profile_input'
	#   # => <input class="profile_input" id="avatar" name="avatar" type="file" />
	#
	#   file_field_tag 'picture', :disabled => true
	#   # => <input disabled="disabled" id="picture" name="picture" type="file" />
	#
	#   file_field_tag 'resume', :value => '~/resume.doc'
	#   # => <input id="resume" name="resume" type="file" value="~/resume.doc" />
	#
	#   file_field_tag 'user_pic', :accept => 'image/png,image/gif,image/jpeg'
	#   # => <input accept="image/png,image/gif,image/jpeg" id="user_pic" name="user_pic" type="file" />
	#
	#   file_field_tag 'file', :accept => 'text/html', :class => 'upload', :value => 'index.html'
	#   # => <input accept="text/html" class="upload" id="file" name="file" type="file" value="index.html" />
	public static function file_field_tag($name, $options = array()){
		return static::text_field_tag($name, null, array_merge($options, array("type" => "file")));
	}

	# Creates a password field, a masked text field that will hide the users input behind a mask character.
	#
	# ==== Options
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * <tt>:size</tt> - The number of visible characters that will fit in the input.
	# * <tt>:maxlength</tt> - The maximum number of characters that the browser will allow the user to enter.
	# * Any other key creates standard HTML attributes for the tag.
	#
	# ==== Examples
	#   password_field_tag 'pass'
	#   # => <input id="pass" name="pass" type="password" />
	#
	#   password_field_tag 'secret', 'Your secret here'
	#   # => <input id="secret" name="secret" type="password" value="Your secret here" />
	#
	#   password_field_tag 'masked', nil, :class => 'masked_input_field'
	#   # => <input class="masked_input_field" id="masked" name="masked" type="password" />
	#
	#   password_field_tag 'token', '', :size => 15
	#   # => <input id="token" name="token" size="15" type="password" value="" />
	#
	#   password_field_tag 'key', nil, :maxlength => 16
	#   # => <input id="key" maxlength="16" name="key" type="password" />
	#
	#   password_field_tag 'confirm_pass', nil, :disabled => true
	#   # => <input disabled="disabled" id="confirm_pass" name="confirm_pass" type="password" />
	#
	#   password_field_tag 'pin', '1234', :maxlength => 4, :size => 6, :class => "pin_input"
	#   # => <input class="pin_input" id="pin" maxlength="4" name="pin" size="6" type="password" value="1234" />
	public static function password_field_tag($name = "password", $value = null, $options = array()){
		return static::text_field_tag($name, $value, array_merge($options, array("type" => "password")));
	}

	# Creates a text input area; use a textarea for longer text inputs such as blog posts or descriptions.
	#
	# ==== Options
	# * <tt>:size</tt> - A string specifying the dimensions (columns by rows) of the textarea (e.g., "25x10").
	# * <tt>:rows</tt> - Specify the number of rows in the textarea
	# * <tt>:cols</tt> - Specify the number of columns in the textarea
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * <tt>:escape</tt> - By default, the contents of the text input are HTML escaped.
	#   If you need unescaped contents, set this to false.
	# * Any other key creates standard HTML attributes for the tag.
	#
	# ==== Examples
	#   text_area_tag 'post'
	#   # => <textarea id="post" name="post"></textarea>
	#
	#   text_area_tag 'bio', @user.bio
	#   # => <textarea id="bio" name="bio">This is my biography.</textarea>
	#
	#   text_area_tag 'body', nil, :rows => 10, :cols => 25
	#   # => <textarea cols="25" id="body" name="body" rows="10"></textarea>
	#
	#   text_area_tag 'body', nil, :size => "25x10"
	#   # => <textarea name="body" id="body" cols="25" rows="10"></textarea>
	#
	#   text_area_tag 'description', "Description goes here.", :disabled => true
	#   # => <textarea disabled="disabled" id="description" name="description">Description goes here.</textarea>
	#
	#   text_area_tag 'comment', nil, :class => 'comment_input'
	#   # => <textarea class="comment_input" id="comment" name="comment"></textarea>
	public static function text_area_tag($name, $content = null, $options = array()){
		#$options = $options.stringify_keys

		if(($size = \PHPRails\delete($options, 'size'))){
			if(is_string($size)){
				list($options['cols'], $options['rows']) = explode('x', $size);
			}
		}

		$escape = \PHPRails\delete($options, 'escape') ?: true;
		if($escape){
			# ERB::Util.html_escape
			$content = htmlspecialchars($content);
		}

		return TagHelper::content_tag('textarea', $content, array_merge(array( "name" => $name, "id" => static::sanitize_to_id($name)), $options));
	}

	# Creates a check box form input tag.
	#
	# ==== Options
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * Any other key creates standard HTML options for the tag.
	#
	# ==== Examples
	#   check_box_tag 'accept'
	#   # => <input id="accept" name="accept" type="checkbox" value="1" />
	#
	#   check_box_tag 'rock', 'rock music'
	#   # => <input id="rock" name="rock" type="checkbox" value="rock music" />
	#
	#   check_box_tag 'receive_email', 'yes', true
	#   # => <input checked="checked" id="receive_email" name="receive_email" type="checkbox" value="yes" />
	#
	#   check_box_tag 'tos', 'yes', false, :class => 'accept_tos'
	#   # => <input class="accept_tos" id="tos" name="tos" type="checkbox" value="yes" />
	#
	#   check_box_tag 'eula', 'accepted', false, :disabled => true
	#   # => <input disabled="disabled" id="eula" name="eula" type="checkbox" value="accepted" />
	public static function check_box_tag($name, $value = "1", $checked = false, $options = array()){
		$html_options = array_merge(array( "type" => "checkbox", "name" => $name, "id" => static::sanitize_to_id($name), "value" => $value), $options);
		if($checked){
			$html_options["checked"] = "checked";
		}
		return TagHelper::tag('input', $html_options);
	}

	# Creates a radio button; use groups of radio buttons named the same to allow users to
	# select from a group of options.
	#
	# ==== Options
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * Any other key creates standard HTML options for the tag.
	#
	# ==== Examples
	#   radio_button_tag 'gender', 'male'
	#   # => <input id="gender_male" name="gender" type="radio" value="male" />
	#
	#   radio_button_tag 'receive_updates', 'no', true
	#   # => <input checked="checked" id="receive_updates_no" name="receive_updates" type="radio" value="no" />
	#
	#   radio_button_tag 'time_slot', "3:00 p.m.", false, :disabled => true
	#   # => <input disabled="disabled" id="time_slot_300_pm" name="time_slot" type="radio" value="3:00 p.m." />
	#
	#   radio_button_tag 'color', "green", true, :class => "color_input"
	#   # => <input checked="checked" class="color_input" id="color_green" name="color" type="radio" value="green" />
	public static function radio_button_tag($name, $value, $checked = false, $options = array()){
		$html_options = array_merge(array( "type" => "radio", "name" => $name, "id" => static::sanitize_to_id($name) . "_" . static::sanitize_to_id($value), "value" => $value), $options);
		if($checked){
			$html_options["checked"] = "checked";
		}
		return TagHelper::tag('input', $html_options);
	}

	# Creates a submit button with the text <tt>value</tt> as the caption.
	#
	# ==== Options
	# * <tt>:confirm => 'question?'</tt> - If present the unobtrusive JavaScript
	#   drivers will provide a prompt with the question specified. If the user accepts,
	#   the form is processed normally, otherwise no action is taken.
	# * <tt>:disabled</tt> - If true, the user will not be able to use this input.
	# * <tt>:disable_with</tt> - Value of this parameter will be used as the value for a
	#   disabled version of the submit button when the form is submitted. This feature is
	#   provided by the unobtrusive JavaScript driver.
	# * Any other key creates standard HTML options for the tag.
	#
	# ==== Examples
	#   submit_tag
	#   # => <input name="commit" type="submit" value="Save changes" />
	#
	#   submit_tag "Edit this article"
	#   # => <input name="commit" type="submit" value="Edit this article" />
	#
	#   submit_tag "Save edits", :disabled => true
	#   # => <input disabled="disabled" name="commit" type="submit" value="Save edits" />
	#
	#
	#   submit_tag "Complete sale", :disable_with => "Please wait..."
	#   # => <input name="commit" data-disable-with="Please wait..."
	#   #    type="submit" value="Complete sale" />
	#
	#   submit_tag nil, :class => "form_submit"
	#   # => <input class="form_submit" name="commit" type="submit" />
	#
	#   submit_tag "Edit", :disable_with => "Editing...", :class => "edit_button"
	#   # => <input class="edit_button" data-disable_with="Editing..."
	#   #    name="commit" type="submit" value="Edit" />
	#
	#   submit_tag "Save", :confirm => "Are you sure?"
	#   # => <input name='commit' type='submit' value='Save'
	#         data-confirm="Are you sure?" />
	#
	public static function submit_tag($value = "Save changes", $options = array()){
		#options = options.stringify_keys

		if(($disable_with = \PHPRails\delete($options, 'disable_with'))){
			$options["data-disable-with"] = $disable_with;
		}

		if(($confirm = \PHPRails\delete($options, 'confirm'))){
			$options["data-confirm"] = $confirm;
		}

		return TagHelper::tag('input', array_merge(array("type" => "submit", "name" => "commit", "value" => $value), $options));
	}

	# Creates a button element that defines a <tt>submit</tt> button,
	# <tt>reset</tt>button or a generic button which can be used in
	# JavaScript, for example. You can use the button tag as a regular
	# submit tag but it isn't supported in legacy browsers. However,
	# the button tag allows richer labels such as images and emphasis,
	# so this helper will also accept a block.
	#
	# ==== Options
	# * <tt>:confirm => 'question?'</tt> - If present, the
	#   unobtrusive JavaScript drivers will provide a prompt with
	#   the question specified. If the user accepts, the form is
	#   processed normally, otherwise no action is taken.
	# * <tt>:disabled</tt> - If true, the user will not be able to
	#   use this input.
	# * <tt>:disable_with</tt> - Value of this parameter will be
	#   used as the value for a disabled version of the submit
	#   button when the form is submitted. This feature is provided
	#   by the unobtrusive JavaScript driver.
	# * Any other key creates standard HTML options for the tag.
	#
	# ==== Examples
	#   button_tag
	#   # => <button name="button" type="submit">Button</button>
	#
	#   button_tag(:type => 'button') do
	#     content_tag(:strong, 'Ask me!')
	#   end
	#   # => <button name="button" type="button">
	#          <strong>Ask me!</strong>
	#        </button>
	#
	#   button_tag "Checkout", :disable_with => "Please wait..."
	#   # => <button data-disable-with="Please wait..." name="button"
	#                type="submit">Checkout</button>
	#
	public static function button_tag($content_or_options = null, $options = null){ //, &$block){
		$args = func_get_args();
		if( ($block = \PHPRails\block_given__($args)) && \PHPRails\is_hash($content_or_options) ){
			$options = $content_or_options;
		}
		$options = $options ?: array();
		#options = options.stringify_keys

		if(($disable_with = \PHPRails\delete($options, 'disable_with'))){
			$options["data-disable-with"] = $disable_with;
		}

		if(($confirm = \PHPRails\delete($options, 'confirm'))){
			$options["data-confirm"] = $confirm;
		}
		
		$options = array_merge(array('name' => 'button', 'type' => 'submit'), $options);

		return TagHelper::content_tag('button', $content_or_options ?: 'Button', $options, $block);
	}

	# Displays an image which when clicked will submit the form.
	#
	# <tt>source</tt> is passed to AssetTagHelper#path_to_image
	#
	# ==== Options
	# * <tt>:confirm => 'question?'</tt> - This will add a JavaScript confirm
	#   prompt with the question specified. If the user accepts, the form is
	#   processed normally, otherwise no action is taken.
	# * <tt>:disabled</tt> - If set to true, the user will not be able to use this input.
	# * Any other key creates standard HTML options for the tag.
	#
	# ==== Examples
	#   image_submit_tag("login.png")
	#   # => <input src="/images/login.png" type="image" />
	#
	#   image_submit_tag("purchase.png", :disabled => true)
	#   # => <input disabled="disabled" src="/images/purchase.png" type="image" />
	#
	#   image_submit_tag("search.png", :class => 'search_button')
	#   # => <input class="search_button" src="/images/search.png" type="image" />
	#
	#   image_submit_tag("agree.png", :disabled => true, :class => "agree_disagree_button")
	#   # => <input class="agree_disagree_button" disabled="disabled" src="/images/agree.png" type="image" />
	public static function image_submit_tag($source, $options = array()){
		#options = options.stringify_keys
		
		if(($confirm = \PHPRails\delete($options, 'confirm'))){
			$options["data-confirm"] = $confirm;
		}
		
		return TagHelper::tag('input', array_merge(array("type" => "image", "src" => AssetTagHelper::path_to_image($source)), $options));
	}

	# Creates a field set for grouping HTML form elements.
	#
	# <tt>legend</tt> will become the fieldset's title (optional as per W3C).
	# <tt>options</tt> accept the same values as tag.
	#
	# ==== Examples
	#   <%= field_set_tag do %>
	#     <p><%= text_field_tag 'name' %></p>
	#   <% end %>
	#   # => <fieldset><p><input id="name" name="name" type="text" /></p></fieldset>
	#
	#   <%= field_set_tag 'Your details' do %>
	#     <p><%= text_field_tag 'name' %></p>
	#   <% end %>
	#   # => <fieldset><legend>Your details</legend><p><input id="name" name="name" type="text" /></p></fieldset>
	#
	#   <%= field_set_tag nil, :class => 'format' do %>
	#     <p><%= text_field_tag 'name' %></p>
	#   <% end %>
	#   # => <fieldset class="format"><p><input id="name" name="name" type="text" /></p></fieldset>
	public static function field_set_tag($legend = null, $options = null/*, &$block */){
		$args = func_get_args();
		$output = TagHelper::tag('fieldset', $options, true);
		if(!is_null($legend)){
			$output .= TagHelper::content_tag('legend', $legend);
		}
		if(($block = \PHPRails\block_given__($args))){
			$output .= \PHPRails\capture($block);
		}
		$output .= "</fieldset>";
		return $output;
	}

	# Creates a text field of type "search".
	#
	# ==== Options
	# * Accepts the same options as text_field_tag.
	public static function search_field_tag($name, $value = null, $options = array()){
		return static::text_field_tag($name, $value, array_merge($options, array("type" => "search")));
	}

	# Creates a text field of type "tel".
	#
	# ==== Options
	# * Accepts the same options as text_field_tag.
	public static function telephone_field_tag($name, $value = null, $options = array()){
		return static::text_field_tag($name, $value, array_merge($options, array("type" => "tel")));
	}
	public static function phone_field_tag($name, $value = null, $options = array()){
		return static::telephone_field_tag($name, $value, $options);
	}

	# Creates a text field of type "url".
	#
	# ==== Options
	# * Accepts the same options as text_field_tag.
	public static function url_field_tag($name, $value = null, $options = array()){
		return static::text_field_tag($name, $value, array_merge($options, array("type" => "url")));
	}

	# Creates a text field of type "email".
	#
	# ==== Options
	# * Accepts the same options as text_field_tag.
	public static function email_field_tag($name, $value = null, $options = array()){
		return static::text_field_tag($name, $value, array_merge($options, array("type" => "email")));
	}

	# Creates a number field.
	#
	# ==== Options
	# * <tt>:min</tt> - The minimum acceptable value.
	# * <tt>:max</tt> - The maximum acceptable value.
	# * <tt>:in</tt> - A range specifying the <tt>:min</tt> and
	#   <tt>:max</tt> values.
	# * <tt>:step</tt> - The acceptable value granularity.
	# * Otherwise accepts the same options as text_field_tag.
	#
	# ==== Examples
	#   number_field_tag 'quantity', nil, :in => 1...10
	#   => <input id="quantity" name="quantity" min="1" max="9" />
	public static function number_field_tag($name, $value = null, $options = array()){
		#options = options.stringify_keys
		$options["type"] = $options["type"] ?: "number";
		if(($range = $options['in']) || ($range = $options['within'])){
			unset($options['in'], $options['within']);
			$options = array_merge($options, array("min" => min($range), "max" => max($range)));
		}
		return static::text_field_tag($name, $value, $options);
	}

	# Creates a range form element.
	#
	# ==== Options
	# * Accepts the same options as number_field_tag.
	public static function range_field_tag($name, $value = null, $options = array()){
		return static::number_field_tag($name, $value, array_merge($options, array("type" => "range")));
	}

	# Creates the hidden UTF8 enforcer tag. Override this method in a helper
	# to customize the tag.
	public static function utf8_enforcer_tag(){
		return TagHelper::tag('input', array('type' => "hidden", 'name' => "utf8", 'value' => \PHPRails\html_safe("&#x2713;")));
	}

	# private

	private static function html_options_for_form($url_for_options, $options){
		$_tmp = function($html_options) use ($url_for_options){
			if( \PHPRails\delete($html_options, "multipart") ){
				$html_options["enctype"] = "multipart/form-data";
			}
			# The following URL is unescaped, this is just a hash of options, and it is the
			# responsibility of the caller to escape all the values.
			$html_options["action"] = \UrlHelper::url_for($url_for_options);
			$html_options["accept-charset"] = "UTF-8";

			if( \PHPRails\delete($html_options, "remote") ){
				$html_options["data-remote"] = true;
			}

			if( $html_options["data-remote"]
				&& !self::$embed_authenticity_token_in_remote_forms
				&& empty($html_options["authenticity_token"]) ){
				# The authenticity token is taken from the meta tag in this case
				$html_options["authenticity_token"] = false;
			}elseif( $html_options["authenticity_token"] == true ){
				# Include the default authenticity_token, which is only generated when its set to nil,
				# but we needed the true value to override the default of no authenticity_token on data-remote.
				$html_options["authenticity_token"] = null;
			}
			return $html_options;
			
		};
		
		return call_user_func($_tmp, $options);
	}

	private static function extra_tags_for_form(&$html_options){
		$authenticity_token = \PHPRails\delete($html_options, 'authenticity_token');
		$method = \PHPRails\delete($html_options, 'method');
		switch(1){
			case preg_match('/^get$/i', $method): # must be case-insensitive, but can't use downcase as might be nil
				$html_options["method"] = 'get';
				$method_tag = '';
				break;
			case preg_match('/^post$/i', $method) || $method === "" || is_null($method):
				$html_options["method"] = 'post';
				$method_tag = static::token_tag($authenticity_token);
				break;
			default:
				$html_options["method"] = 'post';
				$method_tag = TagHelper::tag('input', array('type' => 'hidden', 'name' => '_method', 'value' => $method)) . static::token_tag($authenticity_token);
		}
		$tags = \PHPRails\html_safe(static::utf8_enforcer_tag() . $method_tag);
		return TagHelper::content_tag('div', $tags, array('style' => 'margin:0;padding:0;display:inline'));
	}

	private static function form_tag_html($html_options){
		$extra_tags = static::extra_tags_for_form($html_options);
		return TagHelper::tag('form', $html_options, true) . "\n" . $extra_tags;
	}

	private static function form_tag_in_block($html_options, &$block = null){
		$content = \PHPRails\capture($block);
		$output = new ActiveSupport\SafeBuffer();
		$output->append( static::form_tag_html($html_options) );
		$output->append( $content );
		return $output->append( "</form>" );
	}

	private static function token_tag($token){
		if($token === false || !CsrfHelper::protect_against_forgery__()){
			return '';
		}else{
			$token = $token ?: CsrfHelper::form_authenticity_token();
			return TagHelper::tag('input', array('type' => "hidden", 'name' => CsrfHelper::form_authenticity_param(), 'value' => $token));
		}
	}

	# see http://www.w3.org/TR/html4/types.html#type-name
	private static function sanitize_to_id($name){
		return preg_replace('/[^-a-zA-Z0-9:.]/', "_", str_replace(']','', $name));
	}
}
