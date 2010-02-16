# PHP I18n #

by Tom Rochette  
<roctom@gmail.com>  
<http://www.tomrochette.com>  

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

### Installing required PEAR package ###
pear channel-discover pear.symfony-project.com  
pear install symfony/YAML

## Features ##

- Internationalization similar to I18n found in Ruby on Rails I18n.
- PHP can be used within the .yml/.php locales files.

## Usage ##
