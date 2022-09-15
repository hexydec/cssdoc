# CSSDoc: PHP CSS Document Parser and Minifier

A tokeniser based CSS document parser and minifier, written in PHP.

![Licence](https://img.shields.io/badge/Licence-MIT-lightgrey.svg)
![Status: Stable](https://img.shields.io/badge/Status-Stable-Green.svg)
[![Tests Status](https://github.com/hexydec/cssdoc/actions/workflows/tests.yml/badge.svg)](https://github.com/hexydec/cssdoc/actions/workflows/tests.yml)
[![Code Coverage](https://codecov.io/gh/hexydec/cssdoc/branch/master/graph/badge.svg)](https://app.codecov.io/gh/hexydec/cssdoc)

## Description

A CSS parser, primarily designed for minifying CSS documents.

The parser designed around a tokeniser to make the document processing more reliable than regex based minifiers, which are a bit blunt and can be problematic if they match patterns in the wrong places.

## Usage

To minify a CSS document:

```php
$doc = new \hexydec\css\cssdoc();

// load from a variable
if ($doc->load($css) {

	// minify the document
	$doc->minify();

	// compile back to CSS
	echo $doc->compile();
}
```

You can test out the minifier online at [https://hexydec.com/cssdoc/](https://hexydec.com/cssdoc/), or run the supplied index.php file after installation.

## Installation

The easiest way to get up and running is to use composer:

```
$ composer install hexydec/cssdoc
```

CSSdoc requires [\hexydec\token\tokenise](https://github.com/hexydec/tokenise) to run, which you can install manually if not using composer.

## Documentation

- [How it works](docs/how-it-works.md)
- [How to use and examples](docs/how-to-use.md)
- [API Reference](docs/api/readme.md)
- [Object Performance](docs/performance.md)

## Support

CSSdoc supports PHP version 7.4+.

## Contributing

If you find an issue with CSSdoc, please create an issue in the tracker.

If you wish to fix an issues yourself, please fork the code, fix the issue, then create a pull request, and I will evaluate your submission.

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.
