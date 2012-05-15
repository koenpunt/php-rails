# PHP I18n #

by Tom Rochette
<roctom@gmail.com>  
<http://www.jolteon.net/projects/php-i18n>

Optimizalization, actualization, localization and pluralization by Koen Punt
<koen@koen.pt>
<http://www.koen.pt>

## Introduction ##
A brief summarization of what I18n is:

> In computing, internationalization and localization (also spelled internationalisation and localisation, see spelling differences)
> are means of adapting computer software to different languages and regional differences. Internationalization is the process of
> designing a software application so that it can be adapted to various languages and regions without engineering changes.
> Localization is the process of adapting internationalized software for a specific region or language by adding locale-specific
> components and translating text.
> Source : [Wikipedia](http://en.wikipedia.org/wiki/Internationalization_and_localization)

This implementation is inspired and thus borrows heavily from Ruby on Rails' I18n.  
Ruby/Rails programming conventions have been maintained as much as possible. Deviation is due to language differences.

## Minimum Requirements ##

* PHP 5.3+
* Symfony PEAR Yaml Parser

## Installation ##

Setup is very easy and straight-forward. Essentially, you have to point to the locales folder, and that's pretty much it.

You can use _I18n::push\_load\_path(__PATH\_TO\_LOCALE__)_ to load a specific file, such as

    I18n::push_load_path('/home/myapp/locales/fr.yml');

### Installing required PEAR package ###
pear channel-discover pear.symfony-project.com  
pear install symfony/YAML

## Features ##

- Internationalization and localization similar to I18n found in Ruby on Rails I18n.
- PHP can be used within the .yml/.php locales files.

## Usage ##

### Translations ###
Usage is similar to how you would use I18n in Rails.

    I18n::translate('hello'); // Hello
    I18n::translate('hello', array('locale' => 'fr')); // Bonjour
    I18n::translate('hello_to', array('name' => 'Tom')); // Hello Tom
    I18n::translate('hello_to', array('locale' => 'fr', 'name' => 'Tom')); // Bonjour Tom

You can also use the shorthand `I18n::t('hello');`

In order to differentiate symbols from strings, we have decided to use
to_sym (like the object.to_sym in Rails) to specify it as a symbol.

To use a symbol (which will be resolved), you simply do

    I18n::translate('hello', array('default' => to_sym('hi'));	// if hello doesn't exist, the translation of hi is returned

If you do not specifically use the to_sym() function, you'll be returning the string you passed

    I18n::translate('hello', array('default' => 'Hello');	// if hello doesn't exist, Hello is returned

Using symbols is only necessary when specifying default messages. If you specify an array of defaults messages to default to, you'd do something like

    $defaults = array(to_sym('model.A'), to_sym('model.B'), 'Model A');
    I18n::translate('hello', array('default' => $defaults);	// if hello doesn't exist, try to translate model.A, then model.B and if nothing is found, return string 'Model A'
    
### Localizations ###

Usage is similar to translate, except that the first parameter has to be an instance of `I18n\Date`, `I18n\Time` or `I18n\DateTime`

    I18n::localize(Time::utc(2004, 6, 6, 21, 45, 0)); // Sun, 06 Jun 2004 21:45:00 +0200
    I18n::localize(Time::utc(2004, 6, 6, 21, 45, 0), array('format' => '%R')); // 21:45
    I18n::localize(new Date('2004/06/06')); // 2004-06-06 
    I18n::localize(new Date('2004/06/06'), array('format' => '%Y')); // 2004

You can also use the shorthand `I18n::l(Time::utc());





