<?php

# = Action View URL Helpers
namespace ActionView\Helpers;

\PHPRails::import('action_view/helpers/javascript_helper');
#\PHPRails::import('active_support/core_ext/array/access');
#\PHPRails::import('active_support/core_ext/hash/keys');
#\PHPRails::import('active_support/core_ext/string/output_safety');
#\PHPRails::import('action_dispatch');

\PHPRails::import('ruby/RFunction');

# Provides a set of methods for making links and getting URLs that
# depend on the routing subsystem (see ActionDispatch::Routing).
# This allows you to use the same format for links in views
# and controllers.
class UrlHelper{
	# This helper may be included in any class that includes the
	# URL helpers of a routes (routes.url_helpers). Some methods
	# provided here will only work in the context of a request
	# (link_to_unless_current, for instance), which must be provided
	# as a method called #request on the context.

	#extend ActiveSupport::Concern

	#  include ActionDispatch::Routing::UrlFor
	#  include TagHelper

	# We need to override url_options, _routes_context
	# and optimize_routes_generation? to consider the controller.

	// def url_options #:nodoc:
	//   return super unless controller.respond_to?(:url_options)
	//   controller.url_options
	// end
    // 
	// def _routes_context #:nodoc:
	//   controller
	// end
	// protected :_routes_context
    // 
	// def optimize_routes_generation? #:nodoc:
	//   controller.respond_to?(:optimize_routes_generation?) ?
	//     controller.optimize_routes_generation? : super
	// end
	// protected :optimize_routes_generation?

	# Returns the URL for the set of +options+ provided. This takes the
	# same options as +url_for+ in Action Controller (see the
	# documentation for <tt>ActionController::Base#url_for</tt>). Note that by default
	# <tt>:only_path</tt> is <tt>true</tt> so you'll get the relative "/controller/action"
	# instead of the fully qualified URL like "http://example.com/controller/action".
	#
	# ==== Options
	# * <tt>:anchor</tt> - Specifies the anchor name to be appended to the path.
	# * <tt>:only_path</tt> - If true, returns the relative URL (omitting the protocol, host name, and port) (<tt>true</tt> by default unless <tt>:host</tt> is specified).
	# * <tt>:trailing_slash</tt> - If true, adds a trailing slash, as in "/archive/2005/". Note that this
	#   is currently not recommended since it breaks caching.
	# * <tt>:host</tt> - Overrides the default (current) host if provided.
	# * <tt>:protocol</tt> - Overrides the default (current) protocol if provided.
	# * <tt>:user</tt> - Inline HTTP authentication (only plucked out if <tt>:password</tt> is also present).
	# * <tt>:password</tt> - Inline HTTP authentication (only plucked out if <tt>:user</tt> is also present).
	#
	# ==== Relying on named routes
	#
	# Passing a record (like an Active Record) instead of a Hash as the options parameter will
	# trigger the named route for that record. The lookup will happen on the name of the class. So passing a
	# Workshop object will attempt to use the +workshop_path+ route. If you have a nested route, such as
	# +admin_workshop_path+ you'll have to call that explicitly (it's impossible for +url_for+ to guess that route).
	#
	# ==== Examples
	#   <%= url_for(:action => 'index') %>
	#   # => /blog/
	#
	#   <%= url_for(:action => 'find', :controller => 'books') %>
	#   # => /books/find
	#
	#   <%= url_for(:action => 'login', :controller => 'members', :only_path => false, :protocol => 'https') %>
	#   # => https://www.example.com/members/login/
	#
	#   <%= url_for(:action => 'play', :anchor => 'player') %>
	#   # => /messages/play/#player
	#
	#   <%= url_for(:action => 'jump', :anchor => 'tax&ship') %>
	#   # => /testing/jump/#tax&ship
	#
	#   <%= url_for(Workshop.new) %>
	#   # relies on Workshop answering a persisted? call (and in this case returning false)
	#   # => /workshops
	#
	#   <%= url_for(@workshop) %>
	#   # calls @workshop.to_param which by default returns the id
	#   # => /workshops/5
	#
	#   # to_param can be re-defined in a model to provide different URL names:
	#   # => /workshops/1-workshop-name
	#
	#   <%= url_for("http://www.example.com") %>
	#   # => http://www.example.com
	#
	#   <%= url_for(:back) %>
	#   # if request.env["HTTP_REFERER"] is set to "http://www.example.com"
	#   # => http://www.example.com
	#
	#   <%= url_for(:back) %>
	#   # if request.env["HTTP_REFERER"] is not set or is blank
	#   # => javascript:history.back()
	public static function url_for($options = null){
		if(is_string($options)){
			if($options == 'back'){
				return $controller->request->referer() ?: 'javascript:history.back()';
			}
			return $options;
		}else if(is_null($options) || \PHPRails\is_hash($options)){ 
			$options = $options ?: array();
			$options = array_merge(array('only_path' => is_null($options['host'])), $options);
			return static::url_for($options);
		}else{
			return polymorphic_path($options);
		}
	}

