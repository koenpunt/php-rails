<?php
/**
 * FormTagHelper
 * 
 * Rails source: https://github.com/rails/rails/blob/master/actionpack/lib/action_view/helpers/date_helper.rb
 * @package PHP Rails
 * @author Koen Punt
 */

#require 'date'
#require 'action_view/helpers/tag_helper'
#require 'active_support/core_ext/date/conversions'
#require 'active_support/core_ext/hash/slice'
#require 'active_support/core_ext/object/with_options'

namespace ActionView\Helpers;
# = Action View Date Helpers
#
# The Date Helper primarily creates select/option tags for different kinds of dates and times or date and time
# elements. All of the select-type methods share a number of common options that are as follows:
#
# * <tt>:prefix</tt> - overwrites the default prefix of "date" used for the select names. So specifying "birthday"
# would give \birthday[month] instead of \date[month] if passed to the <tt>select_month</tt> method.
# * <tt>:include_blank</tt> - set to true if it should be possible to set an empty date.
# * <tt>:discard_type</tt> - set to true if you want to discard the type part of the select name. If set to true,
#   the <tt>select_month</tt> method would use simply "date" (which can be overwritten using <tt>:prefix</tt>) instead
#   of \date[month].
class DateHelper{
	# Reports the approximate distance in time between two Time or Date objects or integers as seconds.
	# Set <tt>include_seconds</tt> to true if you want more detailed approximations when distance < 1 min, 29 secs
	# Distances are reported based on the following table:
	#
	#   0 <-> 29 secs                                                             # => less than a minute
	#   30 secs <-> 1 min, 29 secs                                                # => 1 minute
	#   1 min, 30 secs <-> 44 mins, 29 secs                                       # => [2..44] minutes
	#   44 mins, 30 secs <-> 89 mins, 29 secs                                     # => about 1 hour
	#   89 mins, 29 secs <-> 23 hrs, 59 mins, 29 secs                             # => about [2..24] hours
	#   23 hrs, 59 mins, 29 secs <-> 47 hrs, 59 mins, 29 secs                     # => 1 day
	#   47 hrs, 59 mins, 29 secs <-> 29 days, 23 hrs, 59 mins, 29 secs            # => [2..29] days
	#   29 days, 23 hrs, 59 mins, 30 secs <-> 59 days, 23 hrs, 59 mins, 29 secs   # => about 1 month
	#   59 days, 23 hrs, 59 mins, 30 secs <-> 1 yr minus 1 sec                    # => [2..12] months
	#   1 yr <-> 1 yr, 3 months                                                   # => about 1 year
	#   1 yr, 3 months <-> 1 yr, 9 months                                         # => over 1 year
	#   1 yr, 9 months <-> 2 yr minus 1 sec                                       # => almost 2 years
	#   2 yrs <-> max time or date                                                # => (same rules as 1 yr)
	#
	# With <tt>include_seconds</tt> = true and the difference < 1 minute 29 seconds:
	#   0-4   secs      # => less than 5 seconds
	#   5-9   secs      # => less than 10 seconds
	#   10-19 secs      # => less than 20 seconds
	#   20-39 secs      # => half a minute
	#   40-59 secs      # => less than a minute
	#   60-89 secs      # => 1 minute
	#
	# ==== Examples
	#   from_time = Time.now
	#   distance_of_time_in_words(from_time, from_time + 50.minutes)        # => about 1 hour
	#   distance_of_time_in_words(from_time, 50.minutes.from_now)           # => about 1 hour
	#   distance_of_time_in_words(from_time, from_time + 15.seconds)        # => less than a minute
	#   distance_of_time_in_words(from_time, from_time + 15.seconds, true)  # => less than 20 seconds
	#   distance_of_time_in_words(from_time, 3.years.from_now)              # => about 3 years
	#   distance_of_time_in_words(from_time, from_time + 60.hours)          # => about 3 days
	#   distance_of_time_in_words(from_time, from_time + 45.seconds, true)  # => less than a minute
	#   distance_of_time_in_words(from_time, from_time - 45.seconds, true)  # => less than a minute
	#   distance_of_time_in_words(from_time, 76.seconds.from_now)           # => 1 minute
	#   distance_of_time_in_words(from_time, from_time + 1.year + 3.days)   # => about 1 year
	#   distance_of_time_in_words(from_time, from_time + 3.years + 6.months) # => over 3 years
	#   distance_of_time_in_words(from_time, from_time + 4.years + 9.days + 30.minutes + 5.seconds) # => about 4 years
	#
	#   to_time = Time.now + 6.years + 19.days
	#   distance_of_time_in_words(from_time, to_time, true)     # => about 6 years
	#   distance_of_time_in_words(to_time, from_time, true)     # => about 6 years
	#   distance_of_time_in_words(Time.now, Time.now)           # => less than a minute
	#
	/*
    def distance_of_time_in_words(from_time, to_time = 0, include_seconds_or_options = {}, options = {})
      if include_seconds_or_options.is_a?(Hash)
        options = include_seconds_or_options
      else
        ActiveSupport::Deprecation.warn "distance_of_time_in_words and time_ago_in_words now accept :include_seconds " +
                                        "as a part of options hash, not a boolean argument", caller
        options[:include_seconds] ||= !!include_seconds_or_options
      end

      from_time = from_time.to_time if from_time.respond_to?(:to_time)
      to_time = to_time.to_time if to_time.respond_to?(:to_time)
      from_time, to_time = to_time, from_time if from_time > to_time
      distance_in_minutes = ((to_time - from_time)/60.0).round
      distance_in_seconds = (to_time - from_time).round

      I18n.with_options :locale => options[:locale], :scope => :'datetime.distance_in_words' do |locale|
        case distance_in_minutes
          when 0..1
            return distance_in_minutes == 0 ?
                   locale.t(:less_than_x_minutes, :count => 1) :
                   locale.t(:x_minutes, :count => distance_in_minutes) unless options[:include_seconds]

            case distance_in_seconds
              when 0..4   then locale.t :less_than_x_seconds, :count => 5
              when 5..9   then locale.t :less_than_x_seconds, :count => 10
              when 10..19 then locale.t :less_than_x_seconds, :count => 20
              when 20..39 then locale.t :half_a_minute
              when 40..59 then locale.t :less_than_x_minutes, :count => 1
              else             locale.t :x_minutes,           :count => 1
            end

          when 2...45           then locale.t :x_minutes,      :count => distance_in_minutes
          when 45...90          then locale.t :about_x_hours,  :count => 1
          # 90 mins up to 24 hours
          when 90...1440        then locale.t :about_x_hours,  :count => (distance_in_minutes.to_f / 60.0).round
          # 24 hours up to 42 hours
          when 1440...2520      then locale.t :x_days,         :count => 1
          # 42 hours up to 30 days
          when 2520...43200     then locale.t :x_days,         :count => (distance_in_minutes.to_f / 1440.0).round
          # 30 days up to 60 days
          when 43200...86400    then locale.t :about_x_months, :count => (distance_in_minutes.to_f / 43200.0).round
          # 60 days up to 365 days
          when 86400...525600   then locale.t :x_months,       :count => (distance_in_minutes.to_f / 43200.0).round
          else
            if from_time.acts_like?(:time) && to_time.acts_like?(:time)
              fyear = from_time.year
              fyear += 1 if from_time.month >= 3
              tyear = to_time.year
              tyear -= 1 if to_time.month < 3
              leap_years = (fyear > tyear) ? 0 : (fyear..tyear).count{|x| Date.leap?(x)}
              minute_offset_for_leap_year = leap_years * 1440
              # Discount the leap year days when calculating year distance.
              # e.g. if there are 20 leap year days between 2 dates having the same day
              # and month then the based on 365 days calculation
              # the distance in years will come out to over 80 years when in written
              # english it would read better as about 80 years.
              minutes_with_offset = distance_in_minutes - minute_offset_for_leap_year
            else
              minutes_with_offset = distance_in_minutes
            end
            remainder                   = (minutes_with_offset % 525600)
            distance_in_years           = (minutes_with_offset / 525600)
            if remainder < 131400
              locale.t(:about_x_years,  :count => distance_in_years)
            elsif remainder < 394200
              locale.t(:over_x_years,   :count => distance_in_years)
            else
              locale.t(:almost_x_years, :count => distance_in_years + 1)
            end
        end
      end
    end
		
	*/
	public static function distance_of_time_in_words($from_time, $to_time = 0, $include_seconds = false){
		/*
			TODO Reimplement this method with source from above
		*/
		$to_time = is_null($to_time) ? time() : $to_time;
	
		$distance_in_minutes = floor(abs($to_time - $from_time) / 60);
		$distance_in_seconds = floor(abs($to_time - $from_time));
	
		$string = '';
		$parameters = array();
	
		if ($distance_in_minutes <= 1){
			if (!$include_seconds){
				$string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
			}else{
				if ($distance_in_seconds <= 5){
					$string = 'less than 5 seconds';
				}else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10){
					$string = 'less than 10 seconds';
				}else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20){
				  $string = 'less than 20 seconds';
				}else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40){
				  $string = 'half a minute';
				}else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59){
					$string = 'less than a minute';
				}else{
					$string = '1 minute';
				}
			}
		}else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44){
			$string = '%minutes% minutes';
			$parameters['%minutes%'] = $distance_in_minutes;
		}else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89){
			$string = 'about 1 hour';
		}else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439){
			$string = 'about %hours% hours';
			$parameters['%hours%'] = round($distance_in_minutes / 60);
		}else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879){
			$string = '1 day';
		}else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199){
			$string = '%days% days';
			$parameters['%days%'] = round($distance_in_minutes / 1440);
		}else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399){
			$string = 'about 1 month';
		}else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959){
			$string = '%months% months';
			$parameters['%months%'] = round($distance_in_minutes / 43200);
		}else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919){
			$string = 'about 1 year';
		}else{
			$string = 'over %years% years';
			$parameters['%years%'] = floor($distance_in_minutes / 525960);
		}
	
		return strtr($string, $parameters);
	}
	
	
	public static function precise_time_ago_in_words($tsmp){
		$diffu = array(  'seconds'=>2, 'minutes' => 120, 'hours' => 7200, 'days' => 172800, 'months' => 5259487,  'years' =>  63113851 );
		$diff = time() - strtotime($tsmp);
		$dt = '0 seconds ago';
		foreach($diffu as $u => $n){ if($diff>$n) {$dt = floor($diff/(.5*$n)).' '.$u.' ago';} }

		return $dt;
	}

	# Like <tt>distance_of_time_in_words</tt>, but where <tt>to_time</tt> is fixed to <tt>Time.now</tt>.
	#
	# ==== Examples
	#   time_ago_in_words(3.minutes.from_now)                 # => 3 minutes
	#   time_ago_in_words(Time.now - 15.hours)                # => about 15 hours
	#   time_ago_in_words(Time.now)                           # => less than a minute
	#   time_ago_in_words(Time.now, :include_seconds => true) # => less than 5 seconds
	#
	#   from_time = Time.now - 3.days - 14.minutes - 25.seconds
	#   time_ago_in_words(from_time)      # => 3 days
	#
	public static function time_ago_in_words($from_time, $include_seconds_or_options = array()){
		#$from_time = strtotime($from);
		return self::distance_of_time_in_words($from_time, time(), $include_seconds_or_options);
	}
	
	# Alias of 'time_ago_in_words'
	public static function distance_of_time_in_words_to_now($from_time, $include_seconds_or_options = array()){
		return self::time_ago_in_words($from_time, $include_seconds_or_options);
	}
	
	# Returns a set of select tags (one for year, month, and day) pre-selected for accessing a specified date-based
	# attribute (identified by +method+) on an object assigned to the template (identified by +object+).
	#
	#
	# ==== Options
	# * <tt>:use_month_numbers</tt> - Set to true if you want to use month numbers rather than month names (e.g.
	#   "2" instead of "February").
	# * <tt>:use_two_digit_numbers</tt> - Set to true if you want to display two digit month and day numbers (e.g.
	#   "02" instead of "February" and "08" instead of "8").
	# * <tt>:use_short_month</tt>   - Set to true if you want to use abbreviated month names instead of full
	#   month names (e.g. "Feb" instead of "February").
	# * <tt>:add_month_numbers</tt>  - Set to true if you want to use both month numbers and month names (e.g.
	#   "2 - February" instead of "February").
	# * <tt>:use_month_names</tt>   - Set to an array with 12 month names if you want to customize month names.
	#   Note: You can also use Rails' i18n functionality for this.
	# * <tt>:date_separator</tt>    - Specifies a string to separate the date fields. Default is "" (i.e. nothing).
	# * <tt>:start_year</tt>        - Set the start year for the year select. Default is <tt>Time.now.year - 5</tt>.
	# * <tt>:end_year</tt>          - Set the end year for the year select. Default is <tt>Time.now.year + 5</tt>.
	# * <tt>:discard_day</tt>       - Set to true if you don't want to show a day select. This includes the day
	#   as a hidden field instead of showing a select field. Also note that this implicitly sets the day to be the
	#   first of the given month in order to not create invalid dates like 31 February.
	# * <tt>:discard_month</tt>     - Set to true if you don't want to show a month select. This includes the month
	#   as a hidden field instead of showing a select field. Also note that this implicitly sets :discard_day to true.
	# * <tt>:discard_year</tt>      - Set to true if you don't want to show a year select. This includes the year
	#   as a hidden field instead of showing a select field.
	# * <tt>:order</tt>             - Set to an array containing <tt>:day</tt>, <tt>:month</tt> and <tt>:year</tt> to
	#   customize the order in which the select fields are shown. If you leave out any of the symbols, the respective
	#   select will not be shown (like when you set <tt>:discard_xxx => true</tt>. Defaults to the order defined in
	#   the respective locale (e.g. [:year, :month, :day] in the en locale that ships with Rails).
	# * <tt>:include_blank</tt>     - Include a blank option in every select field so it's possible to set empty
	#   dates.
	# * <tt>:default</tt>           - Set a default date if the affected date isn't set or is nil.
	# * <tt>:disabled</tt>          - Set to true if you want show the select fields as disabled.
	# * <tt>:prompt</tt>            - Set to true (for a generic prompt), a prompt string or a hash of prompt strings
	#   for <tt>:year</tt>, <tt>:month</tt>, <tt>:day</tt>, <tt>:hour</tt>, <tt>:minute</tt> and <tt>:second</tt>.
	#   Setting this option prepends a select option with a generic prompt  (Day, Month, Year, Hour, Minute, Seconds)
	#   or the given prompt string.
	#
	# If anything is passed in the +html_options+ hash it will be applied to every select tag in the set.
	#
	# NOTE: Discarded selects will default to 1. So if no month select is available, January will be assumed.
	#
	# ==== Examples
	#   # Generates a date select that when POSTed is stored in the article variable, in the written_on attribute.
	#   date_select("article", "written_on")
	#
	#   # Generates a date select that when POSTed is stored in the article variable, in the written_on attribute,
	#   # with the year in the year drop down box starting at 1995.
	#   date_select("article", "written_on", :start_year => 1995)
	#
	#   # Generates a date select that when POSTed is stored in the article variable, in the written_on attribute,
	#   # with the year in the year drop down box starting at 1995, numbers used for months instead of words,
	#   # and without a day select box.
	#   date_select("article", "written_on", :start_year => 1995, :use_month_numbers => true,
	#                                     :discard_day => true, :include_blank => true)
	#
	#   # Generates a date select that when POSTed is stored in the article variable, in the written_on attribute,
	#   # with two digit numbers used for months and days.
	#   date_select("article", "written_on", :use_two_digit_numbers => true)
	#
	#   # Generates a date select that when POSTed is stored in the article variable, in the written_on attribute
	#   # with the fields ordered as day, month, year rather than month, day, year.
	#   date_select("article", "written_on", :order => [:day, :month, :year])
	#
	#   # Generates a date select that when POSTed is stored in the user variable, in the birthday attribute
	#   # lacking a year field.
	#   date_select("user", "birthday", :order => [:month, :day])
	#
	#   # Generates a date select that when POSTed is stored in the article variable, in the written_on attribute
	#   # which is initially set to the date 3 days from the current date
	#   date_select("article", "written_on", :default => 3.days.from_now)
	#
	#   # Generates a date select that when POSTed is stored in the credit_card variable, in the bill_due attribute
	#   # that will have a default day of 20.
	#   date_select("credit_card", "bill_due", :default => { :day => 20 })
	#
	#   # Generates a date select with custom prompts.
	#   date_select("article", "written_on", :prompt => { :day => 'Select day', :month => 'Select month', :year => 'Select year' })
	#
	# The selects are prepared for multi-parameter assignment to an Active Record object.
	#
	# Note: If the day is not included as an option but the month is, the day will be set to the 1st to ensure that
	# all month choices are valid.
	public static function date_select($object_name, $method, $options = array(), $html_options = array()){
		$date_select = new Tags\DateSelect($object_name, $method, self, $options, $html_options);
		return $date_select->render();
	}

	# Returns a set of select tags (one for hour, minute and optionally second) pre-selected for accessing a
	# specified time-based attribute (identified by +method+) on an object assigned to the template (identified by
	# +object+). You can include the seconds with <tt>:include_seconds</tt>. You can get hours in the AM/PM format
	# with <tt>:ampm</tt> option.
	#
	# This method will also generate 3 input hidden tags, for the actual year, month and day unless the option
	# <tt>:ignore_date</tt> is set to +true+. If you set the <tt>:ignore_date</tt> to +true+, you must have a
	# +date_select+ on the same method within the form otherwise an exception will be raised.
	#
	# If anything is passed in the html_options hash it will be applied to every select tag in the set.
	#
	# ==== Examples
	#   # Creates a time select tag that, when POSTed, will be stored in the article variable in the sunrise attribute.
	#   time_select("article", "sunrise")
	#
	#   # Creates a time select tag with a seconds field that, when POSTed, will be stored in the article variables in
	#   # the sunrise attribute.
	#   time_select("article", "start_time", :include_seconds => true)
	#
	#   # You can set the <tt>:minute_step</tt> to 15 which will give you: 00, 15, 30 and 45.
	#   time_select 'game', 'game_time', {:minute_step => 15}
	#
	#   # Creates a time select tag with a custom prompt. Use <tt>:prompt => true</tt> for generic prompts.
	#   time_select("article", "written_on", :prompt => {:hour => 'Choose hour', :minute => 'Choose minute', :second => 'Choose seconds'})
	#   time_select("article", "written_on", :prompt => {:hour => true}) # generic prompt for hours
	#   time_select("article", "written_on", :prompt => true) # generic prompts for all
	#
	#   # You can set :ampm option to true which will show the hours as: 12 PM, 01 AM .. 11 PM.
	#   time_select 'game', 'game_time', {:ampm => true}
	#
	# The selects are prepared for multi-parameter assignment to an Active Record object.
	#
	# Note: If the day is not included as an option but the month is, the day will be set to the 1st to ensure that
	# all month choices are valid.
	public static function time_select($object_name, $method, $options = array(), $html_options = array()){
		$time_select = new Tags\TimeSelect($object_name, $method, self, $options, $html_options);
		return $time_select->render();
	}

	# Returns a set of select tags (one for year, month, day, hour, and minute) pre-selected for accessing a
	# specified datetime-based attribute (identified by +method+) on an object assigned to the template (identified
	# by +object+).
	#
	# If anything is passed in the html_options hash it will be applied to every select tag in the set.
	#
	# ==== Examples
	#   # Generates a datetime select that, when POSTed, will be stored in the article variable in the written_on
	#   # attribute.
	#   datetime_select("article", "written_on")
	#
	#   # Generates a datetime select with a year select that starts at 1995 that, when POSTed, will be stored in the
	#   # article variable in the written_on attribute.
	#   datetime_select("article", "written_on", :start_year => 1995)
	#
	#   # Generates a datetime select with a default value of 3 days from the current time that, when POSTed, will
	#   # be stored in the trip variable in the departing attribute.
	#   datetime_select("trip", "departing", :default => 3.days.from_now)
	#
	#   # Generate a datetime select with hours in the AM/PM format
	#   datetime_select("article", "written_on", :ampm => true)
	#
	#   # Generates a datetime select that discards the type that, when POSTed, will be stored in the article variable
	#   # as the written_on attribute.
	#   datetime_select("article", "written_on", :discard_type => true)
	#
	#   # Generates a datetime select with a custom prompt. Use <tt>:prompt => true</tt> for generic prompts.
	#   datetime_select("article", "written_on", :prompt => {:day => 'Choose day', :month => 'Choose month', :year => 'Choose year'})
	#   datetime_select("article", "written_on", :prompt => {:hour => true}) # generic prompt for hours
	#   datetime_select("article", "written_on", :prompt => true) # generic prompts for all
	#
	# The selects are prepared for multi-parameter assignment to an Active Record object.
	public static function datetime_select($object_name, $method, $options = array(), $html_options = array()){
		$datetime_select = new Tags\DatetimeSelect($object_name, $method, self, $options, $html_options);
		return $datetime_select->render();
	}

	# Returns a set of html select-tags (one for year, month, day, hour, minute, and second) pre-selected with the
	# +datetime+. It's also possible to explicitly set the order of the tags using the <tt>:order</tt> option with
	# an array of symbols <tt>:year</tt>, <tt>:month</tt> and <tt>:day</tt> in the desired order. If you do not
	# supply a Symbol, it will be appended onto the <tt>:order</tt> passed in. You can also add
	# <tt>:date_separator</tt>, <tt>:datetime_separator</tt> and <tt>:time_separator</tt> keys to the +options+ to
	# control visual display of the elements.
	#
	# If anything is passed in the html_options hash it will be applied to every select tag in the set.
	#
	# ==== Examples
	#   my_date_time = Time.now + 4.days
	#
	#   # Generates a datetime select that defaults to the datetime in my_date_time (four days after today).
	#   select_datetime(my_date_time)
	#
	#   # Generates a datetime select that defaults to today (no specified datetime)
	#   select_datetime()
	#
	#   # Generates a datetime select that defaults to the datetime in my_date_time (four days after today)
	#   # with the fields ordered year, month, day rather than month, day, year.
	#   select_datetime(my_date_time, :order => [:year, :month, :day])
	#
	#   # Generates a datetime select that defaults to the datetime in my_date_time (four days after today)
	#   # with a '/' between each date field.
	#   select_datetime(my_date_time, :date_separator => '/')
	#
	#   # Generates a datetime select that defaults to the datetime in my_date_time (four days after today)
	#   # with a date fields separated by '/', time fields separated by '' and the date and time fields
	#   # separated by a comma (',').
	#   select_datetime(my_date_time, :date_separator => '/', :time_separator => '', :datetime_separator => ',')
	#
	#   # Generates a datetime select that discards the type of the field and defaults to the datetime in
	#   # my_date_time (four days after today)
	#   select_datetime(my_date_time, :discard_type => true)
	#
	#   # Generate a datetime field with hours in the AM/PM format
	#   select_datetime(my_date_time, :ampm => true)
	#
	#   # Generates a datetime select that defaults to the datetime in my_date_time (four days after today)
	#   # prefixed with 'payday' rather than 'date'
	#   select_datetime(my_date_time, :prefix => 'payday')
	#
	#   # Generates a datetime select with a custom prompt. Use <tt>:prompt => true</tt> for generic prompts.
	#   select_datetime(my_date_time, :prompt => {:day => 'Choose day', :month => 'Choose month', :year => 'Choose year'})
	#   select_datetime(my_date_time, :prompt => {:hour => true}) # generic prompt for hours
	#   select_datetime(my_date_time, :prompt => true) # generic prompts for all
	#
	public static function select_datetime($datetime = null, $options = array(), $html_options = array()){
		$datetime = $datetime ?: time();
		$datetime_select = new DateTimeSelector($datetime, $options, $html_options);
		return $datetime_select->select_datetime();
	}

	# Returns a set of html select-tags (one for year, month, and day) pre-selected with the +date+.
	# It's possible to explicitly set the order of the tags using the <tt>:order</tt> option with an array of
	# symbols <tt>:year</tt>, <tt>:month</tt> and <tt>:day</tt> in the desired order.
	# If the array passed to the <tt>:order</tt> option does not contain all the three symbols, all tags will be hidden.
	#
	# If anything is passed in the html_options hash it will be applied to every select tag in the set.
	#
	# ==== Examples
	#   my_date = Time.now + 6.days
	#
	#   # Generates a date select that defaults to the date in my_date (six days after today).
	#   select_date(my_date)
	#
	#   # Generates a date select that defaults to today (no specified date).
	#   select_date()
	#
	#   # Generates a date select that defaults to the date in my_date (six days after today)
	#   # with the fields ordered year, month, day rather than month, day, year.
	#   select_date(my_date, :order => [:year, :month, :day])
	#
	#   # Generates a date select that discards the type of the field and defaults to the date in
	#   # my_date (six days after today).
	#   select_date(my_date, :discard_type => true)
	#
	#   # Generates a date select that defaults to the date in my_date,
	#   # which has fields separated by '/'.
	#   select_date(my_date, :date_separator => '/')
	#
	#   # Generates a date select that defaults to the datetime in my_date (six days after today)
	#   # prefixed with 'payday' rather than 'date'.
	#   select_date(my_date, :prefix => 'payday')
	#
	#   # Generates a date select with a custom prompt. Use <tt>:prompt => true</tt> for generic prompts.
	#   select_date(my_date, :prompt => {:day => 'Choose day', :month => 'Choose month', :year => 'Choose year'})
	#   select_date(my_date, :prompt => {:hour => true}) # generic prompt for hours
	#   select_date(my_date, :prompt => true) # generic prompts for all
	#
	public static function select_date($date = null, $options = array(), $html_options = array()){
		$date = $date ?: time();
		$date_select = new DateTimeSelector($date, $options, $html_options);
		return $date_select->select_date();
	}

	# Returns a set of html select-tags (one for hour and minute).
	# You can set <tt>:time_separator</tt> key to format the output, and
	# the <tt>:include_seconds</tt> option to include an input for seconds.
	#
	# If anything is passed in the html_options hash it will be applied to every select tag in the set.
	#
	# ==== Examples
	#   my_time = Time.now + 5.days + 7.hours + 3.minutes + 14.seconds
	#
	#   # Generates a time select that defaults to the time in my_time.
	#   select_time(my_time)
	#
	#   # Generates a time select that defaults to the current time (no specified time).
	#   select_time()
	#
	#   # Generates a time select that defaults to the time in my_time,
	#   # which has fields separated by ':'.
	#   select_time(my_time, :time_separator => ':')
	#
	#   # Generates a time select that defaults to the time in my_time,
	#   # that also includes an input for seconds.
	#   select_time(my_time, :include_seconds => true)
	#
	#   # Generates a time select that defaults to the time in my_time, that has fields
	#   # separated by ':' and includes an input for seconds.
	#   select_time(my_time, :time_separator => ':', :include_seconds => true)
	#
	#   # Generate a time select field with hours in the AM/PM format
	#   select_time(my_time, :ampm => true)
	#
	#   # Generates a time select with a custom prompt. Use <tt>:prompt</tt> to true for generic prompts.
	#   select_time(my_time, :prompt => {:day => 'Choose day', :month => 'Choose month', :year => 'Choose year'})
	#   select_time(my_time, :prompt => {:hour => true}) # generic prompt for hours
	#   select_time(my_time, :prompt => true) # generic prompts for all
	#
	public static function select_time($datetime = null, $options = array(), $html_options = array()){
		$datetime = $datetime ?: time();
		$datetime_select = new DateTimeSelector($datetime, $options, $html_options);
		return $datetime_select->select_time();
	}
	
	# Returns a select tag with options for each of the seconds 0 through 59 with the current second selected.
	# The <tt>datetime</tt> can be either a +Time+ or +DateTime+ object or an integer.
	# Override the field name using the <tt>:field_name</tt> option, 'second' by default.
	#
	# ==== Examples
	#   my_time = Time.now + 16.minutes
	#
	#   # Generates a select field for seconds that defaults to the seconds for the time in my_time.
	#   select_second(my_time)
	#
	#   # Generates a select field for seconds that defaults to the number given.
	#   select_second(33)
	#
	#   # Generates a select field for seconds that defaults to the seconds for the time in my_time
	#   # that is named 'interval' rather than 'second'.
	#   select_second(my_time, :field_name => 'interval')
	#
	#   # Generates a select field for seconds with a custom prompt. Use <tt>:prompt => true</tt> for a
	#   # generic prompt.
	#   select_second(14, :prompt => 'Choose seconds')
	#
	public static function select_second($datetime, $options = array(), $html_options = array()){
		$datetime_select = new DateTimeSelector($datetime, $options, $html_options);
		return $datetime_select->select_second();
	}

	# Returns a select tag with options for each of the minutes 0 through 59 with the current minute selected.
	# Also can return a select tag with options by <tt>minute_step</tt> from 0 through 59 with the 00 minute
	# selected. The <tt>datetime</tt> can be either a +Time+ or +DateTime+ object or an integer.
	# Override the field name using the <tt>:field_name</tt> option, 'minute' by default.
	#
	# ==== Examples
	#   my_time = Time.now + 6.hours
	#
	#   # Generates a select field for minutes that defaults to the minutes for the time in my_time.
	#   select_minute(my_time)
	#
	#   # Generates a select field for minutes that defaults to the number given.
	#   select_minute(14)
	#
	#   # Generates a select field for minutes that defaults to the minutes for the time in my_time
	#   # that is named 'moment' rather than 'minute'.
	#   select_minute(my_time, :field_name => 'moment')
	#
	#   # Generates a select field for minutes with a custom prompt. Use <tt>:prompt => true</tt> for a
	#   # generic prompt.
	#   select_minute(14, :prompt => 'Choose minutes')
	#
	public static function select_minute($datetime, $options = array(), $html_options = array()){
		$datetime_select = new DateTimeSelector($datetime, $options, $html_options);
		return $datetime_select->select_minute();
	}

	# Returns a select tag with options for each of the hours 0 through 23 with the current hour selected.
	# The <tt>datetime</tt> can be either a +Time+ or +DateTime+ object or an integer.
	# Override the field name using the <tt>:field_name</tt> option, 'hour' by default.
	#
	# ==== Examples
	#   my_time = Time.now + 6.hours
	#
	#   # Generates a select field for hours that defaults to the hour for the time in my_time.
	#   select_hour(my_time)
	#
	#   # Generates a select field for hours that defaults to the number given.
	#   select_hour(13)
	#
	#   # Generates a select field for hours that defaults to the hour for the time in my_time
	#   # that is named 'stride' rather than 'hour'.
	#   select_hour(my_time, :field_name => 'stride')
	#
	#   # Generates a select field for hours with a custom prompt. Use <tt>:prompt => true</tt> for a
	#   # generic prompt.
	#   select_hour(13, :prompt => 'Choose hour')
	#
	#   # Generate a select field for hours in the AM/PM format
	#   select_hour(my_time, :ampm => true)
	#
	public static function select_hour($datetime, $options = array(), $html_options = array()){
		$datetime_select = new DateTimeSelector($datetime, $options, $html_options);
		return $datetime_select->select_hour();
	}

	# Returns a select tag with options for each of the days 1 through 31 with the current day selected.
	# The <tt>date</tt> can also be substituted for a day number.
	# If you want to display days with a leading zero set the <tt>:use_two_digit_numbers</tt> key in +options+ to true.
	# Override the field name using the <tt>:field_name</tt> option, 'day' by default.
	#
	# ==== Examples
	#   my_date = Time.now + 2.days
	#
	#   # Generates a select field for days that defaults to the day for the date in my_date.
	#   select_day(my_time)
	#
	#   # Generates a select field for days that defaults to the number given.
	#   select_day(5)
	#
	#   # Generates a select field for days that defaults to the number given, but displays it with two digits.
	#   select_day(5, :use_two_digit_numbers => true)
	#
	#   # Generates a select field for days that defaults to the day for the date in my_date
	#   # that is named 'due' rather than 'day'.
	#   select_day(my_time, :field_name => 'due')
	#
	#   # Generates a select field for days with a custom prompt. Use <tt>:prompt => true</tt> for a
	#   # generic prompt.
	#   select_day(5, :prompt => 'Choose day')
	#
	public static function select_day($date, $options = array(), $html_options = array()){
		$datetime_select = new DateTimeSelector($date, $options, $html_options);
		return $datetime_select->select_day();
	}
	
	# Returns a select tag with options for each of the months January through December with the current month
	# selected. The month names are presented as keys (what's shown to the user) and the month numbers (1-12) are
	# used as values (what's submitted to the server). It's also possible to use month numbers for the presentation
	# instead of names -- set the <tt>:use_month_numbers</tt> key in +options+ to true for this to happen. If you
	# want both numbers and names, set the <tt>:add_month_numbers</tt> key in +options+ to true. If you would prefer
	# to show month names as abbreviations, set the <tt>:use_short_month</tt> key in +options+ to true. If you want
	# to use your own month names, set the <tt>:use_month_names</tt> key in +options+ to an array of 12 month names.
	# If you want to display months with a leading zero set the <tt>:use_two_digit_numbers</tt> key in +options+ to true.
	# Override the field name using the <tt>:field_name</tt> option, 'month' by default.
	#
	# ==== Examples
	#   # Generates a select field for months that defaults to the current month that
	#   # will use keys like "January", "March".
	#   select_month(Date.today)
	#
	#   # Generates a select field for months that defaults to the current month that
	#   # is named "start" rather than "month".
	#   select_month(Date.today, :field_name => 'start')
	#
	#   # Generates a select field for months that defaults to the current month that
	#   # will use keys like "1", "3".
	#   select_month(Date.today, :use_month_numbers => true)
	#
	#   # Generates a select field for months that defaults to the current month that
	#   # will use keys like "1 - January", "3 - March".
	#   select_month(Date.today, :add_month_numbers => true)
	#
	#   # Generates a select field for months that defaults to the current month that
	#   # will use keys like "Jan", "Mar".
	#   select_month(Date.today, :use_short_month => true)
	#
	#   # Generates a select field for months that defaults to the current month that
	#   # will use keys like "Januar", "Marts."
	#   select_month(Date.today, :use_month_names => %w(Januar Februar Marts ...))
	#
	#   # Generates a select field for months that defaults to the current month that
	#   # will use keys with two digit numbers like "01", "03".
	#   select_month(Date.today, :use_two_digit_numbers => true)
	#
	#   # Generates a select field for months with a custom prompt. Use <tt>:prompt => true</tt> for a
	#   # generic prompt.
	#   select_month(14, :prompt => 'Choose month')
	#
	public static function select_month($date, $options = array(), $html_options = array()){
		$datetime_select = new DateTimeSelector($date, $options, $html_options);
		return $datetime_select->select_month();
	}

	# Returns a select tag with options for each of the five years on each side of the current, which is selected.
	# The five year radius can be changed using the <tt>:start_year</tt> and <tt>:end_year</tt> keys in the
	# +options+. Both ascending and descending year lists are supported by making <tt>:start_year</tt> less than or
	# greater than <tt>:end_year</tt>. The <tt>date</tt> can also be substituted for a year given as a number.
	# Override the field name using the <tt>:field_name</tt> option, 'year' by default.
	#
	# ==== Examples
	#   # Generates a select field for years that defaults to the current year that
	#   # has ascending year values.
	#   select_year(Date.today, :start_year => 1992, :end_year => 2007)
	#
	#   # Generates a select field for years that defaults to the current year that
	#   # is named 'birth' rather than 'year'.
	#   select_year(Date.today, :field_name => 'birth')
	#
	#   # Generates a select field for years that defaults to the current year that
	#   # has descending year values.
	#   select_year(Date.today, :start_year => 2005, :end_year => 1900)
	#
	#   # Generates a select field for years that defaults to the year 2006 that
	#   # has ascending year values.
	#   select_year(2006, :start_year => 2000, :end_year => 2010)
	#
	#   # Generates a select field for years with a custom prompt. Use <tt>:prompt => true</tt> for a
	#   # generic prompt.
	#   select_year(14, :prompt => 'Choose year')
	#
	public static function select_year($date, $options = array(), $html_options = array()){
		$datetime_select = new DateTimeSelector($date, $options, $html_options);
		return $datetime_select->select_year();
	}

	# Returns an html time tag for the given date or time.
	#
	# ==== Examples
	#   time_tag Date.today  # =>
	#     <time datetime="2010-11-04">November 04, 2010</time>
	#   time_tag Time.now  # =>
	#     <time datetime="2010-11-04T17:55:45+01:00">November 04, 2010 17:55</time>
	#   time_tag Date.yesterday, 'Yesterday'  # =>
	#     <time datetime="2010-11-03">Yesterday</time>
	#   time_tag Date.today, :pubdate => true  # =>
	#     <time datetime="2010-11-04" pubdate="pubdate">November 04, 2010</time>
	#
	#   <%= time_tag Time.now do %>
	#     <span>Right now</span>
	#   <% end %>
	#   # => <time datetime="2010-11-04T17:55:45+01:00"><span>Right now</span></time>
	#
	public static function time_tag($date_or_time/*, *args */){ //, &block)
		$args = func_get_args();
		$options = extract_options($args);
		$format = delete($options, 'format') ?: 'long';
		$content  = func_get_arg(0) ?: I18n::l($date_or_time, array('format' => $format));
		$datetime = $date_or_time;
		/*
			TODO Format date (xmlschema or rfc3339)
		*/
		#$datetime = $date_or_time.acts_like?(:time) ? date_or_time.xmlschema : date_or_time.rfc3339

		TagHelper::content_tag('time', $content, array_merge(array('datetime' => $datetime), $options)); //, &$block)
	}
}

