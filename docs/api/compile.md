# compile()

Compile the document into a CSS string and save to the specified location, or return as a string.

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->load($css)) {
	$doc->compile($options);
}
```

## Arguments

<table>
	<thead>
		<tr>
			<th>Parameter</th>
			<th>Type</th>
			<th>Description</th>
			<th>Default</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><code>output</code></td>
			<td>String</td>
			<td>Defines the output mode, either <code>minify</code> or <code>beautify</code></td>
			<td><code>minify</code></td>
		</tr>
	</tbody>
</table>

## Returns

Returns the rendered CSS as a string.
