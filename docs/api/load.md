# load()

Loads the input CSS as a document.

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->load($css, $charset = null)) {
	// do something
}
```

## Arguments

| Parameter	| Type		| Description 											|
|-----------|-----------|-------------------------------------------------------|
| `$css`	| String	| The CSS to be parsed into the object					|
| `$charset`| String	| The charset of the document, or `null` to auto-detect |

## Auto-detecting the Charset

If `$charset` is set to null, the program will attempt to auto-detect the charset by looking for:

`@charset "xxx"`

Where the charset will be extracted from, otherwise the charset will be detected using `mb_detect_encoding`.

## Returns

A boolean indicating whether the CSS was parsed successfully.
