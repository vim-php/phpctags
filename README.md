# phpctags

An enhanced php [ctags](http://ctags.sourceforge.net/) index file generator
compatible with http://ctags.sourceforge.net/FORMAT.

Using [PHP_Parser](https://github.com/nikic/PHP-Parser) as PHP syntax parsing
backend, written in pure PHP. The generated ctags index file contains scope
and access information about classes' methods and properties.

This tool was originally developed to enhance the PHP syntax outline surport
for vim [tagbar](http://majutsushi.github.com/tagbar/) plugin. The enhanced
functionality has been included into an addon plugin for tagbar as
[tagbar-phpctags](https://github.com/techlivezheng/tagbar-phpctags).

Enjoy!

## Download and installation

```
curl -Ss https://raw.githubusercontent.com/vim-php/phpctags/gh-pages/install/phpctags.phar > phpctags
chmod +x phpctags
```

Optionally one can move it into a directory on the `$PATH`:

```
sudo mv phpctags /usr/local/bin/
```

## Usage

Single file:

```
phpctags phpfile.php
```

Tags will be written to a `tags` file. In order to specify a different tags file
use the `-f` option:

```
phpctags -f myphp.tags phpfile.php
```

Directory with recursive option:

```
phpctags -f myphp.tags -R target_directory
```

## Build

> We currently only support building PHAR executable for \*nix like platform
> which provides `make` utility. If you are interested in building an executable
> for other platform, especially for Windows, please help yourself out. It
> should be easy though (Apologize for not being able to provide any help for
> this, I am really not a Windows guy), it also would be great if someone could
> provide a patch for this.

Installation is straightforward, make sure you have PHP's PHAR extension enabled,
then run `make` in the root directory of the source, you will get a `phpctags`
PHAR executable, add it to your `$PATH`, then you can invoke `phpctags`
directly from anywhere.

See [phpctags on packagist](http://packagist.org/packages/techlivezheng/phpctags)
for more details.

Requirements
------------

* PHP CLI 7.0+
* [PHP-Parser](https://github.com/nikic/PHP-Parser)

Acknowledgements
----------------

* [Snapi](https://github.com/sanpii) for composer support.
* [DeMarko](https://github.com/DeMarko) for memory limit support.
* [Sander Marechal](https://github.com/sandermarechal) for improve console support.
* [Mark Wu](https://github.com/markwu) for building a stand-alone PHAR executable.
* [InFog](https://github.com/InFog) for maintaining the project since end of 2019.
