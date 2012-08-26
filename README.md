phpctags
========

An enhanced php [ctags](http://ctags.sourceforge.net/) index file generator
compatible with http://ctags.sourceforge.net/FORMAT.

Using [PHP_Parse](https://github.com/nikic/PHP-Parser) as PHP syntax parsing
backend, written in pure PHP. The generated ctags index file contains scope
and access information about class's methods and properties.

This tool was originally developed to enhance the PHP syntax outline surport
for vim [tagbar](http://majutsushi.github.com/tagbar/) plugin. The enhaced
functionality has been included into an addon plugin for tagbar as
[tagbar-phpctags](https://github.com/techlivezheng/tagbar-phpctags).

Enjoy!

Installation
------------

We use [composer](http://getcomposer.org/) for dependency management, run the
following commands under the project directory to get composer and install the
dependency.

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

See [phpctags on packagist](http://packagist.org/packages/techlivezheng/phpctags)
for more details.

Requirements
------------

* PHP CLI 5.3+
* [PHP-Parser](https://github.com/nikic/PHP-Parser)

Acknowledgements
----------------

* [Snapi](https://github.com/sanpii) for composer support.
