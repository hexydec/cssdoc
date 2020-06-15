# CSSdoc: How to Use

CSSdoc has been designed to be as simple to use as possible, but with enough configuration options to control the output more closely.

## Configuring CSSdoc

CSSdoc is configured only through the minify and output options, there is not other configuration.

## Loading CSS

CSS can be loaded in two ways, either from a string, or from a stream:

### From a String

```php
$css = '#test {
	font-weight: bold;
	color: red;
}'; // can be a snippet
$charset = mb_internal_encoding(); // UTF-8?

$doc = new \hexydec\css\cssdoc();
if ($doc->load($html, $charset)) {
	// do something
}
```

### From a Stream

```php
$url = 'https://github.githubassets.com/assets/github-12ad3ce380b8369cc49199a0e1805f6c.css';
$context = stream_context_create([
	'http' => [
		'user-agent' => 'My CSS Bot 1.0 (Mozilla Compatible)',
		'timeout' => 10
	]
]);

$doc = new \hexydec\css\cssdoc();
if ($doc->open($url, $context, $error)) {
	// do something
} else {
	trigger_error('Could not parse CSS: '.$error, E_USER_WARNING);
}
```

For more information, see the API documentation for the [`load()` method](api/load.md) and the [`open()` method](api/open.md).

## Finding Elements and Extracting Information

You can use standard CSS selectors to query an HTMLdoc object after you have loaded some HTML:

```php
$url = 'https://github.com/hexydec';

$doc = new \hexydec\html\htmldoc();
if ($doc->open($url, $context, $error)) {

	// make a new HTMLdoc containg just this node
	$name = $doc->find(".vcard-fullname");

	// echo my name
	echo $name->text();

	// extract the HTML
	echo $name->html();

	// get the value of an attribute
	echo $name->attr("itemprop"); // = "name"
} else {
	trigger_error('Could not parse HTML: '.$error, E_USER_WARNING);
}
```

For more information, [see the API documentation](api/readme.md).

## Minifying Documents

When minifying documents, CSSdoc updates the internal representation of the document and some of the output settings. When the document is saved, the generated code will then be smaller.

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->load($css)) {
	$doc->minify(); // just run the minify method
	echo $doc->save();
}
```

The `minify()` method can also accept an array of minification options to change what optimisations are performed.

To see all the available options [see the API documentation](api/minify.md).

## Outputting Documents

CSS can be rendered in the following ways from your CSSdoc object:

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->load($html)) {

	// output as a string
	echo $doc->compile();

	// output as a string with charset conversion
	echo $doc->save(null, 'iso-8859-1');

	// save to a file, optionally convert the charset
	$file = __DIR__.'/output/file.html';
	if ($doc->save($file, 'iso-8859-1')) {
		// do something when it is saved
	}

}
```
For a full description of the methods above and to see all the available options [see the API documentation](api/readme.md).