class DateTimeSelector{ #:nodoc:
	#include ActionView::Helpers::TagHelper
	static $DEFAULT_PREFIX = 'date';
	static $POSITION = array(
		'year' => 1, 'month' => 2, 'day' => 3, 'hour' => 4, 'minute' => 5, 'second' => 6
	);

	static $AMPM_TRANSLATION = array(
		0 => "12 AM", 1 => "01 AM", 2 => "02 AM", 3 => "03 AM",
		4 => "04 AM", 5 => "05 AM", 6 => "06 AM", 7 => "07 AM",
		8 => "08 AM", 9 => "09 AM", 10 => "10 AM", 11 => "11 AM",
		12 => "12 PM", 13 => "01 PM", 14 => "02 PM", 15 => "03 PM",
		16 => "04 PM", 17 => "05 PM", 18 => "06 PM", 19 => "07 PM",
		20 => "08 PM", 21 => "09 PM", 22 => "10 PM", 23 => "11 PM"
	);
	
	public function __construct($datetime, $options = array(), $html_options = array()){
		$this->options      = $options; # .dup
		$this->html_options = $html_options; # .dup
		$this->datetime     = $datetime;
		$this->options['datetime_separator'] = $this->options['datetime_separator'] ?: ' &mdash; ';
		$this->options['time_separator'] = $this->options['time_separator'] ?: ' : ';
	}

