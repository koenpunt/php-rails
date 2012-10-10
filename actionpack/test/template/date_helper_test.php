<?php 
require_once 'utils.php';
require_once 'action_view/helpers/DateHelper.php';
require_once 'i18n/I18n.php';

$translations = '../lib/action_view/locale/en.yml';

I18n\I18n::push_load_path($translations);


use \ActionView\Helpers\DateHelper;
#use \ActionView\Helpers\DateTimeSelector;
use \ActiveSupport\CoreExt\Date;
use \ActiveSupport\CoreExt\Time;
use \ActiveSupport\CoreExt\TimeSpan;

class DateHelperTest extends PHPUnit_Framework_TestCase{

	
	public function assert_distance_of_time_in_words($from, $to = null){
		$to = is_null($to) ? $from->dup() : $to;
		# 0..1 minute with :include_seconds => true
		$this->assertEquals("less than 5 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+ 0 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than 5 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+ 4 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than 10 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+ 5 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than 10 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+ 9 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than 20 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+ 10 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than 20 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+ 19 seconds'), array('include_seconds' => true)));
		$this->assertEquals("half a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 20 seconds'), array('include_seconds' => true)));
		$this->assertEquals("half a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 39 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 40 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 59 seconds'), array('include_seconds' => true)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 60 seconds'), array('include_seconds' => true)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 89 seconds'), array('include_seconds' => true)));

		# 0..1 minute with :include_seconds => false
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 0 seconds'), array('include_seconds' => false)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 4 seconds'), array('include_seconds' => false)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 5 seconds'), array('include_seconds' => false)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 9 seconds'), array('include_seconds' => false)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 10 seconds'), array('include_seconds' => false)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 19 seconds'), array('include_seconds' => false)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 20 seconds'), array('include_seconds' => false)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 39 seconds'), array('include_seconds' => false)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 40 seconds'), array('include_seconds' => false)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 59 seconds'), array('include_seconds' => false)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 60 seconds'), array('include_seconds' => false)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+ 89 seconds'), array('include_seconds' => false)));

		# Note that we are including a 30-second boundary around the interval we
		# want to test. For instance, "1 minute" is actually 30s to 1m29s. The
		# reason for doing this is simple -- in `distance_of_time_to_words`, when we
		# take the distance between our two Time objects in seconds and convert it
		# to minutes, we round the number. So 29s gets rounded down to 0m, 30s gets
		# rounded up to 1m, and 1m29s gets rounded down to 1m. A similar thing
		# happens with the other cases.

		# First case 0..1 minute
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+0 seconds')));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update('+29 seconds')));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+30 seconds')));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words($from, $to->update('+1 minutes +29 seconds')));

		# 2 minutes up to 45 minutes
		$this->assertEquals("2 minutes", DateHelper::distance_of_time_in_words($from, $to->update('+1 minutes +30 seconds')));
		$this->assertEquals("44 minutes", DateHelper::distance_of_time_in_words($from, $to->update('+44 minutes +29 seconds')));

		# 45 minutes up to 90 minutes
		$this->assertEquals("about 1 hour", DateHelper::distance_of_time_in_words($from, $to->update('+44 minutes +30 seconds')));
		$this->assertEquals("about 1 hour", DateHelper::distance_of_time_in_words($from, $to->update('+89 minutes +29 seconds')));

		# 90 minutes up to 24 hours
		$this->assertEquals("about 2 hours", DateHelper::distance_of_time_in_words($from, $to->update('+89 minutes +30 seconds')));
		$this->assertEquals("about 24 hours", DateHelper::distance_of_time_in_words($from, $to->update('+23 hours +59 minutes +29 seconds')));

		# 24 hours up to 42 hours
		$this->assertEquals("1 day", DateHelper::distance_of_time_in_words($from, $to->update('+23 hours +59 minutes +30 seconds')));
		$this->assertEquals("1 day", DateHelper::distance_of_time_in_words($from, $to->update('+41 hours +59 minutes +29 seconds')));

		# 42 hours up to 30 days
		$this->assertEquals("2 days", DateHelper::distance_of_time_in_words($from, $to->update('+41 hours +59 minutes +30 seconds')));
		$this->assertEquals("3 days", DateHelper::distance_of_time_in_words($from, $to->update('+2 days +12 hours')));
		$this->assertEquals("30 days", DateHelper::distance_of_time_in_words($from, $to->update('+29 days +23 hours +59 minutes +29 seconds')));

		# 30 days up to 60 days
		$this->assertEquals("about 1 month", DateHelper::distance_of_time_in_words($from, $to->update('+29 days +23 hours +59 minutes +30 seconds')));
		$this->assertEquals("about 1 month", DateHelper::distance_of_time_in_words($from, $to->update('+44 days +23 hours +59 minutes +29 seconds')));
		$this->assertEquals("about 2 months", DateHelper::distance_of_time_in_words($from, $to->update('+44 days +23 hours +59 minutes +30 seconds')));
		$this->assertEquals("about 2 months", DateHelper::distance_of_time_in_words($from, $to->update('+59 days +23 hours +59 minutes +29 seconds')));

		# 60 days up to 365 days
		$this->assertEquals("2 months", DateHelper::distance_of_time_in_words($from, $to->update('+59 days +23 hours +59 minutes +30 seconds')));
		$this->assertEquals("12 months", DateHelper::distance_of_time_in_words($from, $to->update('+1 years -31 seconds')));

		# >= 365 days
		#		$this->assertEquals("about 1 year",    DateHelper::distance_of_time_in_words($from, $to->update('+1 years -30 seconds')));
		$this->assertEquals("about 1 year",    DateHelper::distance_of_time_in_words($from, $to->update('+1 years +3 months -1 day')));
		$this->assertEquals("over 1 year",     DateHelper::distance_of_time_in_words($from, $to->update('+1 years +6 months')));

		#		$this->assertEquals("almost 2 years",  DateHelper::distance_of_time_in_words($from, $to->update('+2 years -3 months +1 day')));
		$this->assertEquals("about 2 years",   DateHelper::distance_of_time_in_words($from, $to->update('+2 years +3 months -1 day')));
		$this->assertEquals("over 2 years",    DateHelper::distance_of_time_in_words($from, $to->update('+2 years +3 months +1 day')));
		$this->assertEquals("over 2 years",    DateHelper::distance_of_time_in_words($from, $to->update('+2 years +9 months -1 day')));
		#		$this->assertEquals("almost 3 years",  DateHelper::distance_of_time_in_words($from, $to->update('+2 years +9 months +1 day')));

		$this->assertEquals("almost 5 years",  DateHelper::distance_of_time_in_words($from, $to->update('+5 years -3 months +1 day')));
		$this->assertEquals("about 5 years",   DateHelper::distance_of_time_in_words($from, $to->update('+5 years +3 months -1 day')));
		$this->assertEquals("over 5 years",    DateHelper::distance_of_time_in_words($from, $to->update('+5 years +3 months +1 day')));
		$this->assertEquals("over 5 years",    DateHelper::distance_of_time_in_words($from, $to->update('+5 years +9 months -1 day')));
		$this->assertEquals("almost 6 years",  DateHelper::distance_of_time_in_words($from, $to->update('+5 years +9 months +1 day')));

		$this->assertEquals("almost 10 years", DateHelper::distance_of_time_in_words($from, $to->update('+10 years -3 months +1 day')));
		#		$this->assertEquals("about 10 years",  DateHelper::distance_of_time_in_words($from, $to->update('+10 years +3 months -1 day')));
		$this->assertEquals("over 10 years",   DateHelper::distance_of_time_in_words($from, $to->update('+10 years +3 months +1 day')));
		$this->assertEquals("over 10 years",   DateHelper::distance_of_time_in_words($from, $to->update('+10 years +9 months -1 day')));
		$this->assertEquals("almost 11 years", DateHelper::distance_of_time_in_words($from, $to->update('+10 years +9 months +1 day')));

		# test to < from
		$this->assertEquals("about 4 hours", DateHelper::distance_of_time_in_words($from, $to->update('+4 hours')));
		$this->assertEquals("less than 20 seconds", DateHelper::distance_of_time_in_words($from, $to->update('+19 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words($from, $to->update( '+19 seconds'), array('include_seconds' => false)));
	}
	
	public function test_distance_in_words(){
		$from = Time::utc(2004, 6, 6, 21, 45, 0);
		$this->assert_distance_of_time_in_words($from);
	}

	public function test_time_ago_in_words_passes_include_seconds(){
		$this->assertEquals("less than 20 seconds", DateHelper::time_ago_in_words(new Date('-15 seconds'), array('include_seconds' => true)));
		$this->assertEquals("less than a minute", DateHelper::time_ago_in_words(new Date('-15 seconds'), array('include_seconds' => false)));
	}

	public function test_distance_in_words_with_time_zones(){
		$from = Time::mktime(2004, 6, 6, 21, 45, 0);
		
		$this->assert_distance_of_time_in_words($from->in_time_zone('America/Anchorage')); 
										# In Rails source it was: 'Alaska'
		$this->assert_distance_of_time_in_words($from->in_time_zone('Pacific/Honolulu')); 
										# In Rails source it was: 'Hawaii'
	}

	public function test_distance_in_words_with_different_time_zones(){
		$from = Time::mktime(2004, 6, 6, 21, 45, 0);
		$this->assert_distance_of_time_in_words(
			$from->in_time_zone('America/Anchorage'),
			$from->in_time_zone('Pacific/Honolulu')
		);
	}
	
	public function test_distance_in_words_with_dates(){
		$start_date = new Date( 1975, 1, 31 );
		$end_date = new Date( 1977, 1, 31 );
		$this->assertEquals("about 2 years", DateHelper::distance_of_time_in_words($start_date, $end_date));
	
		$start_date = new Date( 1982, 12, 3 );
		$end_date = new Date( 2010, 11, 30 );
		$this->assertEquals("almost 28 years", DateHelper::distance_of_time_in_words($start_date, $end_date));
		$this->assertEquals("almost 28 years", DateHelper::distance_of_time_in_words($end_date, $start_date));
	}
	
	public function test_distance_in_words_with_integers(){
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words(59));
		$this->assertEquals("about 1 hour", DateHelper::distance_of_time_in_words(60*60));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words(0, 59));
		$this->assertEquals("about 1 hour", DateHelper::distance_of_time_in_words(60*60, 0));
		$this->assertEquals("about 3 years", DateHelper::distance_of_time_in_words(pow(10, 8)));
		$this->assertEquals("about 3 years", DateHelper::distance_of_time_in_words(0, pow(10, 8)));
	}

	public function test_distance_in_words_with_times(){
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words(new TimeSpan('+30 seconds')));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words(new TimeSpan('+59 seconds')));
		$this->assertEquals("2 minutes", DateHelper::distance_of_time_in_words(new TimeSpan('+119 seconds')));
		$this->assertEquals("2 minutes", DateHelper::distance_of_time_in_words(new TimeSpan('+1 minute +59 seconds')));
		$this->assertEquals("3 minutes", DateHelper::distance_of_time_in_words(new TimeSpan('+2 minutes +30 seconds')));
		$this->assertEquals("44 minutes", DateHelper::distance_of_time_in_words(new TimeSpan('+44 minutes +29 seconds')));
		$this->assertEquals("about 1 hour", DateHelper::distance_of_time_in_words(new TimeSpan('+44 minutes +30 seconds')));
		$this->assertEquals("about 1 hour", DateHelper::distance_of_time_in_words(new TimeSpan('+60 minutes')));

		# include seconds
		$this->assertEquals("half a minute", DateHelper::distance_of_time_in_words(new TimeSpan('+39 seconds'), 0, array('include_seconds' => true)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words(new TimeSpan('+40 seconds'), 0, array('include_seconds' => true)));
		$this->assertEquals("less than a minute", DateHelper::distance_of_time_in_words(new TimeSpan('+59 seconds'), 0, array('include_seconds' => true)));
		$this->assertEquals("1 minute", DateHelper::distance_of_time_in_words(new TimeSpan('+60 seconds'), 0, array('include_seconds' => true)));
	}
	
	public function test_time_ago_in_words(){
		$this->assertEquals("about 1 year", DateHelper::time_ago_in_words(new Date('-1 year -1 day')));
	}

	public function test_select_day(){
		$expected = <<<HTML
<select id="date_day" name="date[day]">
<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>
</select>\n
HTML;
		$this->assertEquals($expected, (string)DateHelper::select_day(Time::mktime(2003, 8, 16, 0, 0, 0)));
		$this->assertEquals($expected, (string)DateHelper::select_day(16));
	}
	
	public function test_select_day_with_blank(){
		$expected = <<<HTML
<select id="date_day" name="date[day]">
<option value=""></option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>
</select>\n
HTML;
	
		$this->assertEquals($expected, (string)DateHelper::select_day(Time::mktime(2003, 8, 16, 0, 0, 0), array('include_blank' => true)));
		$this->assertEquals($expected, (string)DateHelper::select_day(16, array('include_blank' => true)));
	}

	public function test_select_day_null_with_blank(){
		$expected = <<<HTML
<select id="date_day" name="date[day]">
<option value=""></option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>
</select>\n
HTML;
		
		$this->assertEquals($expected, (string)DateHelper::select_day(null, array('include_blank' => true)));
	}

	public function test_select_day_with_two_digit_numbers(){
		$expected = <<<HTML
<select id="date_day" name="date[day]">
<option value="1">01</option>\n<option selected="selected" value="2">02</option>\n<option value="3">03</option>\n<option value="4">04</option>\n<option value="5">05</option>\n<option value="6">06</option>\n<option value="7">07</option>\n<option value="8">08</option>\n<option value="9">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>
</select>\n
HTML;
		
		$this->assertEquals($expected, (string)DateHelper::select_day(Time::mktime(2011, 8, 2, 0, 0, 0), array('use_two_digit_numbers' => true)));
		$this->assertEquals($expected, (string)DateHelper::select_day(2, array('use_two_digit_numbers' => true)));
	}

	public function test_select_day_with_html_options(){
		$expected = <<<HTML
<select class="selector" id="date_day" name="date[day]">
<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>
</select>\n
HTML;

		$this->assertEquals($expected, (string)DateHelper::select_day(Time::mktime(2003, 8, 16, 0, 0, 0), array(), array('class' => 'selector')));
		$this->assertEquals($expected, (string)DateHelper::select_day(16, array(), array('class' => 'selector')));
	}

	public function test_select_day_with_default_prompt(){
		$expected = <<<HTML
<select id="date_day" name="date[day]">
<option value="">Day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>
</select>\n
HTML;

		$this->assertEquals($expected, (string)DateHelper::select_day(16, array('prompt' => true)));
	}
	/*
	public function test_select_day_with_custom_prompt(){
    expected = %(<select id="date_day" name="date[day]">\n)
    expected << %(<option value="">Choose day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    $this->assertEquals($expected, (string)DateHelper::select_day(16, :prompt => 'Choose day')
	}

	public function test_select_month(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0))
    assert_dom_equal expected, select_month(8)
	}

	public function test_select_month_with_two_digit_numbers(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="1">01</option>\n<option value="2">02</option>\n<option value="3">03</option>\n<option value="4">04</option>\n<option value="5">05</option>\n<option value="6">06</option>\n<option value="7">07</option>\n<option selected="selected" value="8">08</option>\n<option value="9">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2011, 8, 16, 0, 0, 0), :use_two_digit_numbers => true)
    assert_dom_equal expected, select_month(8, :use_two_digit_numbers => true)
	}

	public function test_select_month_with_disabled(){
    expected = %(<select id="date_month" name="date[month]" disabled="disabled">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :disabled => true)
    assert_dom_equal expected, select_month(8, :disabled => true)
	}

	public function test_select_month_with_field_name_override(){
    expected = %(<select id="date_mois" name="date[mois]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :field_name => 'mois')
    assert_dom_equal expected, select_month(8, :field_name => 'mois')
	}

	public function test_select_month_with_blank(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value=""></option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :include_blank => true)
    assert_dom_equal expected, select_month(8, :include_blank => true)
	}

	public function test_select_month_null_with_blank(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value=""></option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(null, :include_blank => true)
	}

	public function test_select_month_with_numbers(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option selected="selected" value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :use_month_numbers => true)
    assert_dom_equal expected, select_month(8, :use_month_numbers => true)
	}

	public function test_select_month_with_numbers_and_names(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="1">1 - January</option>\n<option value="2">2 - February</option>\n<option value="3">3 - March</option>\n<option value="4">4 - April</option>\n<option value="5">5 - May</option>\n<option value="6">6 - June</option>\n<option value="7">7 - July</option>\n<option selected="selected" value="8">8 - August</option>\n<option value="9">9 - September</option>\n<option value="10">10 - October</option>\n<option value="11">11 - November</option>\n<option value="12">12 - December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :add_month_numbers => true)
    assert_dom_equal expected, select_month(8, :add_month_numbers => true)
	}

	public function test_select_month_with_numbers_and_names_with_abbv(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="1">1 - Jan</option>\n<option value="2">2 - Feb</option>\n<option value="3">3 - Mar</option>\n<option value="4">4 - Apr</option>\n<option value="5">5 - May</option>\n<option value="6">6 - Jun</option>\n<option value="7">7 - Jul</option>\n<option selected="selected" value="8">8 - Aug</option>\n<option value="9">9 - Sep</option>\n<option value="10">10 - Oct</option>\n<option value="11">11 - Nov</option>\n<option value="12">12 - Dec</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :add_month_numbers => true, :use_short_month => true)
    assert_dom_equal expected, select_month(8, :add_month_numbers => true, :use_short_month => true)
	}

	public function test_select_month_with_abbv(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="1">Jan</option>\n<option value="2">Feb</option>\n<option value="3">Mar</option>\n<option value="4">Apr</option>\n<option value="5">May</option>\n<option value="6">Jun</option>\n<option value="7">Jul</option>\n<option selected="selected" value="8">Aug</option>\n<option value="9">Sep</option>\n<option value="10">Oct</option>\n<option value="11">Nov</option>\n<option value="12">Dec</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :use_short_month => true)
    assert_dom_equal expected, select_month(8, :use_short_month => true)
	}

	public function test_select_month_with_custom_names(){
    month_names = %w(null Januar Februar Marts April Maj Juni Juli August September Oktober November December)

    expected = %(<select id="date_month" name="date[month]">\n)
    1.upto(12) { |month| expected << %(<option value="#{month}"#{' selected="selected"' if month == 8}>#{month_names[month]}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :use_month_names => month_names)
    assert_dom_equal expected, select_month(8, :use_month_names => month_names)
	}

	public function test_select_month_with_zero_indexed_custom_names(){
    month_names = %w(Januar Februar Marts April Maj Juni Juli August September Oktober November December)

    expected = %(<select id="date_month" name="date[month]">\n)
    1.upto(12) { |month| expected << %(<option value="#{month}"#{' selected="selected"' if month == 8}>#{month_names[month-1]}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), :use_month_names => month_names)
    assert_dom_equal expected, select_month(8, :use_month_names => month_names)
	}

	public function test_select_month_with_hidden(){
    assert_dom_equal "<input type=\"hidden\" id=\"date_month\" name=\"date[month]\" value=\"8\" />\n", select_month(8, :use_hidden => true)
	}

	public function test_select_month_with_hidden_and_field_name(){
    assert_dom_equal "<input type=\"hidden\" id=\"date_mois\" name=\"date[mois]\" value=\"8\" />\n", select_month(8, :use_hidden => true, :field_name => 'mois')
	}

	public function test_select_month_with_html_options(){
    expected = %(<select class="selector" id="date_month" name="date[month]" accesskey="M">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(Time::mktime(2003, 8, 16, 0, 0, 0), {}, :class => 'selector', :accesskey => 'M')
    #result = select_month(Time::mktime(2003, 8, 16, 0, 0, 0), {}, :class => 'selector', :accesskey => 'M')
    #assert result.include?('<select id="date_month" name="date[month]"')
    #assert result.include?('class="selector"')
    #assert result.include?('accesskey="M"')
    #assert result.include?('<option value="1">January')
	}

	public function test_select_month_with_default_prompt(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="">Month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(8, :prompt => true)
	}

	public function test_select_month_with_custom_prompt(){
    expected = %(<select id="date_month" name="date[month]">\n)
    expected << %(<option value="">Choose month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_month(8, :prompt => 'Choose month')
	}

	public function test_select_year(){
    expected = %(<select id="date_year" name="date[year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2003, :end_year => 2005)
    assert_dom_equal expected, select_year(2003, :start_year => 2003, :end_year => 2005)
	}

	public function test_select_year_with_disabled(){
    expected = %(<select id="date_year" name="date[year]" disabled="disabled">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(Time::mktime(2003, 8, 16, 0, 0, 0), :disabled => true, :start_year => 2003, :end_year => 2005)
    assert_dom_equal expected, select_year(2003, :disabled => true, :start_year => 2003, :end_year => 2005)
	}

	public function test_select_year_with_field_name_override(){
    expected = %(<select id="date_annee" name="date[annee]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2003, :end_year => 2005, :field_name => 'annee')
    assert_dom_equal expected, select_year(2003, :start_year => 2003, :end_year => 2005, :field_name => 'annee')
	}

	public function test_select_year_with_type_discarding(){
    expected = %(<select id="date_year" name="date_year">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(
      mktime(0, 0, 0, 8, 16, 2003), :prefix => "date_year", :discard_type => true, :start_year => 2003, :end_year => 2005)
    assert_dom_equal expected, select_year(
      2003, :prefix => "date_year", :discard_type => true, :start_year => 2003, :end_year => 2005)
	}

	public function test_select_year_descending(){
    expected = %(<select id="date_year" name="date[year]">\n)
    expected << %(<option selected="selected" value="2005">2005</option>\n<option value="2004">2004</option>\n<option value="2003">2003</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(Time::mktime(2005, 8, 16, 0, 0, 0), :start_year => 2005, :end_year => 2003)
    assert_dom_equal expected, select_year(2005, :start_year => 2005, :end_year => 2003)
	}

	public function test_select_year_with_hidden(){
    assert_dom_equal "<input type=\"hidden\" id=\"date_year\" name=\"date[year]\" value=\"2007\" />\n", select_year(2007, :use_hidden => true)
	}

	public function test_select_year_with_hidden_and_field_name(){
    assert_dom_equal "<input type=\"hidden\" id=\"date_anno\" name=\"date[anno]\" value=\"2007\" />\n", select_year(2007, :use_hidden => true, :field_name => 'anno')
	}

	public function test_select_year_with_html_options(){
    expected = %(<select class="selector" id="date_year" name="date[year]" accesskey="M">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(Time::mktime(2003, 8, 16, 0, 0, 0), {:start_year => 2003, :end_year => 2005}, :class => 'selector', :accesskey => 'M')
    #result = select_year(Time::mktime(2003, 8, 16, 0, 0, 0), {:start_year => 2003, :end_year => 2005}, :class => 'selector', :accesskey => 'M')
    #assert result.include?('<select id="date_year" name="date[year]"')
    #assert result.include?('class="selector"')
    #assert result.include?('accesskey="M"')
    #assert result.include?('<option value="2003"')
	}

	public function test_select_year_with_default_prompt(){
    expected = %(<select id="date_year" name="date[year]">\n)
    expected << %(<option value="">Year</option>\n<option value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(null, :start_year => 2003, :end_year => 2005, :prompt => true)
	}

	public function test_select_year_with_custom_prompt(){
    expected = %(<select id="date_year" name="date[year]">\n)
    expected << %(<option value="">Choose year</option>\n<option value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_year(null, :start_year => 2003, :end_year => 2005, :prompt => 'Choose year')
	}

	public function test_select_hour(){
    expected = %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18))
	}

	public function test_select_hour_with_ampm(){
    expected = %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">12 AM</option>\n<option value="01">01 AM</option>\n<option value="02">02 AM</option>\n<option value="03">03 AM</option>\n<option value="04">04 AM</option>\n<option value="05">05 AM</option>\n<option value="06">06 AM</option>\n<option value="07">07 AM</option>\n<option selected="selected" value="08">08 AM</option>\n<option value="09">09 AM</option>\n<option value="10">10 AM</option>\n<option value="11">11 AM</option>\n<option value="12">12 PM</option>\n<option value="13">01 PM</option>\n<option value="14">02 PM</option>\n<option value="15">03 PM</option>\n<option value="16">04 PM</option>\n<option value="17">05 PM</option>\n<option value="18">06 PM</option>\n<option value="19">07 PM</option>\n<option value="20">08 PM</option>\n<option value="21">09 PM</option>\n<option value="22">10 PM</option>\n<option value="23">11 PM</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), :ampm => true)
	}

	public function test_select_hour_with_disabled(){
    expected = %(<select id="date_hour" name="date[hour]" disabled="disabled">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), :disabled => true)
	}

	public function test_select_hour_with_field_name_override(){
    expected = %(<select id="date_heure" name="date[heure]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), :field_name => 'heure')
	}

	public function test_select_hour_with_blank(){
    expected = %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), :include_blank => true)
	}

	public function test_select_hour_null_with_blank(){
    expected = %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(null, :include_blank => true)
	}

	public function test_select_hour_with_html_options(){
    expected = %(<select class="selector" id="date_hour" name="date[hour]" accesskey="M">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector', :accesskey => 'M')
	}

	public function test_select_hour_with_default_prompt(){
    expected = %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="">Hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => true)
	}

	public function test_select_hour_with_custom_prompt(){
    expected = %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="">Choose hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_hour(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => 'Choose hour')
	}

	public function test_select_minute(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18))
	}

	public function test_select_minute_with_disabled(){
    expected = %(<select id="date_minute" name="date[minute]" disabled="disabled">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), :disabled => true)
	}

	public function test_select_minute_with_field_name_override(){
    expected = %(<select id="date_minuto" name="date[minuto]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), :field_name => 'minuto')
	}

	public function test_select_minute_with_blank(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), :include_blank => true)
	}

	public function test_select_minute_with_blank_and_step(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="15">15</option>\n<option value="30">30</option>\n<option value="45">45</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), { :include_blank => true , :minute_step => 15 })
	}

	public function test_select_minute_null_with_blank(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(null, :include_blank => true)
	}

	public function test_select_minute_null_with_blank_and_step(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="15">15</option>\n<option value="30">30</option>\n<option value="45">45</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(null, { :include_blank => true , :minute_step => 15 })
	}

	public function test_select_minute_with_hidden(){
    assert_dom_equal "<input type=\"hidden\" id=\"date_minute\" name=\"date[minute]\" value=\"8\" />\n", select_minute(8, :use_hidden => true)
	}

	public function test_select_minute_with_hidden_and_field_name(){
    assert_dom_equal "<input type=\"hidden\" id=\"date_minuto\" name=\"date[minuto]\" value=\"8\" />\n", select_minute(8, :use_hidden => true, :field_name => 'minuto')
	}

	public function test_select_minute_with_html_options(){
    expected = %(<select class="selector" id="date_minute" name="date[minute]" accesskey="M">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector', :accesskey => 'M')

    #result = select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector', :accesskey => 'M')
    #assert result.include?('<select id="date_minute" name="date[minute]"')
    #assert result.include?('class="selector"')
    #assert result.include?('accesskey="M"')
    #assert result.include?('<option value="00">00')
	}

	public function test_select_minute_with_default_prompt(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="">Minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => true)
	}

	public function test_select_minute_with_custom_prompt(){
    expected = %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="">Choose minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_minute(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => 'Choose minute')
	}

	public function test_select_second(){
    expected = %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18))
	}

	public function test_select_second_with_disabled(){
    expected = %(<select id="date_second" name="date[second]" disabled="disabled">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18), :disabled => true)
	}

	public function test_select_second_with_field_name_override(){
    expected = %(<select id="date_segundo" name="date[segundo]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18), :field_name => 'segundo')
	}

	public function test_select_second_with_blank(){
    expected = %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18), :include_blank => true)
	}

	public function test_select_second_null_with_blank(){
    expected = %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value=""></option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(null, :include_blank => true)
	}

	public function test_select_second_with_html_options(){
    expected = %(<select class="selector" id="date_second" name="date[second]" accesskey="M">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector', :accesskey => 'M')

    #result = select_second(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector', :accesskey => 'M')
    #assert result.include?('<select id="date_second" name="date[second]"')
    #assert result.include?('class="selector"')
    #assert result.include?('accesskey="M"')
    #assert result.include?('<option value="00">00')
	}

	public function test_select_second_with_default_prompt(){
    expected = %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="">Seconds</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => true)
	}

	public function test_select_second_with_custom_prompt(){
    expected = %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="">Choose seconds</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_second(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => 'Choose seconds')
	}

	public function test_select_date(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2003, :end_year => 2005, :prefix => "date[first]")
	}

	public function test_select_date_with_too_big_range_between_start_year_and_end_year(){
    assert_raise(ArgumentError) { select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2000, :end_year => 20000, :prefix => "date[first]", :order => [:month, :day, :year]) }
    assert_raise(ArgumentError) { select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => Date.today.year -100 years, :end_year => 2000, :prefix => "date[first]", :order => [:month, :day, :year]) }
	}

	public function test_select_date_can_have_more_then_(){1000_years_interval_if_forced_via_parameter
    assert_nothing_raised { select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2000, :end_year => 3100, :max_years_allowed => 2000) }
	}

	public function test_select_date_with_order(){
    expected = %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected <<  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2003, :end_year => 2005, :prefix => "date[first]", :order => [:month, :day, :year])
	}

	public function test_select_date_with_incomplete_order(){
    # Since the order is incomplete nothing will be shown
    expected = %(<input id="date_first_year" name="date[first][year]" type="hidden" value="2003" />\n)
    expected << %(<input id="date_first_month" name="date[first][month]" type="hidden" value="8" />\n)
    expected << %(<input id="date_first_day" name="date[first][day]" type="hidden" value="1" />\n)

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2003, :end_year => 2005, :prefix => "date[first]", :order => [:day])
	}

	public function test_select_date_with_disabled(){
    expected =  %(<select id="date_first_year" name="date[first][year]" disabled="disabled">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]" disabled="disabled">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]" disabled="disabled">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :start_year => 2003, :end_year => 2005, :prefix => "date[first]", :disabled => true)
	}

	public function test_select_date_with_no_start_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+1) do |y|
      if y == Date.today.year
        expected << %(<option value="#{y}" selected="selected">#{y}</option>\n)
      else
        expected << %(<option value="#{y}">#{y}</option>\n)
    	}
  	}
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(
      Time.mktime(Date.today.year, 8, 16), :end_year => Date.today.year+1, :prefix => "date[first]"
    )
	}

	public function test_select_date_with_no_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    2003.upto(2008) do |y|
      if y == 2003
        expected << %(<option value="#{y}" selected="selected">#{y}</option>\n)
      else
        expected << %(<option value="#{y}">#{y}</option>\n)
    	}
  	}
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(
      mktime(0, 0, 0, 8, 16, 2003), :start_year => 2003, :prefix => "date[first]"
    )
	}

	public function test_select_date_with_no_start_or_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) do |y|
      if y == Date.today.year
        expected << %(<option value="#{y}" selected="selected">#{y}</option>\n)
      else
        expected << %(<option value="#{y}">#{y}</option>\n)
    	}
  	}
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(
      Time.mktime(Date.today.year, 8, 16), :prefix => "date[first]"
    )
	}

	public function test_select_date_with_zero_value(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :start_year => 2003, :end_year => 2005, :prefix => "date[first]")
	}

	public function test_select_date_with_zero_value_and_no_start_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+1) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :end_year => Date.today.year+1, :prefix => "date[first]")
	}

	public function test_select_date_with_zero_value_and_no_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    last_year = Time.now.year + 5
    2003.upto(last_year) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :start_year => 2003, :prefix => "date[first]")
	}

	public function test_select_date_with_zero_value_and_no_start_and_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :prefix => "date[first]")
	}

	public function test_select_date_with_null_value_and_no_start_and_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(null, :prefix => "date[first]")
	}

	public function test_select_date_with_html_options(){
    expected =  %(<select class="selector" id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select class="selector" id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select class="selector" id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), {:start_year => 2003, :end_year => 2005, :prefix => "date[first]"}, :class => "selector")
	}

	public function test_select_date_with_separator(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << " / "

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << " / "

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), { :date_separator => " / ", :start_year => 2003, :end_year => 2005, :prefix => "date[first]"})
	}

	public function test_select_date_with_separator_and_discard_day(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << " / "

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<input type="hidden" id="date_first_day" name="date[first][day]" value="1" />\n)

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), { :date_separator => " / ", :discard_day => true, :start_year => 2003, :end_year => 2005, :prefix => "date[first]"})
	}

	public function test_select_date_with_separator_discard_month_and_day(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<input type="hidden" id="date_first_month" name="date[first][month]" value="8" />\n)
    expected << %(<input type="hidden" id="date_first_day" name="date[first][day]" value="1" />\n)

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), { :date_separator => " / ", :discard_month => true, :discard_day => true, :start_year => 2003, :end_year => 2005, :prefix => "date[first]"})
	}

	public function test_select_date_with_hidden(){
    expected =  %(<input id="date_first_year" name="date[first][year]" type="hidden" value="2003"/>\n)
    expected << %(<input id="date_first_month" name="date[first][month]" type="hidden" value="8" />\n)
    expected << %(<input id="date_first_day" name="date[first][day]" type="hidden" value="16" />\n)

    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), { :prefix => "date[first]", :use_hidden => true })
    assert_dom_equal expected, select_date(Time::mktime(2003, 8, 16, 0, 0, 0), { :date_separator => " / ", :prefix => "date[first]", :use_hidden => true })
	}

	public function test_select_datetime(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :start_year => 2003, :end_year => 2005, :prefix => "date[first]")
	}

	public function test_select_datetime_with_ampm(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">12 AM</option>\n<option value="01">01 AM</option>\n<option value="02">02 AM</option>\n<option value="03">03 AM</option>\n<option value="04">04 AM</option>\n<option value="05">05 AM</option>\n<option value="06">06 AM</option>\n<option value="07">07 AM</option>\n<option selected="selected" value="08">08 AM</option>\n<option value="09">09 AM</option>\n<option value="10">10 AM</option>\n<option value="11">11 AM</option>\n<option value="12">12 PM</option>\n<option value="13">01 PM</option>\n<option value="14">02 PM</option>\n<option value="15">03 PM</option>\n<option value="16">04 PM</option>\n<option value="17">05 PM</option>\n<option value="18">06 PM</option>\n<option value="19">07 PM</option>\n<option value="20">08 PM</option>\n<option value="21">09 PM</option>\n<option value="22">10 PM</option>\n<option value="23">11 PM</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :start_year => 2003, :end_year => 2005, :prefix => "date[first]", :ampm => true)
	}

	public function test_select_datetime_with_separators(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :start_year => 2003, :end_year => 2005, :prefix => "date[first]", :datetime_separator => ' &mdash; ', :time_separator => ' : ')
	}

	public function test_select_datetime_with_null_value_and_no_start_and_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(null, :prefix => "date[first]")
	}

	public function test_select_datetime_with_html_options(){
    expected =  %(<select class="selector" id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"


    expected << %(<select class="selector" id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select class="selector" id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select class="selector" id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select class="selector" id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), {:start_year => 2003, :end_year => 2005, :prefix => "date[first]"}, :class => 'selector')
	}

	public function test_select_datetime_with_all_separators(){
    expected =  %(<select class="selector" id="date_first_year" name="date[first][year]">\n)
    expected << %(<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << "/"

    expected << %(<select class="selector" id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << "/"

    expected << %(<select class="selector" id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << "&mdash;"

    expected << %(<select class="selector" id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << ":"

    expected << %(<select class="selector" id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), { :datetime_separator => "&mdash;", :date_separator => "/", :time_separator => ":", :start_year => 2003, :end_year => 2005, :prefix => "date[first]"}, :class => 'selector')
	}

	public function test_select_datetime_should_work_with_date(){
    assert_nothing_raised { select_datetime(Date.today) }
	}

	public function test_select_datetime_with_default_prompt(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option value="">Year</option>\n<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="">Month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="">Day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="">Hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="">Minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :start_year => 2003, :end_year => 2005,
                                               :prefix => "date[first]", :prompt => true)
	}

	public function test_select_datetime_with_custom_prompt(){

    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    expected << %(<option value="">Choose year</option>\n<option selected="selected" value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="">Choose month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option selected="selected" value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="">Choose day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="">Choose hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="">Choose minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :start_year => 2003, :end_year => 2005, :prefix => "date[first]",
      :prompt => {:day => 'Choose day', :month => 'Choose month', :year => 'Choose year', :hour => 'Choose hour', :minute => 'Choose minute'})
	}

	public function test_select_datetime_with_hidden(){
    expected =  %(<input id="date_first_year" name="date[first][year]" type="hidden" value="2003" />\n)
    expected << %(<input id="date_first_month" name="date[first][month]" type="hidden" value="8" />\n)
    expected << %(<input id="date_first_day" name="date[first][day]" type="hidden" value="16" />\n)
    expected << %(<input id="date_first_hour" name="date[first][hour]" type="hidden" value="8" />\n)
    expected << %(<input id="date_first_minute" name="date[first][minute]" type="hidden" value="4" />\n)

    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :prefix => "date[first]", :use_hidden => true)
    assert_dom_equal expected, select_datetime(Time::mktime(2003, 8, 16, 8, 4, 18), :datetime_separator => "&mdash;", :date_separator => "/",
      :time_separator => ":", :prefix => "date[first]", :use_hidden => true)
	}

	public function test_select_time(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18))
    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :include_seconds => false)
	}

	public function test_select_time_with_ampm(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">12 AM</option>\n<option value="01">01 AM</option>\n<option value="02">02 AM</option>\n<option value="03">03 AM</option>\n<option value="04">04 AM</option>\n<option value="05">05 AM</option>\n<option value="06">06 AM</option>\n<option value="07">07 AM</option>\n<option selected="selected" value="08">08 AM</option>\n<option value="09">09 AM</option>\n<option value="10">10 AM</option>\n<option value="11">11 AM</option>\n<option value="12">12 PM</option>\n<option value="13">01 PM</option>\n<option value="14">02 PM</option>\n<option value="15">03 PM</option>\n<option value="16">04 PM</option>\n<option value="17">05 PM</option>\n<option value="18">06 PM</option>\n<option value="19">07 PM</option>\n<option value="20">08 PM</option>\n<option value="21">09 PM</option>\n<option value="22">10 PM</option>\n<option value="23">11 PM</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :include_seconds => false, :ampm => true)
	}

	public function test_select_time_with_separator(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)
    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :time_separator => ' : ')
    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :time_separator => ' : ', :include_seconds => false)
	}

	public function test_select_time_with_seconds(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << ' : '

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    expected << ' : '

    expected << %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :include_seconds => true)
	}

	public function test_select_time_with_seconds_and_separator(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :include_seconds => true, :time_separator => ' : ')
	}

	public function test_select_time_with_html_options(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select class="selector" id="date_hour" name="date[hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select class="selector" id="date_minute" name="date[minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector')
    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), {:include_seconds => false}, :class => 'selector')
	}

	public function test_select_time_should_work_with_date(){
    assert_nothing_raised { select_time(Date.today) }
	}

	public function test_select_time_with_default_prompt(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="">Hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="">Minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="">Seconds</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :include_seconds => true, :prompt => true)
	}

	public function test_select_time_with_custom_prompt(){
    expected = %(<input name="date[year]" id="date_year" value="2003" type="hidden" />\n)
    expected << %(<input name="date[month]" id="date_month" value="8" type="hidden" />\n)
    expected << %(<input name="date[day]" id="date_day" value="16" type="hidden" />\n)

    expected << %(<select id="date_hour" name="date[hour]">\n)
    expected << %(<option value="">Choose hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option selected="selected" value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_minute" name="date[minute]">\n)
    expected << %(<option value="">Choose minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option selected="selected" value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_second" name="date[second]">\n)
    expected << %(<option value="">Choose seconds</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option selected="selected" value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :prompt => true, :include_seconds => true,
      :prompt => {:hour => 'Choose hour', :minute => 'Choose minute', :second => 'Choose seconds'})
	}

	public function test_select_time_with_hidden(){
    expected =  %(<input id="date_first_year" name="date[first][year]" type="hidden" value="2003" />\n)
    expected << %(<input id="date_first_month" name="date[first][month]" type="hidden" value="8" />\n)
    expected << %(<input id="date_first_day" name="date[first][day]" type="hidden" value="16" />\n)
    expected << %(<input id="date_first_hour" name="date[first][hour]" type="hidden" value="8" />\n)
    expected << %(<input id="date_first_minute" name="date[first][minute]" type="hidden" value="4" />\n)

    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :prefix => "date[first]", :use_hidden => true)
    assert_dom_equal expected, select_time(Time::mktime(2003, 8, 16, 8, 4, 18), :time_separator => ":", :prefix => "date[first]", :use_hidden => true)
	}

	public function test_date_select(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}

    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on")
	}

	public function test_date_select_without_day(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = "<input type=\"hidden\" id=\"post_written_on_3i\" name=\"post[written_on(3i)]\" value=\"1\" />\n"

    expected <<  %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :order => [ :month, :year ])
	}

	public function test_date_select_without_day_and_month(){
    @post = Post.new
    @post.written_on = Date.new(2004, 2, 29)

    expected = "<input type=\"hidden\" id=\"post_written_on_2i\" name=\"post[written_on(2i)]\" value=\"2\" />\n"
    expected << "<input type=\"hidden\" id=\"post_written_on_3i\" name=\"post[written_on(3i)]\" value=\"1\" />\n"

    expected << %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :order => [ :year ])
	}

	public function test_date_select_without_day_with_separator(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = "<input type=\"hidden\" id=\"post_written_on_3i\" name=\"post[written_on(3i)]\" value=\"1\" />\n"

    expected <<  %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << "/"

    expected << %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :date_separator => '/', :order => [ :month, :year ])
	}

	public function test_date_select_without_day_and_with_disabled_html_option(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = "<input type=\"hidden\" id=\"post_written_on_3i\" disabled=\"disabled\" name=\"post[written_on(3i)]\" value=\"1\" />\n"

    expected <<  %{<select id="post_written_on_2i" disabled="disabled" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_1i" disabled="disabled" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", { :order => [ :month, :year ] }, :disabled => true)
	}

	public function test_date_select_within_fields_for(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    output_buffer = fields_for :post, @post do |f|
      concat f.date_select(:written_on)
  	}

    expected = "<select id='post_written_on_1i' name='post[written_on(1i)]'>\n<option value='1999'>1999</option>\n<option value='2000'>2000</option>\n<option value='2001'>2001</option>\n<option value='2002'>2002</option>\n<option value='2003'>2003</option>\n<option selected='selected' value='2004'>2004</option>\n<option value='2005'>2005</option>\n<option value='2006'>2006</option>\n<option value='2007'>2007</option>\n<option value='2008'>2008</option>\n<option value='2009'>2009</option>\n</select>\n"
    expected << "<select id='post_written_on_2i' name='post[written_on(2i)]'>\n<option value='1'>January</option>\n<option value='2'>February</option>\n<option value='3'>March</option>\n<option value='4'>April</option>\n<option value='5'>May</option>\n<option selected='selected' value='6'>June</option>\n<option value='7'>July</option>\n<option value='8'>August</option>\n<option value='9'>September</option>\n<option value='10'>October</option>\n<option value='11'>November</option>\n<option value='12'>December</option>\n</select>\n"
    expected << "<select id='post_written_on_3i' name='post[written_on(3i)]'>\n<option value='1'>1</option>\n<option value='2'>2</option>\n<option value='3'>3</option>\n<option value='4'>4</option>\n<option value='5'>5</option>\n<option value='6'>6</option>\n<option value='7'>7</option>\n<option value='8'>8</option>\n<option value='9'>9</option>\n<option value='10'>10</option>\n<option value='11'>11</option>\n<option value='12'>12</option>\n<option value='13'>13</option>\n<option value='14'>14</option>\n<option selected='selected' value='15'>15</option>\n<option value='16'>16</option>\n<option value='17'>17</option>\n<option value='18'>18</option>\n<option value='19'>19</option>\n<option value='20'>20</option>\n<option value='21'>21</option>\n<option value='22'>22</option>\n<option value='23'>23</option>\n<option value='24'>24</option>\n<option value='25'>25</option>\n<option value='26'>26</option>\n<option value='27'>27</option>\n<option value='28'>28</option>\n<option value='29'>29</option>\n<option value='30'>30</option>\n<option value='31'>31</option>\n</select>\n"

    assert_dom_equal(expected, output_buffer)
	}

	public function test_date_select_within_fields_for_with_index(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)
    id = 27

    output_buffer = fields_for :post, @post, :index => id do |f|
      concat f.date_select(:written_on)
  	}

    expected = "<select id='post_#{id}_written_on_1i' name='post[#{id}][written_on(1i)]'>\n<option value='1999'>1999</option>\n<option value='2000'>2000</option>\n<option value='2001'>2001</option>\n<option value='2002'>2002</option>\n<option value='2003'>2003</option>\n<option selected='selected' value='2004'>2004</option>\n<option value='2005'>2005</option>\n<option value='2006'>2006</option>\n<option value='2007'>2007</option>\n<option value='2008'>2008</option>\n<option value='2009'>2009</option>\n</select>\n"
    expected << "<select id='post_#{id}_written_on_2i' name='post[#{id}][written_on(2i)]'>\n<option value='1'>January</option>\n<option value='2'>February</option>\n<option value='3'>March</option>\n<option value='4'>April</option>\n<option value='5'>May</option>\n<option selected='selected' value='6'>June</option>\n<option value='7'>July</option>\n<option value='8'>August</option>\n<option value='9'>September</option>\n<option value='10'>October</option>\n<option value='11'>November</option>\n<option value='12'>December</option>\n</select>\n"
    expected << "<select id='post_#{id}_written_on_3i' name='post[#{id}][written_on(3i)]'>\n<option value='1'>1</option>\n<option value='2'>2</option>\n<option value='3'>3</option>\n<option value='4'>4</option>\n<option value='5'>5</option>\n<option value='6'>6</option>\n<option value='7'>7</option>\n<option value='8'>8</option>\n<option value='9'>9</option>\n<option value='10'>10</option>\n<option value='11'>11</option>\n<option value='12'>12</option>\n<option value='13'>13</option>\n<option value='14'>14</option>\n<option selected='selected' value='15'>15</option>\n<option value='16'>16</option>\n<option value='17'>17</option>\n<option value='18'>18</option>\n<option value='19'>19</option>\n<option value='20'>20</option>\n<option value='21'>21</option>\n<option value='22'>22</option>\n<option value='23'>23</option>\n<option value='24'>24</option>\n<option value='25'>25</option>\n<option value='26'>26</option>\n<option value='27'>27</option>\n<option value='28'>28</option>\n<option value='29'>29</option>\n<option value='30'>30</option>\n<option value='31'>31</option>\n</select>\n"

    assert_dom_equal(expected, output_buffer)
	}

	public function test_date_select_within_fields_for_with_blank_index(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)
    id = null

    output_buffer = fields_for :post, @post, :index => id do |f|
      concat f.date_select(:written_on)
  	}

    expected = "<select id='post_#{id}_written_on_1i' name='post[#{id}][written_on(1i)]'>\n<option value='1999'>1999</option>\n<option value='2000'>2000</option>\n<option value='2001'>2001</option>\n<option value='2002'>2002</option>\n<option value='2003'>2003</option>\n<option selected='selected' value='2004'>2004</option>\n<option value='2005'>2005</option>\n<option value='2006'>2006</option>\n<option value='2007'>2007</option>\n<option value='2008'>2008</option>\n<option value='2009'>2009</option>\n</select>\n"
    expected << "<select id='post_#{id}_written_on_2i' name='post[#{id}][written_on(2i)]'>\n<option value='1'>January</option>\n<option value='2'>February</option>\n<option value='3'>March</option>\n<option value='4'>April</option>\n<option value='5'>May</option>\n<option selected='selected' value='6'>June</option>\n<option value='7'>July</option>\n<option value='8'>August</option>\n<option value='9'>September</option>\n<option value='10'>October</option>\n<option value='11'>November</option>\n<option value='12'>December</option>\n</select>\n"
    expected << "<select id='post_#{id}_written_on_3i' name='post[#{id}][written_on(3i)]'>\n<option value='1'>1</option>\n<option value='2'>2</option>\n<option value='3'>3</option>\n<option value='4'>4</option>\n<option value='5'>5</option>\n<option value='6'>6</option>\n<option value='7'>7</option>\n<option value='8'>8</option>\n<option value='9'>9</option>\n<option value='10'>10</option>\n<option value='11'>11</option>\n<option value='12'>12</option>\n<option value='13'>13</option>\n<option value='14'>14</option>\n<option selected='selected' value='15'>15</option>\n<option value='16'>16</option>\n<option value='17'>17</option>\n<option value='18'>18</option>\n<option value='19'>19</option>\n<option value='20'>20</option>\n<option value='21'>21</option>\n<option value='22'>22</option>\n<option value='23'>23</option>\n<option value='24'>24</option>\n<option value='25'>25</option>\n<option value='26'>26</option>\n<option value='27'>27</option>\n<option value='28'>28</option>\n<option value='29'>29</option>\n<option value='30'>30</option>\n<option value='31'>31</option>\n</select>\n"

    assert_dom_equal(expected, output_buffer)
	}

	public function test_date_select_with_index(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)
    id = 456

    expected = %{<select id="post_456_written_on_1i" name="post[#{id}][written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_456_written_on_2i" name="post[#{id}][written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_456_written_on_3i" name="post[#{id}][written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :index => id)
	}

	public function test_date_select_with_auto_index(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)
    id = 123

    expected = %{<select id="post_123_written_on_1i" name="post[#{id}][written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_123_written_on_2i" name="post[#{id}][written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_123_written_on_3i" name="post[#{id}][written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post[]", "written_on")
	}

	public function test_date_select_with_different_order(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected =  %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"

    expected <<  %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    1999.upto(2009) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2004}>#{i}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :order => [:day, :month, :year])
	}

	public function test_date_select_with_null(){
    @post = Post.new

    start_year = Time.now.year-5
  	}_year   = Time.now.year+5
    expected =   %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    start_year.upto(end_year) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == Time.now.year}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == Time.now.month}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == Time.now.day}>#{i}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on")
	}

	public function test_date_select_with_null_and_blank(){
    @post = Post.new

    start_year = Time.now.year-5
  	}_year   = Time.now.year+5
    expected =   %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << "<option value=\"\"></option>\n"
    start_year.upto(end_year) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << "<option value=\"\"></option>\n"
    1.upto(12) { |i| expected << %(<option value="#{i}">#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << "<option value=\"\"></option>\n"
    1.upto(31) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :include_blank => true)
	}

	public function test_date_select_with_null_and_blank_and_order(){
    @post = Post.new

    start_year = Time.now.year-5
  	}_year   = Time.now.year+5

    expected = '<input name="post[written_on(3i)]" type="hidden" id="post_written_on_3i" value="1"/>' + "\n"
    expected <<   %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << "<option value=\"\"></option>\n"
    start_year.upto(end_year) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << "<option value=\"\"></option>\n"
    1.upto(12) { |i| expected << %(<option value="#{i}">#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :order=>[:year, :month], :include_blank=>true)
	}

	public function test_date_select_with_null_and_blank_and_discard_month(){
    @post = Post.new

    start_year = Time.now.year-5
  	}_year   = Time.now.year+5

    expected = %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << "<option value=\"\"></option>\n"
    start_year.upto(end_year) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"
    expected << '<input name="post[written_on(2i)]" type="hidden" id="post_written_on_2i" value="1"/>' + "\n"
    expected << '<input name="post[written_on(3i)]" type="hidden" id="post_written_on_3i" value="1"/>' + "\n"

    assert_dom_equal expected, date_select("post", "written_on", :discard_month => true, :include_blank=>true)
	}

	public function test_date_select_with_null_and_blank_and_discard_year(){
    @post = Post.new

    expected = '<input id="post_written_on_1i" name="post[written_on(1i)]" type="hidden" value="1" />' + "\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << "<option value=\"\"></option>\n"
    1.upto(12) { |i| expected << %(<option value="#{i}">#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << "<option value=\"\"></option>\n"
    1.upto(31) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :discard_year => true, :include_blank=>true)
	}

	public function test_date_select_cant_override_discard_hour(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :discard_hour => false)
	}

	public function test_date_select_with_html_options(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select class="selector" id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select class="selector" id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select class="selector" id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}

    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", {}, :class => 'selector')
	}

	public function test_date_select_with_html_options_within_fields_for(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    output_buffer = fields_for :post, @post do |f|
      concat f.date_select(:written_on, {}, :class => 'selector')
  	}

    expected = %{<select class="selector" id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select class="selector" id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select class="selector" id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}

    expected << "</select>\n"

    assert_dom_equal expected, output_buffer
	}

	public function test_date_select_with_separator(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}

    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", { :date_separator => " / " })
	}

	public function test_date_select_with_separator_and_order(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", { :order => [:day, :month, :year], :date_separator => " / " })
	}

	public function test_date_select_with_separator_and_order_and_year_discarded(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"
    expected << %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}

    assert_dom_equal expected, date_select("post", "written_on", { :order => [:day, :month, :year], :discard_year => true, :date_separator => " / " })
	}

	public function test_date_select_with_default_prompt(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="">Year</option>\n<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="">Month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="">Day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}

    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :prompt => true)
	}

	public function test_date_select_with_custom_prompt(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    expected = %{<select id="post_written_on_1i" name="post[written_on(1i)]">\n}
    expected << %{<option value="">Choose year</option>\n<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_2i" name="post[written_on(2i)]">\n}
    expected << %{<option value="">Choose month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_written_on_3i" name="post[written_on(3i)]">\n}
    expected << %{<option value="">Choose day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}

    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "written_on", :prompt => {:year => 'Choose year', :month => 'Choose month', :day => 'Choose day'})
	}

	public function test_time_select(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select id="post_written_on_4i" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_5i" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on")
	}

	public function test_time_select_without_date_hidden_fields(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %(<select id="post_written_on_4i" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_5i" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", :ignore_date => true)
	}

	public function test_time_select_with_seconds(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select id="post_written_on_4i" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_5i" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_6i" name="post[written_on(6i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 35}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", :include_seconds => true)
	}

	public function test_time_select_with_html_options(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select class="selector" id="post_written_on_4i" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select class="selector" id="post_written_on_5i" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", {}, :class => 'selector')
	}

	public function test_time_select_with_html_options_within_fields_for(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    output_buffer = fields_for :post, @post do |f|
      concat f.time_select(:written_on, {}, :class => 'selector')
  	}

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select class="selector" id="post_written_on_4i" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select class="selector" id="post_written_on_5i" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, output_buffer
	}

	public function test_time_select_with_separator(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select id="post_written_on_4i" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    expected << " - "

    expected << %(<select id="post_written_on_5i" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    expected << " - "

    expected << %(<select id="post_written_on_6i" name="post[written_on(6i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 35}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", { :time_separator => " - ", :include_seconds => true })
	}

	public function test_time_select_with_default_prompt(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select id="post_written_on_4i" name="post[written_on(4i)]">\n)
    expected << %(<option value="">Hour</option>\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_5i" name="post[written_on(5i)]">\n)
        expected << %(<option value="">Minute</option>\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", :prompt => true)
	}

	public function test_time_select_with_custom_prompt(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select id="post_written_on_4i" name="post[written_on(4i)]">\n)
    expected << %(<option value="">Choose hour</option>\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_5i" name="post[written_on(5i)]">\n)
        expected << %(<option value="">Choose minute</option>\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", :prompt => {:hour => 'Choose hour', :minute => 'Choose minute'})
	}

	public function test_time_select_with_disabled_html_option(){
    @post = Post.new
    @post.written_on = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_written_on_1i" disabled="disabled" name="post[written_on(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_written_on_2i" disabled="disabled" name="post[written_on(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_written_on_3i" disabled="disabled" name="post[written_on(3i)]" value="15" />\n}

    expected << %(<select id="post_written_on_4i" disabled="disabled" name="post[written_on(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %(<select id="post_written_on_5i" disabled="disabled" name="post[written_on(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, time_select("post", "written_on", {}, :disabled => true)
	}

	public function test_datetime_select(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 16, 35)

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option selected="selected" value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at")
	}

	public function test_datetime_select_defaults_to_time_zone_now_when_config_time_zone_is_set(){
    # The love zone is UTC+0
    mytz = Class.new(ActiveSupport::TimeZone) {
      attr_accessor :now
    }.create('tenderlove', 0)

    now       = mktime(16, 35, 0, 6, 15, 2004)
    mytz.now  = now
    Time.zone = mytz

    $this->assertEquals(mytz, Time.zone);

    @post = Post.new

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option selected="selected" value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at")
  ensure
    Time.zone = null
	}

	public function test_datetime_select_with_html_options_within_fields_for(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 16, 35)

    output_buffer = fields_for :post, @post do |f|
      concat f.datetime_select(:updated_at, {}, :class => 'selector')
  	}

    expected = "<select id='post_updated_at_1i' name='post[updated_at(1i)]' class='selector'>\n<option value='1999'>1999</option>\n<option value='2000'>2000</option>\n<option value='2001'>2001</option>\n<option value='2002'>2002</option>\n<option value='2003'>2003</option>\n<option selected='selected' value='2004'>2004</option>\n<option value='2005'>2005</option>\n<option value='2006'>2006</option>\n<option value='2007'>2007</option>\n<option value='2008'>2008</option>\n<option value='2009'>2009</option>\n</select>\n"
    expected << "<select id='post_updated_at_2i' name='post[updated_at(2i)]' class='selector'>\n<option value='1'>January</option>\n<option value='2'>February</option>\n<option value='3'>March</option>\n<option value='4'>April</option>\n<option value='5'>May</option>\n<option selected='selected' value='6'>June</option>\n<option value='7'>July</option>\n<option value='8'>August</option>\n<option value='9'>September</option>\n<option value='10'>October</option>\n<option value='11'>November</option>\n<option value='12'>December</option>\n</select>\n"
    expected << "<select id='post_updated_at_3i' name='post[updated_at(3i)]' class='selector'>\n<option value='1'>1</option>\n<option value='2'>2</option>\n<option value='3'>3</option>\n<option value='4'>4</option>\n<option value='5'>5</option>\n<option value='6'>6</option>\n<option value='7'>7</option>\n<option value='8'>8</option>\n<option value='9'>9</option>\n<option value='10'>10</option>\n<option value='11'>11</option>\n<option value='12'>12</option>\n<option value='13'>13</option>\n<option value='14'>14</option>\n<option selected='selected' value='15'>15</option>\n<option value='16'>16</option>\n<option value='17'>17</option>\n<option value='18'>18</option>\n<option value='19'>19</option>\n<option value='20'>20</option>\n<option value='21'>21</option>\n<option value='22'>22</option>\n<option value='23'>23</option>\n<option value='24'>24</option>\n<option value='25'>25</option>\n<option value='26'>26</option>\n<option value='27'>27</option>\n<option value='28'>28</option>\n<option value='29'>29</option>\n<option value='30'>30</option>\n<option value='31'>31</option>\n</select>\n"
    expected << " &mdash; <select id='post_updated_at_4i' name='post[updated_at(4i)]' class='selector'>\n<option value='00'>00</option>\n<option value='01'>01</option>\n<option value='02'>02</option>\n<option value='03'>03</option>\n<option value='04'>04</option>\n<option value='05'>05</option>\n<option value='06'>06</option>\n<option value='07'>07</option>\n<option value='08'>08</option>\n<option value='09'>09</option>\n<option value='10'>10</option>\n<option value='11'>11</option>\n<option value='12'>12</option>\n<option value='13'>13</option>\n<option value='14'>14</option>\n<option value='15'>15</option>\n<option selected='selected' value='16'>16</option>\n<option value='17'>17</option>\n<option value='18'>18</option>\n<option value='19'>19</option>\n<option value='20'>20</option>\n<option value='21'>21</option>\n<option value='22'>22</option>\n<option value='23'>23</option>\n</select>\n"
    expected << " : <select id='post_updated_at_5i' name='post[updated_at(5i)]' class='selector'>\n<option value='00'>00</option>\n<option value='01'>01</option>\n<option value='02'>02</option>\n<option value='03'>03</option>\n<option value='04'>04</option>\n<option value='05'>05</option>\n<option value='06'>06</option>\n<option value='07'>07</option>\n<option value='08'>08</option>\n<option value='09'>09</option>\n<option value='10'>10</option>\n<option value='11'>11</option>\n<option value='12'>12</option>\n<option value='13'>13</option>\n<option value='14'>14</option>\n<option value='15'>15</option>\n<option value='16'>16</option>\n<option value='17'>17</option>\n<option value='18'>18</option>\n<option value='19'>19</option>\n<option value='20'>20</option>\n<option value='21'>21</option>\n<option value='22'>22</option>\n<option value='23'>23</option>\n<option value='24'>24</option>\n<option value='25'>25</option>\n<option value='26'>26</option>\n<option value='27'>27</option>\n<option value='28'>28</option>\n<option value='29'>29</option>\n<option value='30'>30</option>\n<option value='31'>31</option>\n<option value='32'>32</option>\n<option value='33'>33</option>\n<option value='34'>34</option>\n<option selected='selected' value='35'>35</option>\n<option value='36'>36</option>\n<option value='37'>37</option>\n<option value='38'>38</option>\n<option value='39'>39</option>\n<option value='40'>40</option>\n<option value='41'>41</option>\n<option value='42'>42</option>\n<option value='43'>43</option>\n<option value='44'>44</option>\n<option value='45'>45</option>\n<option value='46'>46</option>\n<option value='47'>47</option>\n<option value='48'>48</option>\n<option value='49'>49</option>\n<option value='50'>50</option>\n<option value='51'>51</option>\n<option value='52'>52</option>\n<option value='53'>53</option>\n<option value='54'>54</option>\n<option value='55'>55</option>\n<option value='56'>56</option>\n<option value='57'>57</option>\n<option value='58'>58</option>\n<option value='59'>59</option>\n</select>\n"

    assert_dom_equal expected, output_buffer
	}

	public function test_datetime_select_with_separators(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << " / "

    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " , "

    expected << %(<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n)
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    expected << " - "

    expected << %(<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    expected << " - "

    expected << %(<select id="post_updated_at_6i" name="post[updated_at(6i)]">\n)
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 35}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", { :date_separator => " / ", :datetime_separator => " , ", :time_separator => " - ", :include_seconds => true })
	}

	public function test_datetime_select_with_integer(){
    @post = Post.new
    @post.updated_at = 3
    datetime_select("post", "updated_at")
	}

	public function test_datetime_select_with_infinity(){ # Float
    @post = Post.new
    @post.updated_at = (-1.0/0)
    datetime_select("post", "updated_at")
	}

	public function test_datetime_select_with_default_prompt(){
    @post = Post.new
    @post.updated_at = null

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %{<option value="">Year</option>\n<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %{<option value="">Month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %{<option value="">Day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    expected << %{<option value="">Hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    expected << %{<option value="">Minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :start_year=>1999, :end_year=>2009,  :prompt => true)
	}

	public function test_datetime_select_with_custom_prompt(){
    @post = Post.new
    @post.updated_at = null

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %{<option value="">Choose year</option>\n<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %{<option value="">Choose month</option>\n<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %{<option value="">Choose day</option>\n<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    expected << %{<option value="">Choose hour</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    expected << %{<option value="">Choose minute</option>\n<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :start_year=>1999, :end_year=>2009, :prompt => {:year => 'Choose year', :month => 'Choose month', :day => 'Choose day', :hour => 'Choose hour', :minute => 'Choose minute'})
	}

	public function test_date_select_with_zero_value_and_no_start_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+1) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :end_year => Date.today.year+1, :prefix => "date[first]")
	}

	public function test_date_select_with_zero_value_and_no_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    last_year = Time.now.year + 5
    2003.upto(last_year) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :start_year => 2003, :prefix => "date[first]")
	}

	public function test_date_select_with_zero_value_and_no_start_and_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(0, :prefix => "date[first]")
	}

	public function test_date_select_with_null_value_and_no_start_and_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_date(null, :prefix => "date[first]")
	}

	public function test_datetime_select_with_null_value_and_no_start_and_end_year(){
    expected =  %(<select id="date_first_year" name="date[first][year]">\n)
    (Date.today.year-5).upto(Date.today.year+5) { |y| expected << %(<option value="#{y}">#{y}</option>\n) }
    expected << "</select>\n"

    expected << %(<select id="date_first_month" name="date[first][month]">\n)
    expected << %(<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n)
    expected << "</select>\n"

    expected << %(<select id="date_first_day" name="date[first][day]">\n)
    expected << %(<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n)
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %(<select id="date_first_hour" name="date[first][hour]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n)
    expected << "</select>\n"

    expected << " : "

    expected << %(<select id="date_first_minute" name="date[first][minute]">\n)
    expected << %(<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n)
    expected << "</select>\n"

    assert_dom_equal expected, select_datetime(null, :prefix => "date[first]")
	}

	public function test_datetime_select_with_options_index(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 16, 35)
    id = 456

    expected = %{<select id="post_456_updated_at_1i" name="post[#{id}][updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_456_updated_at_2i" name="post[#{id}][updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_456_updated_at_3i" name="post[#{id}][updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_456_updated_at_4i" name="post[#{id}][updated_at(4i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_456_updated_at_5i" name="post[#{id}][updated_at(5i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option selected="selected" value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :index => id)
	}

	public function test_datetime_select_within_fields_for_with_options_index(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 16, 35)
    id = 456

    output_buffer = fields_for :post, @post, :index => id do |f|
      concat f.datetime_select(:updated_at)
  	}

    expected = %{<select id="post_456_updated_at_1i" name="post[#{id}][updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_456_updated_at_2i" name="post[#{id}][updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_456_updated_at_3i" name="post[#{id}][updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_456_updated_at_4i" name="post[#{id}][updated_at(4i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_456_updated_at_5i" name="post[#{id}][updated_at(5i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option selected="selected" value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, output_buffer
	}

	public function test_datetime_select_with_auto_index(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 16, 35)
    id = @post.id

    expected = %{<select id="post_123_updated_at_1i" name="post[#{id}][updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_123_updated_at_2i" name="post[#{id}][updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select id="post_123_updated_at_3i" name="post[#{id}][updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_123_updated_at_4i" name="post[#{id}][updated_at(4i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_123_updated_at_5i" name="post[#{id}][updated_at(5i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option selected="selected" value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post[]", "updated_at")
	}

	public function test_datetime_select_with_seconds(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    1999.upto(2009) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2004}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_6i" name="post[updated_at(6i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 35}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :include_seconds => true)
	}

	public function test_datetime_select_discard_year(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_updated_at_1i" name="post[updated_at(1i)]" value="2004" />\n}
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :discard_year => true)
	}

	public function test_datetime_select_discard_month(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    1999.upto(2009) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2004}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<input type="hidden" id="post_updated_at_2i" name="post[updated_at(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_updated_at_3i" name="post[updated_at(3i)]" value="1" />\n}

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :discard_month => true)
	}

	public function test_datetime_select_discard_year_and_month(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_updated_at_1i" name="post[updated_at(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_updated_at_2i" name="post[updated_at(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_updated_at_3i" name="post[updated_at(3i)]" value="1" />\n}

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :discard_year => true, :discard_month => true)
	}

	public function test_datetime_select_discard_year_and_month_with_disabled_html_option(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_updated_at_1i" disabled="disabled" name="post[updated_at(1i)]" value="2004" />\n}
    expected << %{<input type="hidden" id="post_updated_at_2i" disabled="disabled" name="post[updated_at(2i)]" value="6" />\n}
    expected << %{<input type="hidden" id="post_updated_at_3i" disabled="disabled" name="post[updated_at(3i)]" value="1" />\n}

    expected << %{<select id="post_updated_at_4i" disabled="disabled" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" disabled="disabled" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", { :discard_year => true, :discard_month => true }, :disabled => true)
	}

	public function test_datetime_select_discard_hour(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    1999.upto(2009) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2004}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :discard_hour => true)
	}

	public function test_datetime_select_discard_minute(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    1999.upto(2009) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2004}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << %{<input type="hidden" id="post_updated_at_5i" name="post[updated_at(5i)]" value="16" />\n}

    assert_dom_equal expected, datetime_select("post", "updated_at", :discard_minute => true)
	}

	public function test_datetime_select_invalid_order(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    1999.upto(2009) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2004}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :order => [:minute, :day, :hour, :month, :year, :second])
	}

	public function test_datetime_select_discard_with_order(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 15, 16, 35)

    expected = %{<input type="hidden" id="post_updated_at_1i" name="post[updated_at(1i)]" value="2004" />\n}
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 15}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 6}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :order => [:day, :month])
	}

	public function test_datetime_select_with_default_value_as_time(){
    @post = Post.new
    @post.updated_at = null

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    2001.upto(2011) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 2006}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 9}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 19}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 15}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 16}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :default => Time.local(2006, 9, 19, 15, 16, 35))
	}

	public function test_include_blank_overrides_default_option(){
    @post = Post.new
    @post.updated_at = null

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %(<option value=""></option>\n)
    (Time.now.year - 5).upto(Time.now.year + 5) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %(<option value=""></option>\n)
    1.upto(12) { |i| expected << %(<option value="#{i}">#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %(<option value=""></option>\n)
    1.upto(31) { |i| expected << %(<option value="#{i}">#{i}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, date_select("post", "updated_at", :default => Time.local(2006, 9, 19, 15, 16, 35), :include_blank => true)
	}

	public function test_datetime_select_with_default_value_as_hash(){
    @post = Post.new
    @post.updated_at = null

    expected = %{<select id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    (Time.now.year - 5).upto(Time.now.year + 5) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == Time.now.year}>#{i}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    1.upto(12) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == 10}>#{Date::MONTHNAMES[i]}</option>\n) }
    expected << "</select>\n"
    expected << %{<select id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    1.upto(31) { |i| expected << %(<option value="#{i}"#{' selected="selected"' if i == Time.now.day}>#{i}</option>\n) }
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    0.upto(23) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 9}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"
    expected << " : "
    expected << %{<select id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    0.upto(59) { |i| expected << %(<option value="#{sprintf("%02d", i)}"#{' selected="selected"' if i == 42}>#{sprintf("%02d", i)}</option>\n) }
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", :default => { :month => 10, :minute => 42, :hour => 9 })
	}

	public function test_datetime_select_with_html_options(){
    @post = Post.new
    @post.updated_at = Time.local(2004, 6, 15, 16, 35)

    expected = %{<select class="selector" id="post_updated_at_1i" name="post[updated_at(1i)]">\n}
    expected << %{<option value="1999">1999</option>\n<option value="2000">2000</option>\n<option value="2001">2001</option>\n<option value="2002">2002</option>\n<option value="2003">2003</option>\n<option selected="selected" value="2004">2004</option>\n<option value="2005">2005</option>\n<option value="2006">2006</option>\n<option value="2007">2007</option>\n<option value="2008">2008</option>\n<option value="2009">2009</option>\n}
    expected << "</select>\n"

    expected << %{<select class="selector" id="post_updated_at_2i" name="post[updated_at(2i)]">\n}
    expected << %{<option value="1">January</option>\n<option value="2">February</option>\n<option value="3">March</option>\n<option value="4">April</option>\n<option value="5">May</option>\n<option selected="selected" value="6">June</option>\n<option value="7">July</option>\n<option value="8">August</option>\n<option value="9">September</option>\n<option value="10">October</option>\n<option value="11">November</option>\n<option value="12">December</option>\n}
    expected << "</select>\n"

    expected << %{<select class="selector" id="post_updated_at_3i" name="post[updated_at(3i)]">\n}
    expected << %{<option value="1">1</option>\n<option value="2">2</option>\n<option value="3">3</option>\n<option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option selected="selected" value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n}
    expected << "</select>\n"

    expected << " &mdash; "

    expected << %{<select class="selector" id="post_updated_at_4i" name="post[updated_at(4i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option selected="selected" value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n}
    expected << "</select>\n"
    expected << " : "
    expected << %{<select class="selector" id="post_updated_at_5i" name="post[updated_at(5i)]">\n}
    expected << %{<option value="00">00</option>\n<option value="01">01</option>\n<option value="02">02</option>\n<option value="03">03</option>\n<option value="04">04</option>\n<option value="05">05</option>\n<option value="06">06</option>\n<option value="07">07</option>\n<option value="08">08</option>\n<option value="09">09</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n<option value="13">13</option>\n<option value="14">14</option>\n<option value="15">15</option>\n<option value="16">16</option>\n<option value="17">17</option>\n<option value="18">18</option>\n<option value="19">19</option>\n<option value="20">20</option>\n<option value="21">21</option>\n<option value="22">22</option>\n<option value="23">23</option>\n<option value="24">24</option>\n<option value="25">25</option>\n<option value="26">26</option>\n<option value="27">27</option>\n<option value="28">28</option>\n<option value="29">29</option>\n<option value="30">30</option>\n<option value="31">31</option>\n<option value="32">32</option>\n<option value="33">33</option>\n<option value="34">34</option>\n<option selected="selected" value="35">35</option>\n<option value="36">36</option>\n<option value="37">37</option>\n<option value="38">38</option>\n<option value="39">39</option>\n<option value="40">40</option>\n<option value="41">41</option>\n<option value="42">42</option>\n<option value="43">43</option>\n<option value="44">44</option>\n<option value="45">45</option>\n<option value="46">46</option>\n<option value="47">47</option>\n<option value="48">48</option>\n<option value="49">49</option>\n<option value="50">50</option>\n<option value="51">51</option>\n<option value="52">52</option>\n<option value="53">53</option>\n<option value="54">54</option>\n<option value="55">55</option>\n<option value="56">56</option>\n<option value="57">57</option>\n<option value="58">58</option>\n<option value="59">59</option>\n}
    expected << "</select>\n"

    assert_dom_equal expected, datetime_select("post", "updated_at", {}, :class => 'selector')
	}

	public function test_date_select_should_not_change_passed_options_hash(){
    @post = Post.new
    @post.updated_at = Time.local(2008, 7, 16, 23, 30)

    options = {
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }
    date_select(@post, :updated_at, options)

    # note: the literal hash is intentional to show that the actual options hash isn't modified
    #       don't change this!
    assert_equal({
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }, options)
	}

	public function test_datetime_select_should_not_change_passed_options_hash(){
    @post = Post.new
    @post.updated_at = Time.local(2008, 7, 16, 23, 30)

    options = {
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }
    datetime_select(@post, :updated_at, options)

    # note: the literal hash is intentional to show that the actual options hash isn't modified
    #       don't change this!
    assert_equal({
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }, options)
	}

	public function test_time_select_should_not_change_passed_options_hash(){
    @post = Post.new
    @post.updated_at = Time.local(2008, 7, 16, 23, 30)

    options = {
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }
    time_select(@post, :updated_at, options)

    # note: the literal hash is intentional to show that the actual options hash isn't modified
    #       don't change this!
    assert_equal({
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }, options)
	}

	public function test_select_date_should_not_change_passed_options_hash(){
    options = {
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }
    select_date(Date.today, options)

    # note: the literal hash is intentional to show that the actual options hash isn't modified
    #       don't change this!
    assert_equal({
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }, options)
	}

	public function test_select_datetime_should_not_change_passed_options_hash(){
    options = {
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }
    select_datetime(Time.now, options)

    # note: the literal hash is intentional to show that the actual options hash isn't modified
    #       don't change this!
    assert_equal({
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }, options)
	}

	public function test_select_time_should_not_change_passed_options_hash(){
    options = {
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }
    select_time(Time.now, options)

    # note: the literal hash is intentional to show that the actual options hash isn't modified
    #       don't change this!
    assert_equal({
      :order => [ :year, :month, :day ],
      :default => { :year => 2008, :month => 7, :day => 16, :hour => 23, :minute => 30, :second => 1 },
      :discard_type => false,
      :include_blank => false,
      :ignore_date => false,
      :include_seconds => true
    }, options)
	}

	public function test_select_html_safety(){
    assert select_day(16).html_safe?
    assert select_month(8).html_safe?
    assert select_year(Time::mktime(2003, 8, 16, 8, 4, 18)).html_safe?
    assert select_minute(Time::mktime(2003, 8, 16, 8, 4, 18)).html_safe?
    assert select_second(Time::mktime(2003, 8, 16, 8, 4, 18)).html_safe?

    assert select_minute(8, :use_hidden => true).html_safe?
    assert select_month(8, :prompt => 'Choose month').html_safe?

    assert select_time(Time::mktime(2003, 8, 16, 8, 4, 18), {}, :class => 'selector').html_safe?
    assert select_date(Time::mktime(2003, 8, 16, 0, 0, 0), :date_separator => " / ", :start_year => 2003, :end_year => 2005, :prefix => "date[first]").html_safe?
	}

	public function test_object_select_html_safety(){
    @post = Post.new
    @post.written_on = Date.new(2004, 6, 15)

    assert date_select("post", "written_on", :default => Time.local(2006, 9, 19, 15, 16, 35), :include_blank => true).html_safe?
    assert time_select("post", "written_on", :ignore_date => true).html_safe?
	}

	public function test_time_tag_with_date(){
    date = Date.today
    expected = "<time datetime=\"#{date.rfc3339}\">#{I18n.l(date, :format => :long)}</time>"
    $this->assertEquals(expected, time_tag(date));
	}

	public function test_time_tag_with_time(){
    time = Time.now
    expected = "<time datetime=\"#{time.xmlschema}\">#{I18n.l(time, :format => :long)}</time>"
    $this->assertEquals(expected, time_tag(time));
	}

	public function test_time_tag_pubdate_option(){
    assert_match(/<time.*pubdate="pubdate">.*<\/time>/, time_tag(Time.now, :pubdate => true))
	}

	public function test_time_tag_with_given_text(){
    assert_match(/<time.*>Right now<\/time>/, time_tag(Time.now, 'Right now'))
	}

	public function test_time_tag_with_given_block(){
    assert_match(/<time.*><span>Right now<\/span><\/time>/, time_tag(Time.now){ '<span>Right now</span>'.html_safe })
	}

	public function test_time_tag_with_different_format(){
    time = Time.now
    expected = "<time datetime=\"#{time.xmlschema}\">#{I18n.l(time, :format => :short)}</time>"
    $this->assertEquals(expected, time_tag(time, :format => :short));
	}

  protected
  	public function with_env_tz(){(new_tz = 'US/Eastern')
      old_tz, ENV['TZ'] = ENV['TZ'], new_tz
      yield
    ensure
      old_tz ? ENV['TZ'] = old_tz : ENV.delete('TZ')
  	}
	*/
}