	# Creates a link tag of the given +name+ using a URL created by the set of +options+.
	# See the valid options in the documentation for +url_for+. It's also possible to
	# pass a String instead of an options hash, which generates a link tag that uses the
	# value of the String as the href for the link. Using a <tt>:back</tt> Symbol instead
	# of an options hash will generate a link to the referrer (a JavaScript back link
	# will be used in place of a referrer if none exists). If +nil+ is passed as the name
	# the value of the link itself will become the name.
	#
	# ==== Signatures
	#
	#   link_to(body, url, html_options = {})
	#     # url is a String; you can use URL helpers like
	#     # posts_path
	#
	#   link_to(body, url_options = {}, html_options = {})
	#     # url_options, except :confirm or :method,
	#     # is passed to url_for
	#
	#   link_to(options = {}, html_options = {}) do
	#     # name
	#   end
	#
	#   link_to(url, html_options = {}) do
	#     # name
	#   end
	#
	# ==== Options
	# * <tt>:confirm => 'question?'</tt> - This will allow the unobtrusive JavaScript
	#   driver to prompt with the question specified. If the user accepts, the link is
	#   processed normally, otherwise no action is taken.
	# * <tt>:method => symbol of HTTP verb</tt> - This modifier will dynamically
	#   create an HTML form and immediately submit the form for processing using
	#   the HTTP verb specified. Useful for having links perform a POST operation
	#   in dangerous actions like deleting a record (which search bots can follow
	#   while spidering your site). Supported verbs are <tt>:post</tt>, <tt>:delete</tt>, <tt>:patch</tt>, and <tt>:put</tt>.
	#   Note that if the user has JavaScript disabled, the request will fall back
	#   to using GET. If <tt>:href => '#'</tt> is used and the user has JavaScript
	#   disabled clicking the link will have no effect. If you are relying on the
	#   POST behavior, you should check for it in your controller's action by using
	#   the request object's methods for <tt>post?</tt>, <tt>delete?</tt>, <tt>:patch</tt>, or <tt>put?</tt>.
	# * <tt>:remote => true</tt> - This will allow the unobtrusive JavaScript
	#   driver to make an Ajax request to the URL in question instead of following
	#   the link. The drivers each provide mechanisms for listening for the
	#   completion of the Ajax request and performing JavaScript operations once
	#   they're complete
	#
	# ==== Examples
	# Because it relies on +url_for+, +link_to+ supports both older-style controller/action/id arguments
	# and newer RESTful routes. Current Rails style favors RESTful routes whenever possible, so base
	# your application on resources and use
	#
	#   link_to "Profile", profile_path(@profile)
	#   # => <a href="/profiles/1">Profile</a>
	#
	# or the even pithier
	#
	#   link_to "Profile", @profile
	#   # => <a href="/profiles/1">Profile</a>
	#
	# in place of the older more verbose, non-resource-oriented
	#
	#   link_to "Profile", :controller => "profiles", :action => "show", :id => @profile
	#   # => <a href="/profiles/show/1">Profile</a>
	#
	# Similarly,
	#
	#   link_to "Profiles", profiles_path
	#   # => <a href="/profiles">Profiles</a>
	#
	# is better than
	#
	#   link_to "Profiles", :controller => "profiles"
	#   # => <a href="/profiles">Profiles</a>
	#
	# You can use a block as well if your link target is hard to fit into the name parameter. ERB example:
	#
	#   <%= link_to(@profile) do %>
	#     <strong><%= @profile.name %></strong> -- <span>Check it out!</span>
	#   <% end %>
	#   # => <a href="/profiles/1">
	#          <strong>David</strong> -- <span>Check it out!</span>
	#        </a>
	#
	# Classes and ids for CSS are easy to produce:
	#
	#   link_to "Articles", articles_path, :id => "news", :class => "article"
	#   # => <a href="/articles" class="article" id="news">Articles</a>
	#
	# Be careful when using the older argument style, as an extra literal hash is needed:
	#
	#   link_to "Articles", { :controller => "articles" }, :id => "news", :class => "article"
	#   # => <a href="/articles" class="article" id="news">Articles</a>
	#
	# Leaving the hash off gives the wrong link:
	#
	#   link_to "WRONG!", :controller => "articles", :id => "news", :class => "article"
	#   # => <a href="/articles/index/news?class=article">WRONG!</a>
	#
	# +link_to+ can also produce links with anchors or query strings:
	#
	#   link_to "Comment wall", profile_path(@profile, :anchor => "wall")
	#   # => <a href="/profiles/1#wall">Comment wall</a>
	#
	#   link_to "Ruby on Rails search", :controller => "searches", :query => "ruby on rails"
	#   # => <a href="/searches?query=ruby+on+rails">Ruby on Rails search</a>
	#
	#   link_to "Nonsense search", searches_path(:foo => "bar", :baz => "quux")
	#   # => <a href="/searches?foo=bar&amp;baz=quux">Nonsense search</a>
	#
	# The two options specific to +link_to+ (<tt>:confirm</tt> and <tt>:method</tt>) are used as follows:
	#
	#   link_to "Visit Other Site", "http://www.rubyonrails.org/", :confirm => "Are you sure?"
	#   # => <a href="http://www.rubyonrails.org/" data-confirm="Are you sure?"">Visit Other Site</a>
	#
	#   link_to("Destroy", "http://www.example.com", :method => :delete, :confirm => "Are you sure?")
	#   # => <a href='http://www.example.com' rel="nofollow" data-method="delete" data-confirm="Are you sure?">Destroy</a>
	public static function link_to(/* *$args , &block */){
		$args = func_get_args();
		if($block = \PHPRails\block_given__($args)){
			$options      = reset($args) ?: array();
			$html_options = next($args);
			return static::link_to(call_user_func($block), $options, $html_options);
		}else{
			$name         = $args[0];
			$options      = $args[1] ?: array();
			$html_options = $args[2] ?: array();
			
			$html_options = static::convert_options_to_data_attributes($options, $html_options);
			$url = static::url_for($options);

			$href = $html_options['href'];
			$tag_options = TagHelper::tag_options($html_options);
			$url = htmlspecialchars($url);
			$href_attr = $href ? "" : "href=\"{$url}\"";
			$name_or_url = $name ? htmlspecialchars($name) : $url;
			return \PHPRails\html_safe("<a {$href_attr} {$tag_options}>{$name_or_url}</a>");
		}
	}