	public function select_datetime(){
		$order = $this->date_order();
		$order = array_diff($order, array('hour', 'minute', 'second'));
		if(!in_array('year', $order)){
			$this->options['discard_year'] = $this->options['discard_year'] ?: true;
		}
		if(!in_array('month', $order)){
			$this->options['discard_month'] = $this->options['discard_month'] ?: true;
		}
		if($this->options['discard_month'] || !in_array('day', $order)){
			$this->options['discard_day'] = $this->options['discard_day'] ?: true;
		}
		if($this->options['discard_hour']){
			$this->options['discard_minute'] = $this->options['discard_minute'] ?: true;
		}
		if(!($this->options['include_seconds'] && !$this->options['discard_minute'])){
			$this->options['discard_second'] = $this->options['discard_second'] ?: true;
		}

		$this->set_day_if_discarded();

		if( $this->options['tag'] && $this->options['ignore_date']){
			return $this->select_time();
		}else{
			foreach(array('day', 'month', 'year') as $o){
				if(!in_array($o, $order)){
					array_unshift($order, $o);
				}
			}
			if(!$this->options['discard_hour']){
				$order += array('hour', 'minute', 'second');
			}
			
			return $this->build_selects_from_types($order);
		}
	}

	public function select_date(){
		$order = $this->date_order();

		$this->options['discard_hour']     = true;
		$this->options['discard_minute']   = true;
		$this->options['discard_second']   = true;

		if(!in_array('year', $order)){
			$this->options['discard_year'] = $this->options['discard_year'] ?: true;
		}
		if(!in_array('month', $order)){
			$this->options['discard_month'] = $this->options['discard_month'] ?: true;
		}
		if( $this->options['discard_month'] || !in_array('day', $order) ){
			$this->options['discard_day'] = $this->options['discard_day'] ?: true;
		}

		$this->set_day_if_discarded();
		
		foreach(array('day', 'month', 'year') as $o){
			if(!in_array($o, $order)){
				array_unshift($order, $o);
			}
		}
			
		return $this->build_selects_from_types($order);
			
			
	}

