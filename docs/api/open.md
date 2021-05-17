# open()

Open a CSS document from a URL.

*Note the charset of the document is determined by the `charset` directive of the `Content-Type` header. If the header is not present, the charset will be detected using the method described in the [`load()` method](load.md).*

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->open($url, $context = null, &$error = null)) {
	// do something
}

// or if want the output
$doc = new \hexydec\css\cssdoc();
if (($css = $doc->open($url, $context = null, &$error = null)) !== false) {
	// do something
}
```

## Arguments

| Parameter	| Type		| Description 									|
|-----------|-----------|-----------------------------------------------|
| `$url`	| String 	| The URL of the CSS document to be opened		|
| `$context`| Resource 	| A stream context resource created with stream_context_create()	|
| `$error`	| String	| A reference to a description of any error that is generated.	|

## Returns

A string containing the CSS that was loaded, or `false` when the requested file could not be loaded.
