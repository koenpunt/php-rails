<?php 



namespace ActiveSupport;

# in case active_support/inflector is required without the rest of active_support
#require 'active_support/inflector/inflections'
#require 'active_support/inflector/transliterate'
#require 'active_support/inflector/methods'
#
#require 'active_support/inflections'
#require 'active_support/core_ext/string/inflections'
// http://api.rubyonrails.org/classes/ActiveSupport/Inflector.html

\PHPRails::uses('active_support/multibyte/unicode');

# The Inflector transforms words from singular to plural, class names to table names, modularized class names to ones without,
# and class names to foreign keys. The default inflections for pluralization, singularization, and uncountable words are kept
# in inflections.rb.
#
# The Rails core team has stated patches for the inflections library will not be accepted
# in order to avoid breaking legacy applications which may be relying on errant inflections.
# If you discover an incorrect inflection and require it for your application, you'll need
# to correct it yourself (explained below).
class Inflector{
	#extend self

	# Returns the plural form of the word in the string.
	#
	# Examples:
	#   "post".pluralize             # => "posts"
	#   "octopus".pluralize          # => "octopi"
	#   "sheep".pluralize            # => "sheep"
	#   "words".pluralize            # => "words"
	#   "CamelOctopus".pluralize     # => "CamelOctopi"
	public static function pluralize($word){
		return self::apply_inflections($word, self::inflections()->plurals);
	}

	# The reverse of +pluralize+, returns the singular form of a word in a string.
	#
	# Examples:
	#   "posts".singularize            # => "post"
	#   "octopi".singularize           # => "octopus"
	#   "sheep".singularize            # => "sheep"
	#   "word".singularize             # => "word"
	#   "CamelOctopi".singularize      # => "CamelOctopus"
	public static function singularize($word){
		return self::apply_inflections($word, self::inflections()->singulars);
	}

	# By default, +camelize+ converts strings to UpperCamelCase. If the argument to +camelize+
	# is set to <tt>:lower</tt> then +camelize+ produces lowerCamelCase.
	#
	# +camelize+ will also convert '/' to '::' which is useful for converting paths to namespaces.
	#
	# Examples:
	#   "active_model".camelize                # => "ActiveModel"
	#   "active_model".camelize(:lower)        # => "activeModel"
	#   "active_model/errors".camelize         # => "ActiveModel::Errors"
	#   "active_model/errors".camelize(:lower) # => "activeModel::Errors"
	#
	# As a rule of thumb you can think of +camelize+ as the inverse of +underscore+,
	# though there are cases where that does not hold:
	#
	#   "SSLError".underscore.camelize # => "SslError"
	public static function camelize($term, $uppercase_first_letter = true){
		$string = (string)$term;
		if( $uppercase_first_letter ){
			$string = preg_replace_callback('/^[a-z\d]*/', function($matches){ return self::inflections()->acronyms[$matches[0]] ?: ucfirst($matches[0]); }, $string, 1);
		}else{
			$acronym_regex = self::inflections()->acronym_regex;
			$string = preg_replace_callback("/^(?:{$acronym_regex}(?=\b|[A-Z_])|\w)/", function($matches) { return strtolower($matches[0]); }, $string);
		}
		return preg_replace_callback('/(?:_|(\/))([a-z\d]*)/i', function($matches){ 
			return str_replace('/', '::', "{$matches[1]}" . (self::inflections()->acronyms[$matches[2]] ?: ucfirst($matches[2])));
		}, $string);
	}
	

	# Makes an underscored, lowercase form from the expression in the string.
	#
	# Changes '::' to '/' to convert namespaces to paths.
	#
	# Examples:
	#   "ActiveRecord".underscore         # => "active_record"
	#   "ActiveRecord::Errors".underscore # => active_record/errors
	#
	# As a rule of thumb you can think of +underscore+ as the inverse of +camelize+,
	# though there are cases where that does not hold:
	#
	#   "SSLError".underscore.camelize # => "SslError"
	public static function underscore($camel_cased_word){
		$word = $camel_cased_word;
		$word = preg_replace('/::/', '/', $word);
		$acronym_regex = self::inflections()->acronym_regex;
		$word = preg_replace_callback("/(?:([A-Za-z\d])|^)({$acronym_regex})(?=\b|[^a-z])/", function($matches){ 
			return "{$matches[1]}" . ($matches[1] ? '_' : '') . strtolower($matches[2]); 
		}, $word);
		$word = preg_replace('/([A-Z\d]+)([A-Z][a-z])/','$1_$2', $word);
		$word = preg_replace('/([a-z\d])([A-Z])/','$1_$2', $word);
		$word = strtr($word, '-', '_');
		$word = strtolower($word);
		return $word;
	}

