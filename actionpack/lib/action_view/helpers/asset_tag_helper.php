<?php

namespace ActionView\Helpers;

#\PHPRails::import('active_support/core_ext/array/extract_options');
#\PHPRails::import('active_support/core_ext/hash/keys');
\PHPRails::import('action_view/helpers/asset_tag_helpers/javascript_include_tag');
\PHPRails::import('action_view/helpers/asset_tag_helpers/stylesheet_include_tag');
\PHPRails::import('action_view/helpers/asset_tag_helpers/asset_paths');

#module ActionView
  # = Action View Asset Tag Helpers
  #module Helpers #:nodoc:
# This module provides methods for generating HTML that links views to assets such
# as images, javascripts, stylesheets, and feeds. These methods do not verify
# the assets exist before linking to them:
#
#   image_tag("rails.png")
#   # => <img alt="Rails" src="/assets/rails.png" />
#   stylesheet_link_tag("application")
#   # => <link href="/assets/application.css?body=1" media="screen" rel="stylesheet" />
#
#
# === Using asset hosts
#
# By default, Rails links to these assets on the current host in the public
# folder, but you can direct Rails to link to assets from a dedicated asset
# server by setting <tt>ActionController::Base.asset_host</tt> in the application
# configuration, typically in <tt>config/environments/production.rb</tt>.
# For example, you'd define <tt>assets.example.com</tt> to be your asset
# host this way, inside the <tt>configure</tt> block of your environment-specific
# configuration files or <tt>config/application.rb</tt>:
#
#   config.action_controller.asset_host = "assets.example.com"
#
# Helpers take that into account:
#
#   image_tag("rails.png")
#   # => <img alt="Rails" src="http://assets.example.com/assets/rails.png" />
#   stylesheet_link_tag("application")
#   # => <link href="http://assets.example.com/assets/application.css" media="screen" rel="stylesheet" />
#
# Browsers typically open at most two simultaneous connections to a single
# host, which means your assets often have to wait for other assets to finish
# downloading. You can alleviate this by using a <tt>%d</tt> wildcard in the
# +asset_host+. For example, "assets%d.example.com". If that wildcard is
# present Rails distributes asset requests among the corresponding four hosts
# "assets0.example.com", ..., "assets3.example.com". With this trick browsers
# will open eight simultaneous connections rather than two.
#
#   image_tag("rails.png")
#   # => <img alt="Rails" src="http://assets0.example.com/assets/rails.png" />
#   stylesheet_link_tag("application")
#   # => <link href="http://assets2.example.com/assets/application.css" media="screen" rel="stylesheet" />
#
# To do this, you can either setup four actual hosts, or you can use wildcard
# DNS to CNAME the wildcard to a single asset host. You can read more about
# setting up your DNS CNAME records from your ISP.
#
# Note: This is purely a browser performance optimization and is not meant
# for server load balancing. See http://www.die.net/musings/page_load_time/
# for background.
#
# Alternatively, you can exert more control over the asset host by setting
# +asset_host+ to a proc like this:
#
#   ActionController::Base.asset_host = Proc.new { |source|
#     "http://assets#{Digest::MD5.hexdigest(source).to_i(16) % 2 + 1}.example.com"
#   }
#   image_tag("rails.png")
#   # => <img alt="Rails" src="http://assets1.example.com/assets/rails.png" />
#   stylesheet_link_tag("application")
#   # => <link href="http://assets2.example.com/assets/application.css" media="screen" rel="stylesheet" />
#
# The example above generates "http://assets1.example.com" and
# "http://assets2.example.com". This option is useful for example if
# you need fewer/more than four hosts, custom host names, etc.
#
# As you see the proc takes a +source+ parameter. That's a string with the
# absolute path of the asset, for example "/assets/rails.png".
#
#    ActionController::Base.asset_host = Proc.new { |source|
#      if source.ends_with?('.css')
#        "http://stylesheets.example.com"
#      else
#        "http://assets.example.com"
#      end
#    }
#   image_tag("rails.png")
#   # => <img alt="Rails" src="http://assets.example.com/assets/rails.png" />
#   stylesheet_link_tag("application")
#   # => <link href="http://stylesheets.example.com/assets/application.css" media="screen" rel="stylesheet" />
#
# Alternatively you may ask for a second parameter +request+. That one is
# particularly useful for serving assets from an SSL-protected page. The
# example proc below disables asset hosting for HTTPS connections, while
# still sending assets for plain HTTP requests from asset hosts. If you don't
# have SSL certificates for each of the asset hosts this technique allows you
# to avoid warnings in the client about mixed media.
#
#   ActionController::Base.asset_host = Proc.new { |source, request|
#     if request.ssl?
#       "#{request.protocol}#{request.host_with_port}"
#     else
#       "#{request.protocol}assets.example.com"
#     end
#   }
#
# You can also implement a custom asset host object that responds to +call+
# and takes either one or two parameters just like the proc.
#
#   config.action_controller.asset_host = AssetHostingWithMinimumSsl.new(
#     "http://asset%d.example.com", "https://asset1.example.com"
#   )
#
# === Customizing the asset path
#
# By default, Rails appends asset's timestamps to all asset paths. This allows
# you to set a cache-expiration date for the asset far into the future, but
# still be able to instantly invalidate it by simply updating the file (and
# hence updating the timestamp, which then updates the URL as the timestamp
# is part of that, which in turn busts the cache).
#
# It's the responsibility of the web server you use to set the far-future
# expiration date on cache assets that you need to take advantage of this
# feature. Here's an example for Apache:
#
#   # Asset Expiration
#   ExpiresActive On
#   <FilesMatch "\.(ico|gif|jpe?g|png|js|css)$">
#     ExpiresDefault "access plus 1 year"
#   </FilesMatch>
#
# Also note that in order for this to work, all your application servers must
# return the same timestamps. This means that they must have their clocks
# synchronized. If one of them drifts out of sync, you'll see different
# timestamps at random and the cache won't work. In that case the browser
# will request the same assets over and over again even thought they didn't
# change. You can use something like Live HTTP Headers for Firefox to verify
# that the cache is indeed working.
#
# This strategy works well enough for most server setups and requires the
# least configuration, but if you deploy several application servers at
# different times - say to handle a temporary spike in load - then the
# asset time stamps will be out of sync. In a setup like this you may want
# to set the way that asset paths are generated yourself.
#
# Altering the asset paths that Rails generates can be done in two ways.
# The easiest is to define the RAILS_ASSET_ID environment variable. The
# contents of this variable will always be used in preference to
# calculated timestamps. A more complex but flexible way is to set
# <tt>ActionController::Base.config.asset_path</tt> to a proc
# that takes the unmodified asset path and returns the path needed for
# your asset caching to work. Typically you'd do something like this in
# <tt>config/environments/production.rb</tt>:
#
#   # Normally you'd calculate RELEASE_NUMBER at startup.
#   RELEASE_NUMBER = 12345
#   config.action_controller.asset_path = proc { |asset_path|
#     "/release-#{RELEASE_NUMBER}#{asset_path}"
#   }
#
# This example would cause the following behavior on all servers no
# matter when they were deployed:
#
#   image_tag("rails.png")
#   # => <img alt="Rails" src="/release-12345/images/rails.png" />
#   stylesheet_link_tag("application")
#   # => <link href="/release-12345/stylesheets/application.css?1232285206" media="screen" rel="stylesheet" />
#
# Changing the asset_path does require that your web servers have
# knowledge of the asset template paths that you rewrite to so it's not
# suitable for out-of-the-box use. To use the example given above you
# could use something like this in your Apache VirtualHost configuration:
#
#   <LocationMatch "^/release-\d+/(images|javascripts|stylesheets)/.*$">
#     # Some browsers still send conditional-GET requests if there's a
#     # Last-Modified header or an ETag header even if they haven't
#     # reached the expiry date sent in the Expires header.
#     Header unset Last-Modified
#     Header unset ETag
#     FileETag None
#
#     # Assets requested using a cache-busting filename should be served
#     # only once and then cached for a really long time. The HTTP/1.1
#     # spec frowns on hugely-long expiration times though and suggests
#     # that assets which never expire be served with an expiration date
#     # 1 year from access.
#     ExpiresActive On
#     ExpiresDefault "access plus 1 year"
#   </LocationMatch>
#
#   # We use cached-busting location names with the far-future expires
#   # headers to ensure that if a file does change it can force a new
#   # request. The actual asset filenames are still the same though so we
#   # need to rewrite the location from the cache-busting location to the
#   # real asset location so that we can serve it.
#   RewriteEngine On
#   RewriteRule ^/release-\d+/(images|javascripts|stylesheets)/(.*)$ /$1/$2 [L]
class AssetTagHelper{
	#use TagHelper,
	#	 JavascriptTagHelpers,
	#	 StylesheetTagHelpers;
		