	public function select_time(){
		$order = array();
		
		$this->options['discard_month']    = true;
		$this->options['discard_year']     = true;
		$this->options['discard_day']      = true;
		if(!$this->options['include_seconds']){
			$this->options['discard_second'] = $this->options['discard_second'] ?: true;
		}
		
		if(!$this->options['ignore_date']){
			$order += array('year', 'month', 'day'); 
		}
		
		$order += array('hour', 'minute');
		if($this->options['include_seconds']){
			array_push($order, 'second');
		} 
		
		return $this->build_selects_from_types($order);
	}

	public function select_second(){
		if($this->options['use_hidden'] || $this->options['discard_second']){
			if($this->options['include_seconds']){
				return $this->build_hidden('second', $this->sec());
			}
		}else{
			return $this->build_options_and_select('second', $this->sec());
		}
	}

	public function select_minute(){
		if( $this->options['use_hidden'] || $this->options['discard_minute']){
			return $this->build_hidden('minute', $this->min());
		}else{
			return $this->build_options_and_select('minute', $this->min(), array('step' => $this->options['minute_step']));
		}
	}

	public function select_hour(){
		if( $this->options['use_hidden'] || $this->options['discard_hour']){
			return $this->build_hidden('hour', $this->hour());
		}else{
			return $this->build_options_and_select('hour', $this->hour(), array('end' => 23, 'ampm' => $this->options['ampm']));
		}
	}