	# Capitalizes the first word and turns underscores into spaces and strips a
	# trailing "_id", if any. Like +titleize+, this is meant for creating pretty output.
	#
	# Examples:
	#   "employee_salary" # => "Employee salary"
	#   "author_id"       # => "Author"
	public static function humanize($lower_case_and_underscored_word){
		$result = $lower_case_and_underscored_word;
		foreach(self::inflections()->humans as $rule => $replacement){
			if(preg_replace($rule, $replacement, $result, 1))break; 
		};
		$result = preg_replace('/_id$/', "", $result);
		$result = strtr('_', ' ', $result);
		
		return ucfirst(preg_replace_callback('/([a-z\d]*)/i', function($matches){
			return self::inflections()->acronyms[$matches[0]] ?: strtolower($matches[0]);
		}, $result));
		
		#return $result->gsub('/(_)?([a-z\d]*)/i', "#{$1 && ' '}#{inflections.acronyms[$2] || $2.downcase}" }.gsub(/^\w/) { $&.upcase }
	}

	# Capitalizes all the words and replaces some characters in the string to create
	# a nicer looking title. +titleize+ is meant for creating pretty output. It is not
	# used in the Rails internals.
	#
	# +titleize+ is also aliased as as +titlecase+.
	#
	# Examples:
	#   "man from the boondocks".titleize   # => "Man From The Boondocks"
	#   "x-men: the last stand".titleize    # => "X Men: The Last Stand"
	#   "TheManWithoutAPast".titleize       # => "The Man Without A Past"
	#   "raiders_of_the_lost_ark".titleize  # => "Raiders Of The Lost Ark"
	public static function titleize($word){
		return preg_replace_callback("/\b(?<!['’`])[a-z]/", function($r1) use ($word){ 
			return ucfirst($r1); 
		}, self::humanize(self::underscore($word)));
	}

	# Create the name of a table like Rails does for models to table names. This method
	# uses the +pluralize+ method on the last word in the string.
	#
	# Examples
	#   "RawScaledScorer".tableize # => "raw_scaled_scorers"
	#   "egg_and_ham".tableize     # => "egg_and_hams"
	#   "fancyCategory".tableize   # => "fancy_categories"
	public static function tableize($class_name){
		return self::pluralize(self::underscore($class_name));
	}

	# Create a class name from a plural table name like Rails does for table names to models.
	# Note that this returns a string and not a Class. (To convert to an actual class
	# follow +classify+ with +constantize+.)
	#
	# Examples:
	#   "egg_and_hams".classify # => "EggAndHam"
	#   "posts".classify        # => "Post"
	#
	# Singular names are not handled correctly:
	#   "business".classify     # => "Busines"
	public static function classify($table_name){
		# strip out any leading schema name
		return self::camelize(self::singularize(preg_replace('/.*\./', '', $table_name, 1)));
	}

	# Replaces underscores with dashes in the string.
	#
	# Example:
	#   "puni_puni" # => "puni-puni"
	public static function dasherize($underscored_word){
		return preg_replace('/_/', '-', $underscored_word);
	}

	# Removes the module part from the expression in the string.
	#
	# Examples:
	#   "ActiveRecord::CoreExtensions::String::Inflections".demodulize # => "Inflections"
	#   "Inflections".demodulize                                       # => "Inflections"
	public static function demodulize($class_name_in_module){
		return preg_replace('/^.*::/', '', $class_name_in_module);
	}