	static $asset_paths = null;
	
	static $javascript_include = null;
	
	static $stylesheet_include = null;
		
	# Returns a link tag that browsers and news readers can use to auto-detect
	# an RSS or Atom feed. The +type+ can either be <tt>:rss</tt> (default) or
	# <tt>:atom</tt>. Control the link options in url_for format using the
	# +url_options+. You can modify the LINK tag itself in +tag_options+.
	#
	# ==== Options
	# * <tt>:rel</tt>  - Specify the relation of this link, defaults to "alternate"
	# * <tt>:type</tt>  - Override the auto-generated mime type
	# * <tt>:title</tt>  - Specify the title of the link, defaults to the +type+
	#
	# ==== Examples
	#  auto_discovery_link_tag # =>
	#     <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.currenthost.com/controller/action" />
	#  auto_discovery_link_tag(:atom) # =>
	#     <link rel="alternate" type="application/atom+xml" title="ATOM" href="http://www.currenthost.com/controller/action" />
	#  auto_discovery_link_tag(:rss, {:action => "feed"}) # =>
	#     <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.currenthost.com/controller/feed" />
	#  auto_discovery_link_tag(:rss, {:action => "feed"}, {:title => "My RSS"}) # =>
	#     <link rel="alternate" type="application/rss+xml" title="My RSS" href="http://www.currenthost.com/controller/feed" />
	#  auto_discovery_link_tag(:rss, {:controller => "news", :action => "feed"}) # =>
	#     <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.currenthost.com/news/feed" />
	#  auto_discovery_link_tag(:rss, "http://www.example.com/feed.rss", {:title => "Example RSS"}) # =>
	#     <link rel="alternate" type="application/rss+xml" title="Example RSS" href="http://www.example.com/feed" />
	public static function auto_discovery_link_tag($type = 'rss', $url_options = array(), $tag_options = array()){
		return TagHelper::tag(
			"link", array(
				"rel"   => $tag_options['rel'] ?: "alternate",
				"type"  => $tag_options['type'] ?: (string)\Mime\Type::lookup_by_extension((string)$type),
				"title" => $tag_options['title'] ?: strtoupper((string)$type),
				"href"  => \PHPRails\is_hash($url_options) ? UrlHelper::url_for(array_merge($url_options, array('only_path' => false))) : $url_options
			)
		);
	}