	# Generates a form containing a single button that submits to the URL created
	# by the set of +options+. This is the safest method to ensure links that
	# cause changes to your data are not triggered by search bots or accelerators.
	# If the HTML button does not work with your layout, you can also consider
	# using the +link_to+ method with the <tt>:method</tt> modifier as described in
	# the +link_to+ documentation.
	#
	# By default, the generated form element has a class name of <tt>button_to</tt>
	# to allow styling of the form itself and its children. This can be changed
	# using the <tt>:form_class</tt> modifier within +html_options+. You can control
	# the form submission and input element behavior using +html_options+.
	# This method accepts the <tt>:method</tt> and <tt>:confirm</tt> modifiers
	# described in the +link_to+ documentation. If no <tt>:method</tt> modifier
	# is given, it will default to performing a POST operation. You can also
	# disable the button by passing <tt>:disabled => true</tt> in +html_options+.
	# If you are using RESTful routes, you can pass the <tt>:method</tt>
	# to change the HTTP verb used to submit the form.
	#
	# ==== Options
	# The +options+ hash accepts the same options as +url_for+.
	#
	# There are a few special +html_options+:
	# * <tt>:method</tt> - Symbol of HTTP verb. Supported verbs are <tt>:post</tt>, <tt>:get</tt>,
	#   <tt>:delete</tt>, <tt>:patch</tt>, and <tt>:put</tt>. By default it will be <tt>:post</tt>.
	# * <tt>:disabled</tt> - If set to true, it will generate a disabled button.
	# * <tt>:confirm</tt> - This will use the unobtrusive JavaScript driver to
	#   prompt with the question specified. If the user accepts, the link is
	#   processed normally, otherwise no action is taken.
	# * <tt>:remote</tt> -  If set to true, will allow the Unobtrusive JavaScript drivers to control the
	#   submit behavior. By default this behavior is an ajax submit.
	# * <tt>:form</tt> - This hash will be form attributes
	# * <tt>:form_class</tt> - This controls the class of the form within which the submit button will
	#   be placed
	#
	# ==== Examples
	#   <%= button_to "New", :action => "new" %>
	#   # => "<form method="post" action="/controller/new" class="button_to">
	#   #      <div><input value="New" type="submit" /></div>
	#   #    </form>"
	#
	#
	#   <%= button_to "New", :action => "new", :form_class => "new-thing" %>
	#   # => "<form method="post" action="/controller/new" class="new-thing">
	#   #      <div><input value="New" type="submit" /></div>
	#   #    </form>"
	#
	#
	#   <%= button_to "Create", :action => "create", :remote => true, :form => { "data-type" => "json" } %>
	#   # => "<form method="post" action="/images/create" class="button_to" data-remote="true" data-type="json">
	#   #      <div>
	#   #        <input value="Create" type="submit" />
	#   #        <input name="authenticity_token" type="hidden" value="10f2163b45388899ad4d5ae948988266befcb6c3d1b2451cf657a0c293d605a6"/>
	#   #      </div>
	#   #    </form>"
	#
	#
	#   <%= button_to "Delete Image", { :action => "delete", :id => @image.id },
	#             :confirm => "Are you sure?", :method => :delete %>
	#   # => "<form method="post" action="/images/delete/1" class="button_to">
	#   #      <div>
	#   #        <input type="hidden" name="_method" value="delete" />
	#   #        <input data-confirm='Are you sure?' value="Delete Image" type="submit" />
	#   #        <input name="authenticity_token" type="hidden" value="10f2163b45388899ad4d5ae948988266befcb6c3d1b2451cf657a0c293d605a6"/>
	#   #      </div>
	#   #    </form>"
	#
	#
	#   <%= button_to('Destroy', 'http://www.example.com', :confirm => 'Are you sure?',
	#             :method => "delete", :remote => true, :disable_with => 'loading...') %>
	#   # => "<form class='button_to' method='post' action='http://www.example.com' data-remote='true'>
	#   #       <div>
	#   #         <input name='_method' value='delete' type='hidden' />
	#   #         <input value='Destroy' type='submit' disable_with='loading...' data-confirm='Are you sure?' />
	#   #         <input name="authenticity_token" type="hidden" value="10f2163b45388899ad4d5ae948988266befcb6c3d1b2451cf657a0c293d605a6"/>
	#   #       </div>
	#   #     </form>"
	#   #
	public static function button_to($name, $options = array(), $html_options = array()){
		#$html_options = html_options.stringify_keys
		static::convert_boolean_attributes_($html_options, array('disabled'));

		$url    = is_string($options) ? $options : static::url_for($options);
		$remote = \PHPRails\delete($html_options, 'remote');

		$method     = (string)\PHPRails\delete($html_options, 'method');
		$method_tag = in_array($method, array('patch', 'put', 'delete')) ? static::method_tag($method) : \PHPRails\html_safe('');

		$form_method  = $method == 'get' ? 'get' : 'post';
		$form_options = \PHPRails\delete($html_options, 'form') ?: array();
		$form_options['class'] = $form_options['class'] ?: \PHPRails\delete($html_options, 'form_class') ?: 'button_to';
		$form_options = array_merge($options, array('method' => $form_method, 'action' => $url));
		if( $remote ){
			$form_options = array_merge($form_options, array("data-remote" => "true"));
		}

		$request_token_tag = $form_method == 'post' ? static::token_tag() : '';

		$html_options = static::convert_options_to_data_attributes($options, $html_options);
		$html_options = array_merge($html_options, array("type" => "submit", "value" => $name ?: $url));

		$inner_tags = $method_tag->safe_concat( TagHelper::tag('input', $html_options)->safe_concat( $request_token_tag ) );
		return TagHelper::content_tag('form', TagHelper::content_tag('div', $inner_tags), $form_options);
	}