	# Creates a foreign key name from a class name.
	# +separate_class_name_and_id_with_underscore+ sets whether
	# the method should put '_' between the name and 'id'.
	#
	# Examples:
	#   "Message".foreign_key        # => "message_id"
	#   "Message".foreign_key(false) # => "messageid"
	#   "Admin::Post".foreign_key    # => "post_id"
	public static function foreign_key($class_name, $separate_class_name_and_id_with_underscore = true){
		return self::underscore(self::demodulize($class_name)) . ($separate_class_name_and_id_with_underscore ? "_id" : "id");
	}

	# Tries to find a constant with the name specified in the argument string:
	#
	#   "Module".constantize     # => Module
	#   "Test::Unit".constantize # => Test::Unit
	#
	# The name is assumed to be the one of a top-level constant, no matter whether
	# it starts with "::" or not. No lexical context is taken into account:
	#
	#   C = 'outside'
	#   module M
	#     C = 'inside'
	#     C               # => 'inside'
	#     "C".constantize # => 'outside', same as ::C
	#   end
	#
	# NameError is raised when the name is not in CamelCase or the constant is
	# unknown.
	public static function constantize($camel_cased_word){
		$names = explode('::', $camel_cased_word);
		$first = reset($names);
		if(empty($names) || empty($first)){
			array_shift($names);
		}
		#names.inject(Object) do |constant, name|
		#	constant.const_get(name, false)
		#end
		$constant = new CoreExt\Object();
		foreach($names as $name){
			$constant = $constant->const_defined__($name) ? $constant->const_get($name) : $constant->const_missing($name);
		}
		return $constant;
	}
	
	# Tries to find a constant with the name specified in the argument string:
	#
	#   "Module".safe_constantize     # => Module
	#   "Test::Unit".safe_constantize # => Test::Unit
	#
	# The name is assumed to be the one of a top-level constant, no matter whether
	# it starts with "::" or not. No lexical context is taken into account:
	#
	#   C = 'outside'
	#   module M
	#     C = 'inside'
	#     C                    # => 'inside'
	#     "C".safe_constantize # => 'outside', same as ::C
	#   end
	#
	# nil is returned when the name is not in CamelCase or the constant (or part of it) is
	# unknown.
	#
	#   "blargle".safe_constantize  # => nil
	#   "UnknownModule".safe_constantize  # => nil
	#   "UnknownModule::Foo::Bar".safe_constantize  # => nil
	#
	public static function safe_constantize($camel_cased_word){
		try{
			return self::constantize($camel_cased_word);
		}catch(NameError $e){
			#raise unless e.message =~ /uninitialized constant #{const_regexp(camel_cased_word)}$/ || e.name.to_s == camel_cased_word.to_s
		}catch(ArgumentError $e){
			#raise unless e.message =~ /not missing constant #{const_regexp(camel_cased_word)}\!$/
		}
	}

	# Turns a number into an ordinal string used to denote the position in an
	# ordered sequence such as 1st, 2nd, 3rd, 4th.
	#
	# Examples:
	#   ordinalize(1)     # => "1st"
	#   ordinalize(2)     # => "2nd"
	#   ordinalize(1002)  # => "1002nd"
	#   ordinalize(1003)  # => "1003rd"
	#   ordinalize(-11)   # => "-11th"
	#   ordinalize(-1021) # => "-1021st"
	public static function ordinalize($number){
		$number_abs = abs($number);
		if(in_array($number_abs % 100, range(11, 13))){
			return "{$number}th";
		}else{
			switch($number_abs % 10){
				case 1:
					return "{$number}st";
				case 2:
					return "{$number}nd";
				case 3: 
					return "{$number}rd";
				default: 
					return "{$number}th";
			}
		}
	}

	# Mount a regular expression that will match part by part of the constant.
	# For instance, Foo::Bar::Baz will generate Foo(::Bar(::Baz)?)?
	private static function const_regexp($camel_cased_word){ #:nodoc:
		$parts = explode('::', $camel_cased_word);
		$last  = array_pop($parts);
		$parts = array_reverse($parts);
		
		return array_reduce($parts, function($acc, $part){
			return empty($part) ? $acc : "{$part}(::{$acc})?";
		}, $last);
	}
	
	
	# transliterate.rb
		