	#   <%= favicon_link_tag %>
	#
	# generates
	#
	#   <link href="/assets/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	#
	# You may specify a different file in the first argument:
	#
	#   <%= favicon_link_tag '/myicon.ico' %>
	#
	# That's passed to +path_to_image+ as is, so it gives
	#
	#   <link href="/myicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	#
	# The helper accepts an additional options hash where you can override "rel" and "type".
	#
	# For example, Mobile Safari looks for a different LINK tag, pointing to an image that
	# will be used if you add the page to the home screen of an iPod Touch, iPhone, or iPad.
	# The following call would generate such a tag:
	#
	#   <%= favicon_link_tag 'mb-icon.png', :rel => 'apple-touch-icon', :type => 'image/png' %>
	#
	public static function favicon_link_tag($source='favicon.ico', $options=array()){
		return TagHelper::tag('link', array_merge(array(
			'rel'  => 'shortcut icon',
			'type' => 'image/vnd.microsoft.icon',
			'href' => static::path_to_image($source)
		), $options));
	}

	# Computes the path to an image asset.
	# Full paths from the document root will be passed through.
	# Used internally by +image_tag+ to build the image path:
	#
	#   image_path("edit")                                         # => "/assets/edit"
	#   image_path("edit.png")                                     # => "/assets/edit.png"
	#   image_path("icons/edit.png")                               # => "/assets/icons/edit.png"
	#   image_path("/icons/edit.png")                              # => "/icons/edit.png"
	#   image_path("http://www.example.com/img/edit.png")          # => "http://www.example.com/img/edit.png"
	#
	# If you have images as application resources this method may conflict with their named routes.
	# The alias +path_to_image+ is provided to avoid that. Rails uses the alias internally, and
	# plugin authors are encouraged to do so.
	public static function image_path($source){
		return static::asset_paths()->compute_public_path($source, 'images');
	}
	
	# aliased to avoid conflicts with an image_path named route
	public static function path_to_image($source){
		return static::image_path($source);
	}
	
	# Computes the full URL to an image asset.
	# This will use +image_path+ internally, so most of their behaviors will be the same.
	public static function image_url($source){
		return (string)RURI::join(static::current_host(), static::path_to_image($source));
	}

	# aliased to avoid conflicts with an image_url named route
	public static function url_to_image($source){
		return static::image_url($source);
	}

	# Computes the path to a video asset in the public videos directory.
	# Full paths from the document root will be passed through.
	# Used internally by +video_tag+ to build the video path.
	#
	# ==== Examples
	#   video_path("hd")                                            # => /videos/hd
	#   video_path("hd.avi")                                        # => /videos/hd.avi
	#   video_path("trailers/hd.avi")                               # => /videos/trailers/hd.avi
	#   video_path("/trailers/hd.avi")                              # => /trailers/hd.avi
	#   video_path("http://www.example.com/vid/hd.avi")             # => http://www.example.com/vid/hd.avi
	public static function video_path($source){
		return static::asset_paths()->compute_public_path($source, 'videos');
	}
	