	# Creates a link tag of the given +name+ using a URL created by the set of
	# +options+ unless the current request URI is the same as the links, in
	# which case only the name is returned (or the given block is yielded, if
	# one exists). You can give +link_to_unless_current+ a block which will
	# specialize the default behavior (e.g., show a "Start Here" link rather
	# than the link's text).
	#
	# ==== Examples
	# Let's say you have a navigation menu...
	#
	#   <ul id="navbar">
	#     <li><%= link_to_unless_current("Home", { :action => "index" }) %></li>
	#     <li><%= link_to_unless_current("About Us", { :action => "about" }) %></li>
	#   </ul>
	#
	# If in the "about" action, it will render...
	#
	#   <ul id="navbar">
	#     <li><a href="/controller/index">Home</a></li>
	#     <li>About Us</li>
	#   </ul>
	#
	# ...but if in the "index" action, it will render:
	#
	#   <ul id="navbar">
	#     <li>Home</li>
	#     <li><a href="/controller/about">About Us</a></li>
	#   </ul>
	#
	# The implicit block given to +link_to_unless_current+ is evaluated if the current
	# action is the action given. So, if we had a comments page and wanted to render a
	# "Go Back" link instead of a link to the comments page, we could do something like this...
	#
	#    <%=
	#        link_to_unless_current("Comment", { :controller => "comments", :action => "new" }) do
	#           link_to("Go back", { :controller => "posts", :action => "index" })
	#        end
	#     %>
	public static function link_to_unless_current($name, $options = array(), $html_options = array()/*, &$block */){
		$args = func_get_args();
		$block = \PHPRails\block_given__($args);
		return static::link_to_unless( current_page__($options), $name, $options, $html_options, $block );
	}

