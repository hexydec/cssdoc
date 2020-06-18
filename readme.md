# CSSDoc: PHP CSS Document Parser and Minifier

A tokeniser based CSS document parser and minifier, written in PHP.

![Licence](https://img.shields.io/badge/Licence-MIT-lightgrey.svg)

** This project is currently in beta, so you should test your implementation thoroughly before deployment **

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
	echo $doc->compiile();
}
```

## Support

CSSdoc supports PHP version 7.3+.

## Contributing

If you find an issue with CSSdoc, please create an issue in the tracker.

If you wish to fix an issues yourself, please fork the code, fix the issue, then create a pull request, and I will evaluate your submission.

## Licence

The MIT License (MIT). Please see [License File](LICENCE) for more information.