	# aliased to avoid conflicts with a video_path named route
	public static function path_to_video($source){
		return static::video_path($source);
	}
	
	# Computes the full URL to a video asset in the public videos directory.
	# This will use +video_path+ internally, so most of their behaviors will be the same.
	public static function video_url($source){
		return (string)RURI::join(static::current_host(), static::path_to_video($source));
	}
	# aliased to avoid conflicts with an video_url named route
	public static function url_to_video($source){
		return static::video_url($source);
	}

	# Computes the path to an audio asset in the public audios directory.
	# Full paths from the document root will be passed through.
	# Used internally by +audio_tag+ to build the audio path.
	#
	# ==== Examples
	#   audio_path("horse")                                            # => /audios/horse
	#   audio_path("horse.wav")                                        # => /audios/horse.wav
	#   audio_path("sounds/horse.wav")                                 # => /audios/sounds/horse.wav
	#   audio_path("/sounds/horse.wav")                                # => /sounds/horse.wav
	#   audio_path("http://www.example.com/sounds/horse.wav")          # => http://www.example.com/sounds/horse.wav
	public static function audio_path($source){
		return static::asset_paths()->compute_public_path($source, 'audios');
	}
	# aliased to avoid conflicts with an audio_path named route
	public static function path_to_audio($source){
		return static::audio_path($source);
	}

	# Computes the full URL to a audio asset in the public audios directory.
	# This will use +audio_path+ internally, so most of their behaviors will be the same.
	public static function audio_url($source){
		return (string)RURI::join(static::current_host(), static::path_to_audio(source));
	}
	# aliased to avoid conflicts with an audio_url named route
	public static function url_to_audio($source){
		return static::audio_url($source);
	}

	# Computes the path to a font asset.
	# Full paths from the document root will be passed through.
	#
	# ==== Examples
	#   font_path("font")                                           # => /assets/font
	#   font_path("font.ttf")                                       # => /assets/font.ttf
	#   font_path("dir/font.ttf")                                   # => /assets/dir/font.ttf
	#   font_path("/dir/font.ttf")                                  # => /dir/font.ttf
	#   font_path("http://www.example.com/dir/font.ttf")            # => http://www.example.com/dir/font.ttf
	public static function font_path($source){
		return static::asset_paths()->compute_public_path($source, 'fonts');
	}
	# aliased to avoid conflicts with an font_path named route
	public static function path_to_font($source){
		return static::font_path($source);
	}

	# Computes the full URL to a font asset.
	# This will use +font_path+ internally, so most of their behaviors will be the same.
	public static function font_url($source){
		return (string)RURI::join(static::current_host(), static::path_to_font($source));
	}
	# aliased to avoid conflicts with an font_url named route
	public static function url_to_font($source){
		return static::font_url($source);
	}

	# Returns an html image tag for the +source+. The +source+ can be a full
	# path or a file.
	#
	# ==== Options
	# You can add HTML attributes using the +options+. The +options+ supports
	# three additional keys for convenience and conformance:
	#
	# * <tt>:alt</tt>  - If no alt text is given, the file name part of the
	#   +source+ is used (capitalized and without the extension)
	# * <tt>:size</tt> - Supplied as "{Width}x{Height}", so "30x45" becomes
	#   width="30" and height="45". <tt>:size</tt> will be ignored if the
	#   value is not in the correct format.
	# * <tt>:mouseover</tt> - Set an alternate image to be used when the onmouseover
	#   event is fired, and sets the original image to be replaced onmouseout.
	#   This can be used to implement an easy image toggle that fires on onmouseover.
	#
	# ==== Examples
	#  image_tag("icon")  # =>
	#    <img src="/assets/icon" alt="Icon" />
	#  image_tag("icon.png")  # =>
	#    <img src="/assets/icon.png" alt="Icon" />
	#  image_tag("icon.png", :size => "16x10", :alt => "Edit Entry")  # =>
	#    <img src="/assets/icon.png" width="16" height="10" alt="Edit Entry" />
	#  image_tag("/icons/icon.gif", :size => "16x16")  # =>
	#    <img src="/icons/icon.gif" width="16" height="16" alt="Icon" />
	#  image_tag("/icons/icon.gif", :height => '32', :width => '32') # =>
	#    <img alt="Icon" height="32" src="/icons/icon.gif" width="32" />
	#  image_tag("/icons/icon.gif", :class => "menu_icon") # =>
	#    <img alt="Icon" class="menu_icon" src="/icons/icon.gif" />
	#  image_tag("mouse.png", :mouseover => "/assets/mouse_over.png") # =>
	#    <img src="/assets/mouse.png" onmouseover="this.src='/assets/mouse_over.png'" onmouseout="this.src='/assets/mouse.png'" alt="Mouse" />
	#  image_tag("mouse.png", :mouseover => image_path("mouse_over.png")) # =>
	#    <img src="/assets/mouse.png" onmouseover="this.src='/assets/mouse_over.png'" onmouseout="this.src='/assets/mouse.png'" alt="Mouse" />
	public static function image_tag($source, $options=array()){
		#$options = options.symbolize_keys
		
		$src = $options['src'] = static::path_to_image($source);
		
		if(preg_match('/^cid:/', $src) == 0){
			$options['alt'] = \PHPRails\fetch($options, 'alt', function($key){ return static::image_alt($src); });
		}
		
		if( ($size = \PHPRails\delete($options, 'size')) ){
			if(preg_match('/^\d+x\d+$/', $size)){
				list($options['width'], $options['height']) = explode('x', $size);
			}
		}
		
		if( ($mouseover = \PHPRails\delete($options, 'mouseover')) ){
			$options['onmouseover'] = sprintf("this.src='%s'", static::path_to_image($mouseover));
			$options['onmouseout']  = sprintf("this.src='%s'", $src);
		}
		
		return TagHelper::tag("img", $options);
	}