	# Creates a link tag of the given +name+ using a URL created by the set of
	# +options+ unless +condition+ is true, in which case only the name is
	# returned. To specialize the default behavior (i.e., show a login link rather
	# than just the plaintext link text), you can pass a block that
	# accepts the name or the full argument list for +link_to_unless+.
	#
	# ==== Examples
	#   <%= link_to_unless(@current_user.nil?, "Reply", { :action => "reply" }) %>
	#   # If the user is logged in...
	#   # => <a href="/controller/reply/">Reply</a>
	#
	#   <%=
	#      link_to_unless(@current_user.nil?, "Reply", { :action => "reply" }) do |name|
	#        link_to(name, { :controller => "accounts", :action => "signup" })
	#      end
	#   %>
	#   # If the user is logged in...
	#   # => <a href="/controller/reply/">Reply</a>
	#   # If not...
	#   # => <a href="/accounts/signup">Reply</a>
	public static function link_to_unless($condition, $name, $options = array(), $html_options = array()/*, &$block */){
		/*
			FIXME Capture method
		*/
		$args = func_get_args();
		if( $condition ){
			if($block = \PHPRails\block_given__($args)){
				$function = new \RFunction($block);
				return $block->arity <= 1 ? capture($name, $block) : capture($name, $options, $html_options, $block);
			}else{
				return $name;
			}
		}else{
			return static::link_to($name, $options, $html_options);
		}
	}

