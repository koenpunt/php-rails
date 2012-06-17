<?php 

namespace ActionView\Helpers;
#    extend ActiveSupport::Autoload
	
\PHPRails::import('action_view/asset_tag_helper');
\PHPRails::import('action_view/csrf_helper');
\PHPRails::import('action_view/date_helper');
\PHPRails::import('action_view/form_helper');
\PHPRails::import('action_view/form_tag_helper');
\PHPRails::import('action_view/tag_helper');

#    autoload :ActiveModelHelper
#    autoload :AssetTagHelper
#    autoload :AtomFeedHelper
#    autoload :BenchmarkHelper
#    autoload :CacheHelper
#    autoload :CaptureHelper
#    autoload :ControllerHelper
#    autoload :CsrfHelper
#    autoload :DateHelper
#    autoload :DebugHelper
#    autoload :FormHelper
#    autoload :FormOptionsHelper
#    autoload :FormTagHelper
#    autoload :JavaScriptHelper, "action_view/helpers/javascript_helper"
#    autoload :NumberHelper
#    autoload :OutputSafetyHelper
#    autoload :RecordTagHelper
#    autoload :RenderingHelper
#    autoload :SanitizeHelper
#    autoload :TagHelper
#    autoload :TextHelper
#    autoload :TranslationHelper
#    autoload :UrlHelper
#
#    extend ActiveSupport::Concern
#
#    included do
#      extend SanitizeHelper::ClassMethods
#    end
#
#    include ActiveModelHelper
#    include AssetTagHelper
#    include AtomFeedHelper
#    include BenchmarkHelper
#    include CacheHelper
#    include CaptureHelper
#    include ControllerHelper
#    include CsrfHelper
#    include DateHelper
#    include DebugHelper
#    include FormHelper
#    include FormOptionsHelper
#    include FormTagHelper
#    include JavaScriptHelper
#    include NumberHelper
#    include OutputSafetyHelper
#    include RecordTagHelper
#    include RenderingHelper
#    include SanitizeHelper
#    include TagHelper
#    include TextHelper
#    include TranslationHelper
#    include UrlHelper
