<?php

try{
	PHPRails::import('i18n');
	PHPRails::import('active_support/lazy_load_hooks');
}catch( LoadError $e ){
	$stderr = fopen('php://stderr', 'w'); 
	fwrite($stderr, "The i18n gem is not available."); 
	fclose($stderr);
	# $stderr.puts "The i18n gem is not available. Please add it to your Gemfile and run bundle install"
	throw $e;
}

ActiveSupport::run_load_hooks('i18n');