	# Creates a link tag of the given +name+ using a URL created by the set of
	# +options+ if +condition+ is true, otherwise only the name is
	# returned. To specialize the default behavior, you can pass a block that
	# accepts the name or the full argument list for +link_to_unless+ (see the examples
	# in +link_to_unless+).
	#
	# ==== Examples
	#   <%= link_to_if(@current_user.nil?, "Login", { :controller => "sessions", :action => "new" }) %>
	#   # If the user isn't logged in...
	#   # => <a href="/sessions/new/">Login</a>
	#
	#   <%=
	#      link_to_if(@current_user.nil?, "Login", { :controller => "sessions", :action => "new" }) do
	#        link_to(@current_user.login, { :controller => "accounts", :action => "show", :id => @current_user })
	#      end
	#   %>
	#   # If the user isn't logged in...
	#   # => <a href="/sessions/new/">Login</a>
	#   # If they are logged in...
	#   # => <a href="/accounts/show/3">my_username</a>
	public static function link_to_if($condition, $name, $options = array(), $html_options = array()/*, &block */){
		$args = func_get_args();
		$block = \PHPRails\block_given__($args);
		return static::link_to_unless( !$condition, $name, $options, $html_options, $block );
	}

	# Creates a mailto link tag to the specified +email_address+, which is
	# also used as the name of the link unless +name+ is specified. Additional
	# HTML attributes for the link can be passed in +html_options+.
	#
	# +mail_to+ has several methods for hindering email harvesters and customizing
	# the email itself by passing special keys to +html_options+.
	#
	# ==== Options
	# * <tt>:encode</tt> - This key will accept the strings "javascript" or "hex".
	#   Passing "javascript" will dynamically create and encode the mailto link then
	#   eval it into the DOM of the page. This method will not show the link on
	#   the page if the user has JavaScript disabled. Passing "hex" will hex
	#   encode the +email_address+ before outputting the mailto link.
	# * <tt>:replace_at</tt> - When the link +name+ isn't provided, the
	#   +email_address+ is used for the link label. You can use this option to
	#   obfuscate the +email_address+ by substituting the @ sign with the string
	#   given as the value.
	# * <tt>:replace_dot</tt> - When the link +name+ isn't provided, the
	#   +email_address+ is used for the link label. You can use this option to
	#   obfuscate the +email_address+ by substituting the . in the email with the
	#   string given as the value.
	# * <tt>:subject</tt> - Preset the subject line of the email.
	# * <tt>:body</tt> - Preset the body of the email.
	# * <tt>:cc</tt> - Carbon Copy additional recipients on the email.
	# * <tt>:bcc</tt> - Blind Carbon Copy additional recipients on the email.
	#
	# ==== Examples
	#   mail_to "me@domain.com"
	#   # => <a href="mailto:me@domain.com">me@domain.com</a>
	#
	#   mail_to "me@domain.com", "My email", :encode => "javascript"
	#   # => <script>eval(decodeURIComponent('%64%6f%63...%27%29%3b'))</script>
	#
	#   mail_to "me@domain.com", "My email", :encode => "hex"
	#   # => <a href="mailto:%6d%65@%64%6f%6d%61%69%6e.%63%6f%6d">My email</a>
	#
	#   mail_to "me@domain.com", nil, :replace_at => "_at_", :replace_dot => "_dot_", :class => "email"
	#   # => <a href="mailto:me@domain.com" class="email">me_at_domain_dot_com</a>
	#
	#   mail_to "me@domain.com", "My email", :cc => "ccaddress@domain.com",
	#            :subject => "This is an example email"
	#   # => <a href="mailto:me@domain.com?cc=ccaddress@domain.com&subject=This%20is%20an%20example%20email">My email</a>
	public static function mail_to($email_address, $name = null, $html_options = array()){
		$email_address = htmlspecialchars($email_address);

		#$html_options = html_options.stringify_keys
		$encode = (string)\PHPRails\delete($html_options, "encode");

		$extras = array_map(function($item) use (&$html_options){
			$option = \PHPRails\delete($html_options, $item);
			if(!$option)return;
			#option = Rack::Utils.escape_path(option)
			$option = strtr(preg_replace('/([^a-zA-Z0-9_.-]+)/e', "'%' . strtoupper(implode(unpack(str_repeat('H2', strlen('\\1')), '\\1'), '%'))", $option), ' ', '+');
			return "{$item}={$option}";
		}, array( 'cc', 'bcc', 'body', 'subject' ));
		$extras = empty($extras) ? '' : '?' . htmlspecialchars(implode('&', $extras));

		$email_address_obfuscated = (string)$email_address; #.to_str
		if(array_key_exists("replace_at", $html_options)){
			$email_address_obfuscated = preg_replace('/@/', \PHPRails\delete($html_options, "replace_at"), $email_address_obfuscated);
		}
		if(array_key_exists("replace_dot", $html_options)){
			$email_address_obfuscated = preg_replace('/\./', \PHPRails\delete($html_options, "replace_dot"), $email_address_obfuscated);
		}
		switch($encode){
			case "javascript":
				$string  = '';
				$html    = TagHelper::content_tag("a", $name ?: \PHPRails\html_safe($email_address_obfuscated), array_merge($html_options, array("href" => \PHPRails\html_safe("mailto:{$email_address}{$extras}"))));
				$html    = JavaScriptHelper::escape_javascript((string)$html);
				$_string = "document.write('{$html}');";
				for ($i = 0, $j = strlen($_string); $i < $j; $i++) {
					$string .= sprintf("%%%x", ord($_string[$i]));
				}
				return \PHPRails\html_safe("<script>eval(decodeURIComponent('{$string}'))</script>");
			case "hex";
				$email_address_encoded = join(array_map(function($c){
					return sprintf("&#%d;", $c);
				}, unpack('C*', $email_address_obfuscated)));
			
				$string = join(array_map(function($c){
					return sprintf("&#%d;", $c);
				}, unpack('C*', 'mailto:'))) . join(array_map(function($c){
					$char = chr($c);
					return preg_match('/\w/', $char) ? sprintf("%%%x", $c) : $char;
				}, unpack('C*', $email_address)));
				return TagHelper::content_tag("a", $name ?: \PHPRails\html_safe($email_address_encoded), array_merge($html_options, array("href" => \PHPRails\html_safe("{$string}{$extras}"))));
			default:
				return TagHelper::content_tag("a", $name ?: \PHPRails\html_safe($email_address_obfuscated), array_merge($html_options, array("href" => \PHPRails\html_safe("mailto:#{email_address}#{extras}"))));
		}
	}

