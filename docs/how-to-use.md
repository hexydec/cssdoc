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