	public static function image_alt($src){
		return ucfirst(preg_replace('/-[[:xdigit:]]{32}\z/', '', \RFile::basename($src, '.*'), 1));
	}

	# Returns an html video tag for the +sources+. If +sources+ is a string,
	# a single video tag will be returned. If +sources+ is an array, a video
	# tag with nested source tags for each source will be returned. The
	# +sources+ can be full paths or files that exists in your public videos
	# directory.
	#
	# ==== Options
	# You can add HTML attributes using the +options+. The +options+ supports
	# two additional keys for convenience and conformance:
	#
	# * <tt>:poster</tt> - Set an image (like a screenshot) to be shown
	#   before the video loads. The path is calculated like the +src+ of +image_tag+.
	# * <tt>:size</tt> - Supplied as "{Width}x{Height}", so "30x45" becomes
	#   width="30" and height="45". <tt>:size</tt> will be ignored if the
	#   value is not in the correct format.
	#
	# ==== Examples
	#  video_tag("trailer")  # =>
	#    <video src="/videos/trailer" />
	#  video_tag("trailer.ogg")  # =>
	#    <video src="/videos/trailer.ogg" />
	#  video_tag("trailer.ogg", :controls => true, :autobuffer => true)  # =>
	#    <video autobuffer="autobuffer" controls="controls" src="/videos/trailer.ogg" />
	#  video_tag("trailer.m4v", :size => "16x10", :poster => "screenshot.png")  # =>
	#    <video src="/videos/trailer.m4v" width="16" height="10" poster="/assets/screenshot.png" />
	#  video_tag("/trailers/hd.avi", :size => "16x16")  # =>
	#    <video src="/trailers/hd.avi" width="16" height="16" />
	#  video_tag("/trailers/hd.avi", :height => '32', :width => '32') # =>
	#    <video height="32" src="/trailers/hd.avi" width="32" />
	#  video_tag("trailer.ogg", "trailer.flv") # =>
	#    <video><source src="/videos/trailer.ogg" /><source src="/videos/trailer.flv" /></video>
	#  video_tag(["trailer.ogg", "trailer.flv"]) # =>
	#    <video><source src="/videos/trailer.ogg" /><source src="/videos/trailer.flv" /></video>
	#  video_tag(["trailer.ogg", "trailer.flv"], :size => "160x120") # =>
	#    <video height="120" width="160"><source src="/videos/trailer.ogg" /><source src="/videos/trailer.flv" /></video>
	public static function video_tag($sources){
		$arguments = func_get_args();
		$options   = \PHPRails\extract_options($sources);
		
		return multiple_sources_tag('video', $sources, function(&$options){
			if( \PHPRails\get($options, 'poster') ){
				$options['poster'] = static::path_to_image($options['poster']);
			}

			if( ($size = \PHPRails\delete($options, 'size')) ){
				if(preg_match('/^\d+x\d+$/', $size)){
					list($options['width'], $options['height']) = explode('x', $size);
				}
			}
		});
	}

	# Returns an html audio tag for the +source+.
	# The +source+ can be full path or file that exists in
	# your public audios directory.
	#
	# ==== Examples
	#  audio_tag("sound")  # =>
	#    <audio src="/audios/sound" />
	#  audio_tag("sound.wav")  # =>
	#    <audio src="/audios/sound.wav" />
	#  audio_tag("sound.wav", :autoplay => true, :controls => true)  # =>
	#    <audio autoplay="autoplay" controls="controls" src="/audios/sound.wav" />
	#  audio_tag("sound.wav", "sound.mid")  # =>
	#    <audio><source src="/audios/sound.wav" /><source src="/audios/sound.mid" /></audio>
	public static function audio_tag($sources){
		return static::multiple_sources_tag('audio', $sources);
	}
	