	# True if the current request URI was generated by the given +options+.
	#
	# ==== Examples
	# Let's say we're in the <tt>/shop/checkout?order=desc</tt> action.
	#
	#   current_page?(:action => 'process')
	#   # => false
	#
	#   current_page?(:controller => 'shop', :action => 'checkout')
	#   # => true
	#
	#   current_page?(:controller => 'shop', :action => 'checkout', :order => 'asc')
	#   # => false
	#
	#   current_page?(:action => 'checkout')
	#   # => true
	#
	#   current_page?(:controller => 'library', :action => 'checkout')
	#   # => false
	#
	# Let's say we're in the <tt>/shop/checkout?order=desc&page=1</tt> action.
	#
	#   current_page?(:action => 'process')
	#   # => false
	#
	#   current_page?(:controller => 'shop', :action => 'checkout')
	#   # => true
	#
	#   current_page?(:controller => 'shop', :action => 'checkout', :order => 'desc', :page => '1')
	#   # => true
	#
	#   current_page?(:controller => 'shop', :action => 'checkout', :order => 'desc', :page => '2')
	#   # => false
	#
	#   current_page?(:controller => 'shop', :action => 'checkout', :order => 'desc')
	#   # => false
	#
	#   current_page?(:action => 'checkout')
	#   # => true
	#
	#   current_page?(:controller => 'library', :action => 'checkout')
	#   # => false
	#
	# Let's say we're in the <tt>/products</tt> action with method POST in case of invalid product.
	#
	#   current_page?(:controller => 'product', :action => 'index')
	#   # => false
	#
	public static function current_page__($options){
		/*
			FIXME Have request object here
		*/
		if(!$request){
			throw new Exception("You cannot use helpers that need to determine the current ".
				"page unless your view context provides a Request object ".
				"in a #request method");
		}

		if(!$request->is('get')){
			return false;
		}

		$url_string = static::url_for($options);

		# We ignore any extra parameters in the request_uri if the
		# submitted url doesn't have any either. This lets the function
		# work with things like ?order=asc
		$request_uri = strpos($url_string, '?') > -1 ? $request->fullpath : $request->path;

		if( preg_match('/^\w+:\/\//', $url_string) ){
			return $url_string == "{$request->protocol}#{$request->host_with_port}{$request_uri}";
		}else{
			return $url_string == $request_uri;
		}
	}

	
	private static function convert_options_to_data_attributes($options, $html_options){
		if( $html_options ){
			# html_options = html_options.stringify_keys
			if( static::link_to_remote_options__($options) || static::link_to_remote_options__($html_options) ){
				$html_options['data-remote'] = 'true';
			}

			$disable_with = \PHPRails\delete($html_options, 'disable_with');
			$confirm = \PHPRails\delete($html_options, 'confirm');
			$method  = \PHPRails\delete($html_options, 'method');

			if($disable_with){
				$html_options["data-disable-with"] = $disable_with;
			}
			if($confirm){
				$html_options["data-confirm"] = $confirm;
			}
			if( $method ){
				static::add_method_to_attributes_($html_options, $method);
			}

			return $html_options;
		}else{
			return static::link_to_remote_options__($options) ? array('data-remote' => 'true') : array();
		}
	}