	# Replaces non-ASCII characters with an ASCII approximation, or if none
	# exists, a replacement character which defaults to "?".
	#
	#    transliterate("Ærøskøbing")
	#    # => "AEroskobing"
	#
	# Default approximations are provided for Western/Latin characters,
	# e.g, "ø", "ñ", "é", "ß", etc.
	#
	# This method is I18n aware, so you can set up custom approximations for a
	# locale. This can be useful, for example, to transliterate German's "ü"
	# and "ö" to "ue" and "oe", or to add support for transliterating Russian
	# to ASCII.
	#
	# In order to make your custom transliterations available, you must set
	# them as the <tt>i18n.transliterate.rule</tt> i18n key:
	#
	#   # Store the transliterations in locales/de.yml
	#   i18n:
	#     transliterate:
	#       rule:
	#         ü: "ue"
	#         ö: "oe"
	#
	#   # Or set them using Ruby
	#   I18n.backend.store_translations(:de, :i18n => {
	#     :transliterate => {
	#       :rule => {
	#         "ü" => "ue",
	#         "ö" => "oe"
	#       }
	#     }
	#   })
	#
	# The value for <tt>i18n.transliterate.rule</tt> can be a simple Hash that maps
	# characters to ASCII approximations as shown above, or, for more complex
	# requirements, a Proc:
	#
	#   I18n.backend.store_translations(:de, :i18n => {
	#     :transliterate => {
	#       :rule => lambda {|string| MyTransliterator.transliterate(string)}
	#     }
	#   })
	#
	# Now you can have different transliterations for each locale:
	#
	#   I18n.locale = :en
	#   transliterate("Jürgen")
	#   # => "Jurgen"
	#
	#   I18n.locale = :de
	#   transliterate("Jürgen")
	#   # => "Juergen"
	public static function transliterate($string, $replacement = "?"){
		return \I18n\I18n::transliterate(
			\ActiveSupport\Multibyte\Unicode::normalize(\ActiveSupport\Multibyte\Unicode::tidy_bytes($string), 'c'),
			array('replacement' => $replacement)
		);
	}

	# Replaces special characters in a string so that it may be used as part of a 'pretty' URL.
	#
	# ==== Examples
	#
	#   class Person
	#     def to_param
	#       "#{id}-#{name.parameterize}"
	#     end
	#   end
	#
	#   @person = Person.find(1)
	#   # => #<Person id: 1, name: "Donald E. Knuth">
	#
	#   <%= link_to(@person.name, person_path(@person)) %>
	#   # => <a href="/person/1-donald-e-knuth">Donald E. Knuth</a>
	public static function parameterize($string, $sep = '-'){
		# replace accented chars with their ascii equivalents
		$parameterized_string = self::transliterate($string);
		# Turn unwanted chars into the separator
		$parameterized_string = preg_replace('/[^a-z0-9\-_]+/i', $sep, $parameterized_string);
		if(!(is_null($sep) || empty($sep))){
			$re_sep = preg_quote($sep); # CoreExt\Regexp::escape
			# No more than one of the separator in a row.
			$parameterized_string = preg_replace("/{$re_sep}{2,}/", $sep, $parameterized_string);
			# Remove leading/trailing separator.
			$parameterized_string = preg_replace("/^{$re_sep}|{$re_sep}$/i", '', $parameterized_string);
		}
		return strtolower($parameterized_string);
	}
	

	# Yields a singleton instance of Inflector::Inflections so you can specify additional
	# inflector rules.
	#
	# Example:
	#   ActiveSupport::Inflector.inflections do |inflect|
	#     inflect.uncountable "rails"
	#   end
	public static function inflections($block = false){
		if($block){
			return call_user_func($block, Inflector\Inflections::instance());
		}else{
			return Inflector\Inflections::instance();
		}
	}
	
	# Applies inflection rules for +singularize+ and +pluralize+.
	#
	# Examples:
	#  apply_inflections("post", inflections.plurals) # => "posts"
	#  apply_inflections("posts", inflections.singulars) # => "post"
	private static function apply_inflections($word, $rules){
		$result = $word;
		preg_match('/\b\w+\Z/', strtolower($result), $matches);
		if( empty($word) || array_search($matches[0], self::inflections()->uncountables) ){
			return $result;
		}else{
			foreach($rules as $rule_replacement){
				list($rule, $replacement) = $rule_replacement;
				$result = preg_replace($rule, $replacement, $result, -1, $count);
				if($count){
					 break;
				}
			}
			return $result;
		}
	}
}