	#module JavascriptTagHelpers
	#extend ActiveSupport::Concern
	
	#module ClassMethods
	# Register one or more javascript files to be included when <tt>symbol</tt>
	# is passed to <tt>javascript_include_tag</tt>. This method is typically intended
	# to be called from plugin initialization to register javascript files
	# that the plugin installed in <tt>public/javascripts</tt>.
	#
	#   ActionView::Helpers::AssetTagHelper.register_javascript_expansion :monkey => ["head", "body", "tail"]
	#
	#   javascript_include_tag :monkey # =>
	#     <script src="/javascripts/head.js"></script>
	#     <script src="/javascripts/body.js"></script>
	#     <script src="/javascripts/tail.js"></script>
	public static function register_javascript_expansion($expansions){
		$js_expansions = JavascriptIncludeTag::$expansions;
		foreach($$expansions as $key => $values){
			$js_expansions[$key] = array_unique(array_merge($js_expansions[$key] ?: array(), (array)$values));
		}
	}

	# Computes the path to a javascript asset in the public javascripts directory.
	# If the +source+ filename has no extension, .js will be appended (except for explicit URIs)
	# Full paths from the document root will be passed through.
	# Used internally by javascript_include_tag to build the script path.
	#
	# ==== Examples
	#   javascript_path "xmlhr"                              # => /javascripts/xmlhr.js
	#   javascript_path "dir/xmlhr.js"                       # => /javascripts/dir/xmlhr.js
	#   javascript_path "/dir/xmlhr"                         # => /dir/xmlhr.js
	#   javascript_path "http://www.example.com/js/xmlhr"    # => http://www.example.com/js/xmlhr
	#   javascript_path "http://www.example.com/js/xmlhr.js" # => http://www.example.com/js/xmlhr.js
	public static function javascript_path($source){
		return static::asset_paths()->compute_public_path($source, 'javascript_path', array('ext' => 'js'));
	}
	# aliased to avoid conflicts with an javascript_path named route
	public static function path_to_javascript($source){
		return static::javascript_path($source);
	}

	# Computes the full URL to a javascript asset in the public javascripts directory.
	# This will use +javascript_path+ internally, so most of their behaviors will be the same.
	public static function javascript_url($source){
		return (string)\RURI::join(static::current_host(), static::path_to_javascript($source));
	}
	# aliased to avoid conflicts with a javascript_url named route
	public static function url_to_javascript($source){
		return static::javascript_url($source);
	}

