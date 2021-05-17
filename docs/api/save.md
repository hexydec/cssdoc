# save()

Compile the document into a CSS string and save to the specified location, or return as a string.

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->load($css)) {
	$doc->save($file, $options);
}
```

## Arguments

### `$file`

The location to save the CSS, or <code>null</code> to return the CSS as a string.

### `$options`

See the [`compile` method](./compile.md).

## Returns

Returns the CSS document as a string if `$file` is null, or `true` if the file was successfully saved to the specified file. On error the method will return `false`.