	public function select_day(){
		if($this->options['use_hidden'] || $this->options['discard_day']){
			return $this->build_hidden('day', $this->day() ?: 1);
		}else{
			return $this->build_options_and_select('day', $this->day(), array('start' => 1, 'end' => 31, 'leading_zeros' => false, 'use_two_digit_numbers' => $this->options['use_two_digit_numbers']));
		}
	}

	public function select_month(){
		if($this->options['use_hidden'] || $this->options['discard_month']){
			return $this->build_hidden('month', $this->month() ?: 1);
		}else{
			$month_options = array();
			for($month_number = 1; $month_number < 12; $month_number ++){
				$options = array('value' => $month_number);
				if( $this->month() == $month_number){
					$options['selected'] = "selected";
				}
				array_push($month_options, $this->content_tag('option', $this->month_name($month_number), $options) . "\n");
			}
			return $this->build_select('month', implode('', $month_options));
		}
	}

	public function select_year(){
		if( !$this->datetime || $this->datetime == 0){
			$val = '1';
			$middle_year = date('Y'); // Date.today.year
		}else{
			$val = $middle_year = $this->year();
		}

		if( $this->options['use_hidden'] || $this->options['discard_year'] ){
			$this->build_hidden('year', $val);
		}else{
			$options                      = array();
			$options['start']             = $this->options['start_year'] ?: $middle_year - 5;
			$options['end']               = $this->options['end_year'] ?: $middle_year + 5;
			$options['step']              = $options['start'] < $options['end'] ? 1 : -1;
			$options['leading_zeros']     = false;
			$options['max_years_allowed'] = $this->options['max_years_allowed'] ?: 1000;

			if(abs($options['end'] - $options['start']) > $options['max_years_allowed']){
				throw new ArgumentError("There're too many years options to be built. Are you sure you haven't mistyped something? You can provide the :max_years_allowed parameter");
			}

			$this->build_options_and_select('year', $val, $options);
		}
	}