	# Returns an HTML script tag for each of the +sources+ provided.
	#
	# Sources may be paths to JavaScript files. Relative paths are assumed to be relative
	# to <tt>public/javascripts</tt>, full paths are assumed to be relative to the document
	# root. Relative paths are idiomatic, use absolute paths only when needed.
	#
	# When passing paths, the ".js" extension is optional.
	#
	# If the application is not using the asset pipeline, to include the default JavaScript
	# expansion pass <tt>:defaults</tt> as source. By default, <tt>:defaults</tt> loads jQuery,
	# and that can be overridden in <tt>config/application.rb</tt>:
	#
	#   config.action_view.javascript_expansions[:defaults] = %w(foo.js bar.js)
	#
	# When using <tt>:defaults</tt>, if an <tt>application.js</tt> file exists in
	# <tt>public/javascripts</tt> it will be included as well at the end.
	#
	# You can modify the HTML attributes of the script tag by passing a hash as the
	# last argument.
	#
	# ==== Examples
	#   javascript_include_tag "xmlhr"
	#   # => <script src="/javascripts/xmlhr.js?1284139606"></script>
	#
	#   javascript_include_tag "xmlhr.js"
	#   # => <script src="/javascripts/xmlhr.js?1284139606"></script>
	#
	#   javascript_include_tag "common.javascript", "/elsewhere/cools"
	#   # => <script src="/javascripts/common.javascript?1284139606"></script>
	#   #    <script src="/elsewhere/cools.js?1423139606"></script>
	#
	#   javascript_include_tag "http://www.example.com/xmlhr"
	#   # => <script src="http://www.example.com/xmlhr"></script>
	#
	#   javascript_include_tag "http://www.example.com/xmlhr.js"
	#   # => <script src="http://www.example.com/xmlhr.js"></script>
	#
	#   javascript_include_tag :defaults
	#   # => <script src="/javascripts/jquery.js?1284139606"></script>
	#   #    <script src="/javascripts/rails.js?1284139606"></script>
	#   #    <script src="/javascripts/application.js?1284139606"></script>
	#
	# You can also include all JavaScripts in the +javascripts+ directory using <tt>:all</tt> as the source:
	#
	#   javascript_include_tag :all
	#   # => <script src="/javascripts/jquery.js?1284139606"></script>
	#   #    <script src="/javascripts/rails.js?1284139606"></script>
	#   #    <script src="/javascripts/application.js?1284139606"></script>
	#   #    <script src="/javascripts/shop.js?1284139606"></script>
	#   #    <script src="/javascripts/checkout.js?1284139606"></script>
	#
	# Note that your defaults of choice will be included first, so they will be available to all subsequently
	# included files.
	#
	# If you want Rails to search in all the subdirectories under <tt>public/javascripts</tt>, you should
	# explicitly set <tt>:recursive</tt>:
	#
	#   javascript_include_tag :all, :recursive => true
	#
	# == Caching multiple JavaScripts into one
	#
	# You can also cache multiple JavaScripts into one file, which requires less HTTP connections to download
	# and can better be compressed by gzip (leading to faster transfers). Caching will only happen if
	# <tt>config.perform_caching</tt> is set to true (which is the case by default for the Rails
	# production environment, but not for the development environment).
	#
	# ==== Examples
	#
	#   # assuming config.perform_caching is false
	#   javascript_include_tag :all, :cache => true
	#   # => <script src="/javascripts/jquery.js?1284139606"></script>
	#   #    <script src="/javascripts/rails.js?1284139606"></script>
	#   #    <script src="/javascripts/application.js?1284139606"></script>
	#   #    <script src="/javascripts/shop.js?1284139606"></script>
	#   #    <script src="/javascripts/checkout.js?1284139606"></script>
	#
	#   # assuming config.perform_caching is true
	#   javascript_include_tag :all, :cache => true
	#   # => <script src="/javascripts/all.js?1344139789"></script>
	#
	#   # assuming config.perform_caching is false
	#   javascript_include_tag "jquery", "cart", "checkout", :cache => "shop"
	#   # => <script src="/javascripts/jquery.js?1284139606"></script>
	#   #    <script src="/javascripts/cart.js?1289139157"></script>
	#   #    <script src="/javascripts/checkout.js?1299139816"></script>
	#
	#   # assuming config.perform_caching is true
	#   javascript_include_tag "jquery", "cart", "checkout", :cache => "shop"
	#   # => <script src="/javascripts/shop.js?1299139816"></script>
	#
	# The <tt>:recursive</tt> option is also available for caching:
	#
	#   javascript_include_tag :all, :cache => true, :recursive => true
	public static function javascript_include_tag(/* $sources */){
		$sources = func_get_args();
		static::$javascript_include = static::$javascript_include ?: new AssetTagHelper\JavascriptIncludeTag(\PHPRails::config(), static::asset_paths());
		return call_user_func_array(array(static::$javascript_include, 'include_tag'), $sources);
	}

	#module StylesheetTagHelpers
	#extend ActiveSupport::Concern

	#module ClassMethods
	# Register one or more stylesheet files to be included when <tt>symbol</tt>
	# is passed to <tt>stylesheet_link_tag</tt>. This method is typically intended
	# to be called from plugin initialization to register stylesheet files
	# that the plugin installed in <tt>public/stylesheets</tt>.
	#
	#   ActionView::Helpers::AssetTagHelper.register_stylesheet_expansion :monkey => ["head", "body", "tail"]
	#
	#   stylesheet_link_tag :monkey # =>
	#     <link href="/stylesheets/head.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/body.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/tail.css"  media="screen" rel="stylesheet" type="text/css" />
	public static function register_stylesheet_expansion($expansions){
		$style_expansions = StylesheetIncludeTag::$expansions;
		foreach($$expansions as $key => $values){
			$style_expansions[$key] = array_unique(array_merge($style_expansions[$key] ?: array(), (array)$values));
		}
	}

	# Computes the path to a stylesheet asset in the public stylesheets directory.
	# If the +source+ filename has no extension, <tt>.css</tt> will be appended (except for explicit URIs).
	# Full paths from the document root will be passed through.
	# Used internally by +stylesheet_link_tag+ to build the stylesheet path.
	# 
	# ==== Examples
	#   stylesheet_path "style"                                  # => /stylesheets/style.css
	#   stylesheet_path "dir/style.css"                          # => /stylesheets/dir/style.css
	#   stylesheet_path "/dir/style.css"                         # => /dir/style.css
	#   stylesheet_path "http://www.example.com/css/style"       # => http://www.example.com/css/style
	#   stylesheet_path "http://www.example.com/css/style.css"   # => http://www.example.com/css/style.css
	public static function stylesheet_path($source){
		return static::asset_paths()->compute_public_path($source, 'stylesheets', array('ext' => 'css', 'protocol' => 'request'));
	}
	# aliased to avoid conflicts with a stylesheet_path named route
	public static function path_to_stylesheet($source){
		return static::stylesheet_path($source);
	}

