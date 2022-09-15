<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class cssdoc implements \ArrayAccess, \Iterator {

	/**
	 * @var array<string> $tokens Regexp components keyed by their corresponding codename for tokenising HTML
	 */
	protected static array $tokens = [
	   'whitespace' => '\s++',
	   'comment' => '\\/\\*[\d\D]*?\\*\\/',
	   'quotes' => '(?<!\\\\)(?:"(?:[^"\\\\]++|\\\\.)*+"|\'(?:[^\'\\\\]++|\\\\.)*+\')',
	   'comparison' => '[\\^*$<>]?=', // comparison operators for media queries or attribute selectors
	   'join' => '[>+~*\\/]|-(?!-)',
	   'curlyopen' => '{',
	   'curlyclose' => '}',
	   'squareopen' => '\\[',
	   'squareclose' => '\\]',
	   'bracketopen' => '\\(',
	   'bracketclose' => '\\)',
	   'comma' => ',',
	   'colon' => ':',
	   'semicolon' => ';',
	   'directive' => '(?<!\\\\)@[a-z-]++',
	   'important' => '!important\b',
	   'datauri' => 'data:[^\\s)]++',
	   'string' => '(?:[^\\/\\[\\]{}\\(\\):;,\\*>+~\\^$!"\' \\n\\r\\t]++|\\\\.)',
	];

	/**
	 * @var array<array> $config Object configuration array
	 */
	protected array $config = [
		'nested' => ['@media', '@supports', '@keyframes', '@-webkit-keyframes', '@-moz-keyframes', '@-o-keyframes', '@document', '@-moz-document', '@container'], // directive that can have nested rules
		'spaced' => ['calc'], // values where spaces between operators must be retained
		'quoted' => ['content', 'format', 'counters', '@charset', 'syntax', 'font-feature-settings', '-webkit-font-feature-settings', '-moz-font-feature-settings', 'quotes', 'text-overflow'], // directives or properties where the contained values must be quoted
		'casesensitive' => ['url'], // property values that should not be lowercased
		'none' => ['border', 'background', 'outline'], // properties that can be changed to 0 when none
		'multiples' => ['margin', 'padding', 'border-width', 'border-style', 'border-color', 'border-radius'],
		'colors' => [
			'#f0ffff' => 'azure',
			'#f5f5dc' => 'beige',
			'#ffe4c4' => 'bisque',
			'#a52a2a' => 'brown',
			'#ff7f50' => 'coral',
			'#ffd700' => 'gold',
			'#008000' => 'green',
			'#808080' => 'grey',
			'#4b0082' => 'indigo',
			'#fffff0' => 'ivory',
			'#f0e68c' => 'khaki',
			'#faf0e6' => 'linen',
			'#000080' => 'navy',
			'#808000' => 'olive',
			'#ffa500' => 'orange',
			'#da70d6' => 'orchid',
			'#cd853f' => 'peru',
			'#ffc0cb' => 'pink',
			'#dda0dd' => 'plum',
			'#f00' => 'red',
			'#fa8072' => 'salmon',
			'#a0522d' => 'sienna',
			'#c0c0c0' => 'silver',
			'#fffafa' => 'snow',
			'#d2b48c' => 'tan',
			'#008080' => 'teal',
			'#ff6347' => 'tomato',
			'#ee82ee' => 'violet',
			'#f5deb3' => 'wheat',
			'black' => '#000',
			'indianred' => '#cd5c5c',
			'lightcoral' => '#f08080',
			'darksalmon' => '#e9967a',
			'lightsalmon' => '#ffa07a',
			'crimson' => '#dc143c',
			'firebrick' => '#b22222',
			'darkred' => '#8b0000',
			'lightpink' => '#ffb6c1',
			'hotpink' => '#ff69b4',
			'deeppink' => '#ff1493',
			'mediumvioletred' => '#c71585',
			'palevioletred' => '#db7093',
			'orangered' => '#ff4500',
			'darkorange' => '#ff8c00',
			'lightyellow' => '#ffffe0',
			'lemonchiffon' => '#fffacd',
			'lightgoldenrodyellow' => '#fafad2',
			'papayawhip' => '#ffefd5',
			'moccasin' => '#ffe4b5',
			'peachpuff' => '#ffdab9',
			'palegoldenrod' => '#eee8aa',
			'darkkhaki' => '#bdb76b',
			'lavender' => '#e6e6fa',
			'thistle' => '#d8bfd8',
			'fuchsia' => '#ff00ff',
			'magenta' => '#ff00ff',
			'mediumorchid' => '#ba55d3',
			'mediumpurple' => '#9370db',
			'rebeccapurple' => '#663399',
			'blueviolet' => '#8a2be2',
			'darkviolet' => '#9400d3',
			'darkorchid' => '#9932cc',
			'darkmagenta' => '#8b008b',
			'slateblue' => '#6a5acd',
			'darkslateblue' => '#483d8b',
			'mediumslateblue' => '#7b68ee',
			'greenyellow' => '#adff2f',
			'chartreuse' => '#7fff00',
			'lawngreen' => '#7cfc00',
			'limegreen' => '#32cd32',
			'palegreen' => '#98fb98',
			'lightgreen' => '#90ee90',
			'mediumspringgreen' => '#00fa9a',
			'springgreen' => '#00ff7f',
			'mediumseagreen' => '#3cb371',
			'seagreen' => '#2e8b57',
			'forestgreen' => '#228b22',
			'darkgreen' => '#006400',
			'yellowgreen' => '#9acd32',
			'olivedrab' => '#6b8e23',
			'darkolivegreen' => '#556b2f',
			'mediumaquamarine' => '#66cdaa',
			'darkseagreen' => '#8fbc8b',
			'lightseagreen' => '#20b2aa',
			'darkcyan' => '#008b8b',
			'lightcyan' => '#e0ffff',
			'paleturquoise' => '#afeeee',
			'aquamarine' => '#7fffd4',
			'turquoise' => '#40e0d0',
			'mediumturquoise' => '#48d1cc',
			'darkturquoise' => '#00ced1',
			'cadetblue' => '#5f9ea0',
			'steelblue' => '#4682b4',
			'lightsteelblue' => '#b0c4de',
			'powderblue' => '#b0e0e6',
			'lightblue' => '#add8e6',
			'skyblue' => '#87ceeb',
			'lightskyblue' => '#87cefa',
			'deepskyblue' => '#00bfff',
			'dodgerblue' => '#1e90ff',
			'cornflowerblue' => '#6495ed',
			'royalblue' => '#4169e1',
			'mediumblue' => '#0000cd',
			'darkblue' => '#00008b',
			'midnightblue' => '#191970',
			'cornsilk' => '#fff8dc',
			'blanchedalmond' => '#ffebcd',
			'navajowhite' => '#ffdead',
			'burlywood' => '#deb887',
			'rosybrown' => '#bc8f8f',
			'sandybrown' => '#f4a460',
			'goldenrod' => '#daa520',
			'darkgoldenrod' => '#b8860b',
			'chocolate' => '#d2691e',
			'saddlebrown' => '#8b4513',
			'honeydew' => '#f0fff0',
			'mintcream' => '#f5fffa',
			'aliceblue' => '#f0f8ff',
			'ghostwhite' => '#f8f8ff',
			'whitesmoke' => '#f5f5f5',
			'seashell' => '#fff5ee',
			'oldlace' => '#fdf5e6',
			'floralwhite' => '#fffaf0',
			'antiquewhite' => '#faebd7',
			'lavenderblush' => '#fff0f5',
			'mistyrose' => '#ffe4e1',
			'gainsboro' => '#dcdcdc',
			'lightgray' => '#d3d3d3',
			'darkgray' => '#a9a9a9',
			'dimgray' => '#696969',
			'lightslategray' => '#778899',
			'slategray' => '#708090',
			'darkslategray' => '#2f4f4f'
		],
		'minify' => [
			'selectors' => true, // minify selectors where possible
			'semicolons' => true, // remove last semi-colon in each rule
			'zerounits' => true, // remove the unit from 0 values where possible (0px => 0)
			'leadingzeros' => true, // remove leading 0 from fractional values (0.5 => .5)
			'trailingzeros' => true, // remove any trailing 0's from fractional values (74.0 => 74)
			'decimalplaces' => 4, // maximum number of decimal places for a value
			'multiples' => true, // minify multiple values (margin: 20px 10px 20px 10px => margin: 20px 10px)
			'quotes' => true, // remove quotes where possible (background: url("test.png") => background: url(test.png))
			'convertquotes' => true, // convert single quotes to double quotes (content: '' => content: "")
			'colors' => true, // shorten hex values and replace with named values where shorter (color: #FF0000 => color: red)
			'time' => true, // shorten time values where possible (500ms => .5s)
			'fontweight' => true, // shorten font-weight values (font-weight: bold => font-weight: 700)
			'none' => true, // replace none with 0 where possible (border: none => border: 0)
			'lowerproperties' => true, // lowercase property names (DISPLAY: BLOCK => display: BLOCK)
			'lowervalues' => true, // lowercase values where possible (DISPLAY: BLOCK => DISPLAY: block)
			'empty' => true // delete empty rules and @directives
		],
		'output' => [
			'style' => 'minify', // the output style, either 'minify' or 'beautify'
			'prefix' => '' // a string to prefix every line with in beautify mode, used for adding indents to
		]
	];

	/**
	 * @var ?document $document The root document
	 */
	public ?document $document = null;

	/**
	 * @var int $pointer The current pointer position for the array iterator
	 */
	protected int $pointer = 0;

	/**
	 * Constructs the object
	 *
	 * @param array $config An array of configuration parameters that is recursively merged with the default config
	 */
	public function __construct(array $config = []) {
		if ($config) {
			$this->config = \array_replace_recursive($this->config, $config);
		}
	}

	/**
	 * Calculates the length property
	 *
	 * @param string $var The name of the property to retrieve, currently 'length' and output
	 * @return mixed The number of children in the object for length, the output config, or null if the parameter doesn't exist
	 */
	#[\ReturnTypeWillChange]
	public function __get(string $var) {
		if ($var === 'length') {
			return \count($this->document->rules ?? []);
		} elseif ($var === 'config') {
			return $this->config;
		}
		return null;
	}

	/**
	 * Retrieves the children of the document as an array
	 *
	 * @return array An array of child nodes
	 */
	public function toArray() : array {
		return $this->document->rules ?? [];
	}

	/**
	 * Array access method allows you to set the object's configuration as properties
	 *
	 * @param mixed $i The key to be updated, can be a string or integer
	 * @param mixed $value The value of the array key in the children array to be updated
	 */
	public function offsetSet($i, $value) : void {
		if (\is_null($i)) {
			$this->document->rules[] = $value;
		} else {
			$this->document->rules[$i] = $value;
		}
	}

	/**
	 * Array access method allows you to check that a key exists in the configuration array
	 *
	 * @param string|integer $i The key to be checked, can be a string or integer
	 * @return bool Whether the key exists in the config array
	 */
	public function offsetExists($i) : bool {
		return isset($this->document->rules[$i]);
	}

	/**
	 * Removes a key from the configuration array
	 *
	 * @param string|integer $i The key to be removed, can be a string or integer
	 */
	public function offsetUnset($i) : void {
		unset($this->document->rules[$i]);
	}

	/**
	 * Retrieves a value from the configuration array with the specified key
	 *
	 * @param string|integer $i The key to be accessed, can be a string or integer
	 * @return mixed The requested value or null if the key doesn't exist
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($i) { // return reference so you can set it like an array
		return $this->document->rules[$i] ?? null;
	}

	/**
	 * Retrieve the document node in the current position
	 *
	 * @return document|rule The child node at the current pointer position
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		return $this->document->rules[$this->pointer] ?? null;
	}

	/**
	 * Retrieve the the current pointer position for the object
	 *
	 * @return mixed The current pointer position
	 */
	public function key() : mixed {
		return $this->pointer;
	}

	/**
	 * Increments the pointer position
	 *
	 * @return void
	 */
	public function next() : void {
		$this->pointer++;
	}

	/**
	 * Decrements the pointer position
	 *
	 * @return void
	 */
	public function rewind() : void {
		$this->pointer = 0;
	}

	/**
	 * Determines whether there is a node at the current pointer position
	 *
	 * @return bool Whether there is a node at the current pointer position
	 */
	public function valid() : bool {
		return isset($this->document->rules[$this->pointer]);
	}

	/**
	 * Open an HTML file from a URL
	 *
	 * @param string $url The address of the HTML file to retrieve
	 * @param resource $context A resource object made with stream_context_create()
	 * @param ?string &$error A reference to any user error that is generated
	 * @return mixed The loaded HTML, or false on error
	 */
	public function open(string $url, mixed $context = null, ?string &$error = null) {

		// check resource
		if ($context !== null && !\is_resource($context)) {
			$error = 'The supplied context is not a valid resource';

		// open a handle to the stream
		} elseif (($handle = @\fopen($url, 'rb', false, $context)) === false) {
			$error = 'Could not open file "'.$url.'"';

		// retrieve the stream contents
		} elseif (($html = \stream_get_contents($handle)) === false) {
			$error = 'Could not read file "'.$url.'"';

		// success
		} else {

			// find charset in headers
			$charset = null;
			$meta = \stream_get_meta_data($handle);
			if (!empty($meta['wrapper_data'])) {
				foreach ($meta['wrapper_data'] AS $item) {
					if (\mb_stripos($item, 'Content-Type:') === 0 && ($charset = \mb_stristr($item, 'charset=')) !== false) {
						$charset = \mb_substr($charset, 8);
						break;
					}
				}
			}

			// load html
			if ($this->load($html, $charset, $error)) {
				return $html;
			}
		}
		return false;
	}

	/**
	 * Parse an HTML string into the object
	 *
	 * @param string $css A string containing valid CSS
	 * @param string $charset The charset of the document
	 * @param ?string &$error A reference to any user error that is generated
	 * @return bool Whether the input HTML was parsed
	 */
	public function load(string $css, string $charset = null, ?string &$error = null) : bool {

		// detect the charset
		if ($charset || ($charset = $this->getCharsetFromCss($css)) !== null) {
			$css = \mb_convert_encoding($css, \mb_internal_encoding(), $charset);
		}

		// reset the document
		$this->document = null;
		if (($obj = $this->parse($css)) === false) {
			$error = 'Input is not invalid';

		// success
		} else {
			$this->document = $obj;
			return true;
		}
		return false;
	}

	/**
	 * Reads the charset defined in the Content-Type meta tag, or detects the charset from the HTML content
	 *
	 * @param string $css A string containing valid CSS
	 * @return ?string The defined or detected charset or null if the charset is not defined
	 */
	protected function getCharsetFromCss(string $css) : ?string {
		if (\mb_strpos($css, '@charset') === 0 && ($end = \mb_strpos($css, '";')) !== false) {
			return \mb_substr($css, 10, $end - 10);
		} elseif (($charset = \mb_detect_encoding($css)) !== false) {
			return $charset;
		}
		return null;
	}

	/**
	 * Parses an array of tokens into an CSS document
	 *
	 * @param string $css A string containing valid CSS
	 * @return document|bool A document object or false if the string could not be parsed
	 */
	protected function parse(string $css) {

		// tokenise the input CSS
		$tokens = new tokenise(self::$tokens, $css);
		// $time = microtime(true);
		// while (($token = $tokens->next()) !== null) {
		// 	$output[] = $token;
		// }
		// var_dump(number_format(microtime(true) - $time, 4), $output);
		// exit();
		$obj = new document($this);
		if ($obj->parse($tokens)) {
			return $obj;
		}
		return false;
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify = []) : void {
		$minify = \array_merge($this->config['minify'], $minify);
		$this->document->minify($minify);
	}

	/**
	 * Compile the document to a string
	 *
	 * @param array $options An array indicating output options, this is merged with cssdoc::$output
	 * @return string The document as a string
	 */
	public function compile(array $options = []) : string {
		$options = \array_merge($this->config['output'], $options);
		return $this->document->compile($options);
	}

	/**
	 * Compile the document and save it to the specified location
	 *
	 * @param string|null $file The file location to save the document to, or null to just return the compiled code
	 * @param array $options An array indicating output options, this is merged with cssdoc::$output
	 * @return string|false The compiled CSS, or false if the file could not be saved
	 */
	public function save(string $file = null, array $options = []) {
		$css = $this->compile($options);

		// save file
		if ($file && \file_put_contents($file, $css) === false) {
			\trigger_error('File could not be written', E_USER_WARNING);
			return false;
		}

		// send back as string
		return $css;
	}

	public function collection(array $rules) {
		$this->document = new document($this, $rules);
	}

	/**
	 * Find rules in the document that match the specified criteria
	 *
	 * @param string $selector A string specifying the selectors to match, comma separate multiple selectors
	 * @param array|string $hasProp A string or array specifying the properties that any rules must contain
	 * @param array $media An array specifying how any media queries should be match, where the key is the property and the key the value. 'max-width' will match any rules where the value is lower that that specified, 'min-width' the value must be higher. Use 'media' to specify the media type
	 * @param bool $exact Denotes whether to match selectors exactly, if false, selectors will be matched from the left
	 * @return cssdoc A CSSdoc object
	 */
	public function find(?string $selector, $hasProp = null, array $media = [], bool $exact = true) : cssdoc {

		// normalise selectors
		$selector = $selector === null ? null : \array_map('\\trim', \explode(',', $selector));
		if (!\is_array($hasProp)) {
			$hasProp = [$hasProp];
		}

		// find rules
		$rules = $this->document->find($selector, $hasProp, $media);

		// attach to a new document
		$obj = new cssdoc($this->config);
		$obj->collection($rules);
		return $obj;
	}

	// public function prop(string $prop, ?string $func = null) {
	//
	// }
}