	private function sec(){
		if ($this->datetime){
			return is_numeric($this->datetime) ? $this->datetime : call_user_func(array($this->datetime, __FUNCTION__)); 
		}
	}
	
	private function min(){
		if ($this->datetime){
			return is_numeric($this->datetime) ? $this->datetime : call_user_func(array($this->datetime, __FUNCTION__)); 
		}
	}
	
	private function hour(){
		if ($this->datetime){
			return is_numeric($this->datetime) ? $this->datetime : call_user_func(array($this->datetime, __FUNCTION__)); 
		}
	}
	
	private function day(){
		if ($this->datetime){
			return is_numeric($this->datetime) ? $this->datetime : call_user_func(array($this->datetime, __FUNCTION__)); 
		}
	}
	
	private function month(){
		if ($this->datetime){
			return is_numeric($this->datetime) ? $this->datetime : call_user_func(array($this->datetime, __FUNCTION__)); 
		}
	}
	
	private function year(){
		if ($this->datetime){
			return is_numeric($this->datetime) ? $this->datetime : call_user_func(array($this->datetime, __FUNCTION__)); 
		}
	}
	
	# If the day is hidden, the day should be set to the 1st so all month and year choices are
	# valid. Otherwise, February 31st or February 29th, 2011 can be selected, which are invalid.
	private function set_day_if_discarded(){
		if( $this->datetime && $this->options['discard_day']){
			$this->datetime = $this->datetime->change(array('day' => 1));
		}
	}

