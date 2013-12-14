phpctags
========

An enhanced php [ctags](http://ctags.sourceforge.net/) index file generator
compatible with http://ctags.sourceforge.net/FORMAT.

Using [PHP_Parser](https://github.com/nikic/PHP-Parser) as PHP syntax parsing
backend, written in pure PHP. The generated ctags index file contains scope
and access information about class's methods and properties.

This tool was originally developed to enhance the PHP syntax outline surport
for vim [tagbar](http://majutsushi.github.com/tagbar/) plugin. The enhaced
functionality has been included into an addon plugin for tagbar as
[tagbar-phpctags](https://github.com/techlivezheng/tagbar-phpctags).

Enjoy!

Installation
------------

> We currently only support building PHAR executable for \*nix like platform
which provides `make` utility. If you are interested in building an executable
for other platform, especially for Windows, please help yourself out. It
should be easy though (Apologize for not being able to provide any help for
this, I am really not a Windows guy), it also would be great if someone could
provide a patch for this.

Installation is simple, make sure you have PHP's PHAR extension enabled, then
just run `make` in the root directory of the source, you will get a `phpctags`
PHAR executable, add it to your `$PATH`, then you can invoke `phpcatgs`
directly from anywhere.

See [phpctags on packagist](http://packagist.org/packages/techlivezheng/phpctags)
for more details.

Requirements
------------

* PHP CLI 5.3+
* [PHP-Parser](https://github.com/nikic/PHP-Parser)

Acknowledgements
----------------

* [Snapi](https://github.com/sanpii) for composer support.
* [DeMarko](https://github.com/DeMarko) for memory limit support.
* [Sander Marechal](https://github.com/sandermarechal) for improve console support
* [Mark Wu](https://github.com/markwu) for building a stand-alone PHAR executable
