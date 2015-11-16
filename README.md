# Docblock Type Normalizer [ ![Travis Build Status](https://travis-ci.org/mfn/php-docblock-normalize.svg?branch=master)](https://travis-ci.org/mfn/php-docblock-normalize)

Homepage: https://github.com/mfn/php-docblock-normalize

# Blurb

This library extracts docblocks from source files and normalizes all type
descriptions within. That is, they're turned into full qualified symbol names
taking the current `namespace` and `use`-aliases into consideration.

# Requirements

PHP 5.6

# Install

Using composer: `composer.phar require mfn/docblock-normalize 0.1`

# Example

`sample.php`:
```PHP
<?php namespace Foo;
use Bar as Baz;
/**
 * @param Foo $param1
 * @param Bar $param2
 * @param Baz $param3
 * @param \Foo $param4
 * @param string $param5
 */
```
Parse and output the docblock with its types normalized:
```PHP
$parser = new \Mfn\DocblockNormalize\Parser(
    new \Mfn\DocblockNormalize\TokenParser
);

$docblocks = $parser->parseFile('sample.php');

echo $docblocks[0]->getNormalizedContent();
```
Will return:
```
/**
 * @param Foo\Foo $param1
 * @param Foo\Bar $param2
 * @param Bar $param3
 * @param Foo $param4
 * @param string $param5
 */
```

**Note:** `namespace` and `use` statements for the docblock must appear
**before** it!

**Note 2:** the normalization also removes leading backslashes, this is expected!

# Contribute

Fork it, hack on a feature branch, create a pull request, be awesome!

No developer is an island so adhere to these standards:

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)

© Markus Fischer <markus@fischer.name>
