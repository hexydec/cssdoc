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
			<td><code>semicolons</code></td>
			<td>Boolean</td>
			<td>Removes the semi-colon from the last property of each rule</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>zerounits</code></td>
			<td>Boolean</td>
			<td>Removes the unit specification from values that are 0</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>leadingzeros</code></td>
			<td>Boolean</td>
			<td>Removes the leading zero from decimal value < 0</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>trailingzeros</code></td>
			<td>Boolean</td>
			<td>Removes any trailing 0's from fractional values (74.0 => 74)</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>decimalplaces</code></td>
			<td>Integer</td>
			<td>Maximum number of decimal places for a value</td>
			<td><code>4</code></td>
		</tr>
		<tr>
			<td><code>quotes</code></td>
			<td>Boolean</td>
			<td>Removes quotes where they are not required</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>convertquotes</code></td>
			<td>Boolean</td>
			<td>Converts quotes to double quotes</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>colors</code></td>
			<td>Boolean</td>
			<td>Shortens hexidecimal colours to 3 chars where possible, and replaces colour values with their name where shorter</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>time</code></td>
			<td>Boolean</td>
			<td>Shortens time values where possible e.g. <code>500ms</code> becomes <code>.5s</code></td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>fontweight</code></td>
			<td>Boolean</td>
			<td>Shortens <code>normal</code> and <code>bold</code> to <code>400</code> and <code>700</code> in the <code>font-weight</code> property</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>none</code></td>
			<td>Boolean</td>
			<td>Replaces <code>none</code> with <code>0</code> where possible</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>lowerproperties</code></td>
			<td>Boolean</td>
			<td>Lowercases property names</td>
			<td><code>true</code></td>
		</tr>
		<tr>
			<td><code>lowervalues</code></td>
			<td>Boolean</td>
			<td>Lowercases property values where possible</td>
			<td><code>true</code></td>
		</tr>
	</tbody>
</table>

## Returns

`void`