	# Returns translated month names, but also ensures that a custom month
	# name array has a leading nil element.
	private function month_names(){
		$month_names = $this->options['use_month_names'] ?: $this->translated_month_names();
		if(count($month_names) < 13){
			array_unshift($month_names, null);
		}
		$this->month_names = $month_names;
		
		return $this->month_names;
	}

	# Returns translated month names.
	#  => [nil, "January", "February", "March",
	#           "April", "May", "June", "July",
	#           "August", "September", "October",
	#           "November", "December"]
	#
	# If <tt>:use_short_month</tt> option is set
	#  => [nil, "Jan", "Feb", "Mar", "Apr", "May", "Jun",
	#           "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
	private function translated_month_names(){
		$key = $this->options['use_short_month'] ? 'date.abbr_month_names' : 'date.month_names';
		return I18n::translate($key, array('locale' => $this->options['locale']));
	}
	
	# Lookup month name for number.
	#  month_name(1) => "January"
	#
	# If <tt>:use_month_numbers</tt> option is passed
	#  month_name(1) => 1
	#
	# If <tt>:use_two_month_numbers</tt> option is passed
	#  month_name(1) => '01'
	#
	# If <tt>:add_month_numbers</tt> option is passed
	#  month_name(1) => "1 - January"
	private function month_name($number){
		$month_names = $this->month_names();
		if( $this->options['use_month_numbers']){
			return $number;
		}elseif($this->options['use_two_digit_numbers']){
			return sprintf( "%02d", $number );
		}elseif($this->options['add_month_numbers']){
			$month_names = $this->month_names();
			return "{$number} - {$month_names[$number]}";
		}else{
			return $month_names[$number];
		}
	}

	private function date_order(){
		$this->date_order = $this->date_order ?: $this->options['order'] || $this->translated_date_order();
		return $this->date_order;
	}

	private function translated_date_order(){
		$date_order = I18n::translate('date.order', array('locale' => $this->options['locale'], 'default' => array()));

		$forbidden_elements = array_diff($date_order, array('year', 'month', 'day'));
		if(count($forbidden_elements)){
			throw new StandardError("{$this->options['locale']}.date.order only accepts :year, :month and :day");
		}

		return $date_order;
	}