	private static function link_to_remote_options__(&$options){
		return \PHPRails\is_hash($options) && \PHPRails\delete($options, 'remote');
	}

	private static function add_method_to_attributes_(&$html_options, $method){
		if( $method && strtolower($method) != "get" && !preg_match('/nofollow/', $html_options["rel"])){
			$html_options["rel"] = ltrim("{$html_options["rel"]} nofollow");
		}
		$html_options["data-method"] = $method;
	}

	# Processes the +html_options+ hash, converting the boolean
	# attributes from true/false form into the form required by
	# HTML/XHTML. (An attribute is considered to be boolean if
	# its name is listed in the given +bool_attrs+ array.)
	#
	# More specifically, for each boolean attribute in +html_options+
	# given as:
	#
	#   "attr" => bool_value
	#
	# if the associated +bool_value+ evaluates to true, it is
	# replaced with the attribute's name; otherwise the attribute is
	# removed from the +html_options+ hash. (See the XHTML 1.0 spec,
	# section 4.5 "Attribute Minimization" for more:
	# http://www.w3.org/TR/xhtml1/#h-4.5)
	#
	# Returns the updated +html_options+ hash, which is also modified
	# in place.
	#
	# Example:
	#
	#   convert_boolean_attributes!( html_options,
	#                                %w( checked disabled readonly ) )
	private static function convert_boolean_attributes_(&$html_options, $bool_attrs){
		foreach($bool_attrs as $x){
			if( \PHPRails\delete($html_options,$x) ){
				$html_options[$x] = $x;
			}
		}
		return $html_options;
	}

	private static function token_tag($token=null){
		if( $token !== false && CsrfHelper::protect_against_forgery__() ){
			$token = $token ?: CsrfHelper::form_authenticity_token();
			return TagHelper::tag('input', array('type' => "hidden", 'name' => CsrfHelper::$request_forgery_protection_token, 'value' => $token));
		}else{
			return '';
		}
	}

	private static function method_tag($method){
		return TagHelper::tag('input', array('type' => 'hidden', 'name' => '_method', 'value' => (string)$method));
	}
}