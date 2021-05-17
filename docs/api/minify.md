# minify()

Minifies the CSS document with the inputted or default options.

```php
$doc = new \hexydec\css\cssdoc();
if ($doc->load($html)) {
	$doc->minify($options);
}
```

## Arguments

### `$options`

An optional array contains a list of configuration parameters to configure the minifier output, the options are as follows and are recursively merged with the default config:

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
			<td><code>removesemicolon</code></td>
			<td>Boolean</td>
			<td>Removes the semi-colon from the last property of each rule</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>removezerounits</code></td>
			<td>Boolean</td>
			<td>Removes the unit specification from values that are 0</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>removeleadingzero</code></td>
			<td>Boolean</td>
			<td>Removes the leading zero from decimal value < 0</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>convertquotes</code></td>
			<td>Boolean</td>
			<td>Converts quotes to double quotes</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>removequotes</code></td>
			<td>Boolean</td>
			<td>Removes quotes where they are not required</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>shortenhex</code></td>
			<td>Boolean</td>
			<td>Shortens hexidecimal colours to 3 or 2 chars where possible</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>lowerproperties</code></td>
			<td>Boolean</td>
			<td>Lowercase property names</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>lowervalues</code></td>
			<td>Boolean</td>
			<td>Lowercase property values where possible</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>email</code></td>
			<td>Boolean</td>
			<td>Makes the output email safe (Currently this sets the `shortenhex` setting to false)</td>
			<td><code>false</code></td>
		</tr>
	</tbody>
</table>

## Returns

`void`