	# Build full select tag from date type and options.
	private function build_options_and_select($type, $selected, $options = array()){
		return $this->build_select($type, $this->build_options($selected, $options));
	}

	# Build select option html from date value and options.
	#  build_options(15, :start => 1, :end => 31)
	#  => "<option value="1">1</option>
	#      <option value="2">2</option>
	#      <option value="3">3</option>..."
	#
	# If <tt>:use_two_digit_numbers => true</tt> option is passed
	#  build_options(15, :start => 1, :end => 31, :use_two_digit_numbers => true)
	#  => "<option value="1">01</option>
	#      <option value="2">02</option>
	#      <option value="3">03</option>..."
	#
	# If <tt>:step</tt> options is passed
	#  build_options(15, :start => 1, :end => 31, :step => 2)
	#  => "<option value="1">1</option>
	#      <option value="3">3</option>
	#      <option value="5">5</option>..."
	private function build_options($selected, $options = array()){
		$start         = delete($options, 'start') ?: 0;
		$stop          = delete($options, 'end') ?: 59;
		$step          = delete($options, 'step') ?: 1;
		$options = array_merge(array('leading_zeros' => true, 'ampm' => false, 'use_two_digit_numbers' => false), $options);
		$leading_zeros = delete($options, 'leading_zeros');
		
		$select_options = array();
		/*
			FIXME Convert Integer.step() to PHP
		*/
		/*
		start.step(stop, step) do |i|
		  value = leading_zeros ? sprintf("%02d", i) : i
		  tag_options = { :value => value }
		  tag_options[:selected] = "selected" if selected == i
		  text = options[:use_two_digit_numbers] ? sprintf("%02d", i) : value
		  text = options[:ampm] ? AMPM_TRANSLATION[i] : text
		  select_options << content_tag(:option, text, tag_options)
		end
		(select_options.join("\n") + "\n").html_safe
		*/
	}

	# Builds select tag from date type and html select options.
	#  build_select(:month, "<option value="1">January</option>...")
	#  => "<select id="post_written_on_2i" name="post[written_on(2i)]">
	#        <option value="1">January</option>...
	#      </select>"
	private function build_select($type, $select_options_as_html){
		$select_options = array_merge(array(
			'id' => $this->input_id_from_type($type),
			'name' => $this->input_name_from_type($type)
		), $this->html_options);
		if($this->options['disabled']){
			$select_options = array_merge($select_options,array('disabled' => 'disabled'));
		}

		$select_html = "\n";
		if($this->options['include_blank']){
			$select_html .= Taghelper::content_tag('option', '', array('value' => '')) . "\n";
		}
		if($this->options['prompt']){
			$select_html .= $this->prompt_option_tag($type, $this->options['prompt']) . "\n";
		}
		$select_html .= $select_options_as_html;

		return Taghelper::content_tag('select', $select_html /*.html_safe */, $select_options) . "\n"; # .html_safe
	}

	# Builds a prompt option tag with supplied options or from default options.
	#  prompt_option_tag(:month, :prompt => 'Select month')
	#  => "<option value="">Select month</option>"
	private function prompt_option_tag($type, $options){
		switch($options){
			case is_hash($options):
				$default_options = array('year' => false, 'month' => false, 'day' => false, 'hour' => false, 'minute' => false, 'second' => false);
				$default_options = array_merge($default_options, $options);
				$prompt = $default_options[$type];
				break;
			case is_string($options):
			  $prompt = $options;
			  break;
			default:
			  $prompt = I18n::translate(":datetime.prompts.{$type}", array('locale' => $this->options['locale']));
		}
		
		return $prompt ? Taghelper::content_tag('option', $prompt, array('value' => '')) : '';
	}

	# Builds hidden input tag for date part and value.
	#  build_hidden(:year, 2008)
	#  => "<input id="post_written_on_1i" name="post[written_on(1i)]" type="hidden" value="2008" />"
	private function build_hidden($type, $value){
		$html_options = array_merge(array(
			'type' => "hidden",
			'id' => $this->input_id_from_type($type),
			'name' => $this->input_name_from_type($type),
			'value' => $value
		), $this->html_options);
		unset($html_options['disabled']);
		return Taghelper::tag('input', $html_options) . "\n"; #.html_safe
	}

	# Returns the name attribute for the input tag.
	#  => post[written_on(1i)]
	private function input_name_from_type($type){
		$prefix = $this->options['prefix'] ?: ActionView\Helpers\DateTimeSelector::$DEFAULT_PREFIX;
		if(array_key_exists('index', $this->options)){
			$prefix .= "[{$this->options['index']}]";
		}

		$field_name = $this->options['field_name'] ?: $type;
		if($this->options['include_position']){
			$position = ActionView\Helpers\DateTimeSelector::$POSITION[$type];
			$field_name .= "({$position}i)";
		}

		return $this->options['discard_type'] ? $prefix : "{$prefix}[{$field_name}]";
	}

	# Returns the id attribute for the input tag.
	#  => "post_written_on_1i"
	private function input_id_from_type($type){
		$name = $this->input_name_from_type($type);
		$id = preg_replace('/[\]\)]/', '', preg_replace('/([\[\(])|(\]\[)/', '_', $name));
		if( $this->options['namespace']){
			$id = $this->options['namespace'] . '_' . $id;
		}
		
		return $id;
	}

	# Given an ordering of datetime components, create the selection HTML
	# and join them with their appropriate separators.
	private function build_selects_from_types($order){
		$select = '';
		
		foreach($order as $type){
			if(!$this->options["discard_{$type}"]){
				$first_visible = $type;
				break;
			}
		}
		$order = array_reverse($order);
		foreach($order as $type){
			$separator = $type != $first_visible ? $this->separator($type) : ''; # don't add before first visible field
			$select = $separator . call_user_func(array($this, "select_{$type}")) . $select;
		}
		return $select; # .html_safe
	}

	# Returns the separator for a given datetime component.
	private function separator($type){
		if($this->options['use_hidden']){
			return "";
		}
		switch($type){
			case 'year':
			case 'month':
			case 'day':
				return $this->options["discard_{$type}"] ? "" : $this->options['date_separator'];
			case 'hour':
				return ($this->options['discard_year'] && $this->options['discard_day']) ? "" : $this->options['datetime_separator'];
			case 'minute':
			case 'second':
				return $this->options["discard_{$type}"] ? "" : $this->options['time_separator'];
		}
	}
}

class FormBuilder{
	public static function date_select($method, $options = array(), $html_options = array()){
		#@template.date_select(@object_name, method, objectify_options(options), html_options)
	}
	
	public static function time_select($method, $options = array(), $html_options = array()){
		#@template.time_select(@object_name, method, objectify_options(options), html_options)
	}
	
	public static function datetime_select($method, $options = array(), $html_options = array()){
		#@template.datetime_select(@object_name, method, objectify_options(options), html_options)
	}
}

