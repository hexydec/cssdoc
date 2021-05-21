# \__construct()

Called when a new CSSdoc object is created.

```php
$doc = new \hexydec\css\cssdoc($config);
```

## Arguments

### `$config`

A optional array of configuration options that will be merged recursively with the default configuration. The available options and their defaults are:

| Option		| Description													| Default																|
|---------------|---------------------------------------------------------------|-----------------------------------------------------------------------|
| `nested`		| An array of at-directives that are expected to contain nested rules | [`@media`, `@supports`, `@keyframes`, `@-webkit-keyframes`, `@-moz-keyframes`, `@-o-keyframes`] |
| `spaced`		| An array of properties, sub-properties, or at-directives that must have their direct values delimited with spaces | [`calc`]			|
| `quoted`		| An array of properties, sub-properties, or at-directives that must remain quoted | [`content`, `format`, `counters`, `@charset`]		|
| `casesensitive` | An array of properties, sub-properties, or at-directives who's values are case-sensitive | [`url`]									|
| `none`		| An array of properties where the value `none` can be represented as `0` | [`border`, `outline`]										|
| `colors`		| An array of replacement colour Values							| [`#f0ffff` => `azure`, `#f5f5dc` => `beige`, `#ffe4c4` => `bisque`, `#a52a2a` => `brown`, `#ff7f50` => `coral`, `#ffd700` => `gold`, `#008000` => `green`, `#808080` => `grey`, `#4b0082` => `indigo`, `#fffff0` => `ivory`, `#f0e68c` => `khaki`, `#faf0e6` => `linen`, `#000080` => `navy`, `#808000` => `olive`, `#ffa500` => `orange`, `#da70d6` => `orchid`, `#cd853f` => `peru`, `#ffc0cb` => `pink`, `#dda0dd` => `plum`, `#f00` => `red`, `#fa8072` => `salmon`, `#a0522d` => `sienna`, `#c0c0c0` => `silver`, `#fffafa` => `snow`, `#d2b48c` => `tan`, `#008080` => `teal`, `#ff6347` => `tomato`, `#ee82ee` => `violet`, `#f5deb3` => `wheat`] |

## Returns

A new CSSdoc object.