namespace ActiveSupport\Inflector;

# A singleton instance of this class is yielded by Inflector.inflections, which can then be used to specify additional
# inflection rules. Examples:
#
#   ActiveSupport::Inflector.inflections do |inflect|
#     inflect.plural /^(ox)$/i, '\1\2en'
#     inflect.singular /^(ox)en/i, '\1'
#
#     inflect.irregular 'octopus', 'octopi'
#
#     inflect.uncountable "equipment"
#   end
#
# New rules are added at the top. So in the example above, the irregular rule for octopus will now be the first of the
# pluralization and singularization rules that is runs. This guarantees that your rules run before any of the rules that may
# already have been loaded.
class Inflections{
	
	protected static $_instance = null;
	
	public static function instance(){
		if(is_null(self::$_instance)){
			self::$_instance = new Inflections();
		}
		return self::$_instance;
	}

	#attr_reader :plurals, :singulars, :uncountables, :humans, :acronyms, :acronym_regex

	public function __construct(){
		$this->plurals = array();
		$this->singulars = array();
		$this->uncountables = array();
		$this->humans = array();

		$this->acronyms = array();
		$this->acronym_regex = '(?=a)b';
	}

	# Specifies a new acronym. An acronym must be specified as it will appear in a camelized string.  An underscore
	# string that contains the acronym will retain the acronym when passed to `camelize`, `humanize`, or `titleize`.
	# A camelized string that contains the acronym will maintain the acronym when titleized or humanized, and will
	# convert the acronym into a non-delimited single lowercase word when passed to +underscore+.
	#
	# Examples:
	#   acronym 'HTML'
	#   titleize 'html' #=> 'HTML'
	#   camelize 'html' #=> 'HTML'
	#   underscore 'MyHTML' #=> 'my_html'
	#
	# The acronym, however, must occur as a delimited unit and not be part of another word for conversions to recognize it:
	#
	#   acronym 'HTTP'
	#   camelize 'my_http_delimited' #=> 'MyHTTPDelimited'
	#   camelize 'https' #=> 'Https', not 'HTTPs'
	#   underscore 'HTTPS' #=> 'http_s', not 'https'
	#
	#   acronym 'HTTPS'
	#   camelize 'https' #=> 'HTTPS'
	#   underscore 'HTTPS' #=> 'https'
	#
	# Note: Acronyms that are passed to `pluralize` will no longer be recognized, since the acronym will not occur as
	# a delimited unit in the pluralized result. To work around this, you must specify the pluralized form as an
	# acronym as well:
	#
	#    acronym 'API'
	#    camelize(pluralize('api')) #=> 'Apis'
	#
	#    acronym 'APIs'
	#    camelize(pluralize('api')) #=> 'APIs'
	#
	# `acronym` may be used to specify any word that contains an acronym or otherwise needs to maintain a non-standard
	# capitalization. The only restriction is that the word must begin with a capital letter.
	#
	# Examples:
	#   acronym 'RESTful'
	#   underscore 'RESTful' #=> 'restful'
	#   underscore 'RESTfulController' #=> 'restful_controller'
	#   titleize 'RESTfulController' #=> 'RESTful Controller'
	#   camelize 'restful' #=> 'RESTful'
	#   camelize 'restful_controller' #=> 'RESTfulController'
	#
	#   acronym 'McDonald'
	#   underscore 'McDonald' #=> 'mcdonald'
	#   camelize 'mcdonald' #=> 'McDonald'
	public function acronym($word){
		$this->acronyms[$word->downcase()] = $word;
		$this->acronym_regex = implode('|', $this->acronyms);
	}

	# Specifies a new pluralization rule and its replacement. The rule can either be a string or a regular expression.
	# The replacement should always be a string that may include references to the matched data from the rule.
	public function plural($rule, $replacement){
		if(is_string($rule)){
			\PHPRails\array_delete($rule, $this->uncountables);
		}
		\PHPRails\array_delete($replacement, $this->uncountables);
		array_unshift($this->plurals, array($rule, $replacement));
	}