	# Returns a stylesheet link tag for the sources specified as arguments. If
	# you don't specify an extension, <tt>.css</tt> will be appended automatically.
	# You can modify the link attributes by passing a hash as the last argument.
	#
	# ==== Examples
	#   stylesheet_link_tag "style" # =>
	#     <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "style.css" # =>
	#     <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "http://www.example.com/style.css" # =>
	#     <link href="http://www.example.com/style.css" media="screen" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "style", :media => "all" # =>
	#     <link href="/stylesheets/style.css" media="all" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "style", :media => "print" # =>
	#     <link href="/stylesheets/style.css" media="print" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "random.styles", "/css/stylish" # =>
	#     <link href="/stylesheets/random.styles" media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/css/stylish.css" media="screen" rel="stylesheet" type="text/css" />
	#
	# You can also include all styles in the stylesheets directory using <tt>:all</tt> as the source:
	#
	#   stylesheet_link_tag :all # =>
	#     <link href="/stylesheets/style1.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/styleB.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/styleX2.css" media="screen" rel="stylesheet" type="text/css" />
	#
	# If you want Rails to search in all the subdirectories under stylesheets, you should explicitly set <tt>:recursive</tt>:
	#
	#   stylesheet_link_tag :all, :recursive => true
	#
	# == Caching multiple stylesheets into one
	#
	# You can also cache multiple stylesheets into one file, which requires less HTTP connections and can better be
	# compressed by gzip (leading to faster transfers). Caching will only happen if config.perform_caching
	# is set to true (which is the case by default for the Rails production environment, but not for the development
	# environment). Examples:
	#
	# ==== Examples
	#   stylesheet_link_tag :all, :cache => true # when config.perform_caching is false =>
	#     <link href="/stylesheets/style1.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/styleB.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/styleX2.css" media="screen" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag :all, :cache => true # when config.perform_caching is true =>
	#     <link href="/stylesheets/all.css"  media="screen" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "shop", "cart", "checkout", :cache => "payment" # when config.perform_caching is false =>
	#     <link href="/stylesheets/shop.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/cart.css"  media="screen" rel="stylesheet" type="text/css" />
	#     <link href="/stylesheets/checkout.css" media="screen" rel="stylesheet" type="text/css" />
	#
	#   stylesheet_link_tag "shop", "cart", "checkout", :cache => "payment" # when config.perform_caching is true =>
	#     <link href="/stylesheets/payment.css"  media="screen" rel="stylesheet" type="text/css" />
	#
	# The <tt>:recursive</tt> option is also available for caching:
	#
	#   stylesheet_link_tag :all, :cache => true, :recursive => true
	#
	# To force concatenation (even in development mode) set <tt>:concat</tt> to true. This is useful if
	# you have too many stylesheets for IE to load.
	#
	#   stylesheet_link_tag :all, :concat => true
	#
	public static function stylesheet_link_tag(/* $sources */){
		$sources = func_get_args();
		static::$stylesheet_include = static::$stylesheet_include ?: new AssetTagHelper\StylesheetIncludeTag(\PHPRails::config(), static::asset_paths());
		return call_user_func_array(array(static::$stylesheet_include, 'include_tag'), $sources);
	}

	private static function asset_paths(){
		/*
			TODO controller
		*/
		if(is_null(static::$asset_paths)){
			static::$asset_paths = new AssetTagHelper\AssetPaths(\PHPRails::config() /*, $this->controller */);
		}
		return static::$asset_paths;
	}

	private static function multiple_sources_tag($type, $sources /*, &$block */){
		$options = \PHPRails\extract_options($sources);
		$sources = \PHPRails\array_flatten($sources);

		if( $yield = \PHPRails\block_given__(func_get_args()) ){
			$yield( $options );
		}

		if( count($sources) > 1 ){
			return TagHelper::content_tag($type, $options, function() use ($type, $sources){
				return array_map(function($source){
					return TagHelper::tag("source", array('src' => call_user_func(array(__CLASS__, "path_to_{$type}"), $source)));
				}, $sources);
			});
		}else{
			$options['src'] = call_user_func(array(__CLASS__, "path_to_{$type}"), reset($sources));
			return TagHelper::content_tag($type, null, $options);
		}
	}

	private static function current_host(){
		return UrlHelper::url_for(array('only_path' => false));
	}
}