	# Specifies a new singularization rule and its replacement. The rule can either be a string or a regular expression.
	# The replacement should always be a string that may include references to the matched data from the rule.
	public function singular($rule, $replacement){
		if(is_string($rule)){
			\PHPRails\array_delete($rule, $this->uncountables);
		}
		\PHPRails\array_delete($replacement, $this->uncountables);
		array_unshift($this->singulars, array($rule, $replacement));
	}

	# Specifies a new irregular that applies to both pluralization and singularization at the same time. This can only be used
	# for strings, not regular expressions. You simply pass the irregular in singular and plural form.
	#
	# Examples:
	#   irregular 'octopus', 'octopi'
	#   irregular 'person', 'people'
	public function irregular($singular, $plural){
		\PHPRails\array_delete($singular, $this->uncountables);
		\PHPRails\array_delete($plural, $this->uncountables);
		
		$singular_char = substr($singular, 0, 1);
		$singular_char_upcase = strtoupper(substr($singular, 0, 1));
		$singular_char_downcase = strtolower(substr($singular, 0, 1));
		$singular_word = substr($singular, 1);
		
		$plural_char = substr($plural, 0, 1);
		$plural_char_upcase = strtoupper(substr($plural, 0, 1));
		$plural_char_downcase = strtolower(substr($plural, 0, 1));
		$plural_word = substr($plural, 1);
		
		
		if(strtoupper(substr($singular, 0, 1)) == strtoupper(substr($plural, 0, 1))){
			$this->plural("/({$singular_char}){$singular_word}$/i", '$1' . $plural_word);
			$this->plural("/({$plural_char}){$plural_word}$/i", '$1' . $plural_word);
			$this->singular("/({$plural_char}){$plural_word}$/i", '$1' . $singular_word);
		}else{
			$this->plural("/{$singular_char_upcase}(?i){$singular_word}$/", $plural_char_upcase . $plural_word);
			$this->plural("/{$singular_char_downcase}(?i){$singular_word}$/", $plural_char_downcase . $plural_word);
			$this->plural("/{$plural_char_upcase}(?i){$plural_word}$/", $plural_char_upcase . $plural_word);
			$this->plural("/{$plural_char_downcase}(?i){$plural_word}$/", $plural_char_downcase . $plural_word);
			$this->singular("/{$plural_char_upcase}(?i){$plural_word}$/", $singular_char_upcase + $singular_word);
			$this->singular("/{$plural_char_downcase}(?i){$plural_word}$/", $singular_char_downcase + $singular_word);
		}
	}

	# Add uncountable words that shouldn't be attempted inflected.
	#
	# Examples:
	#   uncountable "money"
	#   uncountable "money", "information"
	#   uncountable %w( money information rice )
	public function uncountable(/* *$words */){
		$words = func_get_args();
		array_push($this->uncountables, $words);
		$this->uncountables = \PHPRails\array_flatten($this->uncountables);
		return $this->uncountables;
	}

	# Specifies a humanized form of a string by a regular expression rule or by a string mapping.
	# When using a regular expression based replacement, the normal humanize formatting is called after the replacement.
	# When a string is used, the human form should be specified as desired (example: 'The name', not 'the_name')
	#
	# Examples:
	#   human /_cnt$/i, '\1_count'
	#   human "legacy_col_person_name", "Name"
	public function human($rule, $replacement){
		array_unshift($this->humans, array($rule, $replacement));
		return $this->humans;
	}

	# Clears the loaded inflections within a given scope (default is <tt>:all</tt>).
	# Give the scope as a symbol of the inflection type, the options are: <tt>:plurals</tt>,
	# <tt>:singulars</tt>, <tt>:uncountables</tt>, <tt>:humans</tt>.
	#
	# Examples:
	#   clear :all
	#   clear :plurals
	public function clear($scope = 'all'){
		switch($scope){
			case 'all':
				$this->plurals = array();
				$this->singulars = array();
				$this->uncountables = array();
				$this->humans = array();
				break;
			default:
				$this->${$scope} = array();
		}
	}
}

