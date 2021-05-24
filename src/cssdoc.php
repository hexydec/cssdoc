<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class cssdoc {

	/**
	 * @var array $tokens Regexp components keyed by their corresponding codename for tokenising HTML
	 */
	protected static $tokens = [
	   'whitespace' => '\s++',
	   'comment' => '\\/\\*[\d\D]*?\\*\\/',
	   'quotes' => '(?<!\\\\)(?:"(?:[^"\\\\]++|\\\\.)*+"|\'(?:[^\'\\\\]++|\\\\.)*+\')',
	   'comparison' => '[\^*$<>]?=', // comparison operators for media queries or attribute selectors
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
	   'datauri' => '(?<=url\\()data:[^)]++',
	   'string' => '(?:[^\\/\\[\\]{}\\(\\):;,\\*>+~\\^$!" \\n\\r\\t]++|\\\\.)',
	];

	/**
	 * @var array $config Object configuration array
	 */
	protected $config = [
		'nested' => ['@media', '@supports', '@keyframes', '@-webkit-keyframes', '@-moz-keyframes', '@-o-keyframes', '@document', '@-moz-document'], // directive that can have nested rules
		'spaced' => ['calc'], // values where spaces between operators must be retained
		'quoted' => ['content', 'format', 'counters', '@charset'], // directives or properties where the contained values must be quoted
		'casesensitive' => ['url'], // property values that should not be lowercased
		'none' => ['border', 'background', 'outline'], // properties that can be changed to 0 when none
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
			'#f5deb3' => 'wheat'
		],
		'minify' => [
			'semicolons' => true, // remove last semi-colon in each rule
			'zerounits' => true, // remove the unit from 0 values where possible (0px => 0)
			'leadingzeros' => true, // remove leading 0 from fractional values (0.5 => .5)
			'quotes' => true, // remove quotes where possible (background: url("test.png") => background: url(test.png))
			'convertquotes' => true, // convert single quotes to double quotes (content: '' => content: "")
			'colors' => true, // shorten hex values and replace with named values where shorter (color: #FF0000 => color: red)
			'time' => true, // shorten time values where possible (500ms => .5s)
			'fontweight' => true, // shorten font-weight values (font-weight: bold => font-weight: 700)
			'none' => true, // replace none with 0 where possible (border: none => border: 0)
			'lowerproperties' => true, // lowercase property names (DISPLAY: BLOCK => display: BLOCK)
			'lowervalues' => true // lowercase values where possible (DISPLAY: BLOCK => DISPLAY: block)
		],
		'output' => [
			'style' => 'minify', // the output style, either 'minify' or 'beautify'
			'prefix' => '' // a string to prefix every line with in beautify mode, used for adding indents to
		]
	];

	/**
	 * @var document $document The root document
	 */
	protected $document;

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
	public function __get(string $var) {
		if ($var == 'length') {
			return \count($this->children);
		} elseif ($var == 'config') {
			return $this->config;
		}
		return null;
	}
	/**
	 * Open an HTML file from a URL
	 *
	 * @param string $url The address of the HTML file to retrieve
	 * @param mixed $context A resource object made with stream_context_create()
	 * @param string &$error A reference to any user error that is generated
	 * @return mixed The loaded HTML, or false on error
	 */
	public function open(string $url, mixed $context = null, string &$error = null) {

		// open a handle to the stream
		if (($handle = \fopen($url, 'rb', false, $context)) === false) {
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
	 * @param string $html A string containing valid HTML
	 * @param string $charset The charset of the document
	 * @param string &$error A reference to any user error that is generated
	 * @return bool Whether the input HTML was parsed
	 */
	public function load(string $css, string $charset = null, &$error = null) : bool {

		// detect the charset
		if ($charset || ($charset = $this->getCharsetFromCss($css)) !== null) {
			$css = \mb_convert_encoding($css, \mb_internal_encoding(), $charset);
		}

		// reset the document
		$this->children = [];

		// tokenise the input CSS
		$tokens = new tokenise(self::$tokens, $css);
		// while (($token = $tokens->next()) !== null) {
		// 	var_dump($token);
		// }
		// exit();
		if (!$this->parse($tokens)) {
			$error = 'Input is not invalid';

		// success
		} else {
			return true;
		}
		return false;
	}

	/**
	 * Reads the charset defined in the Content-Type meta tag, or detects the charset from the HTML content
	 *
	 * @param string $html A string containing valid HTML
	 * @return string The defined or detected charset or null if the charset is not defined
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
	 * @param array &$tokens An array of tokens generated by tokenise()
	 * @return bool Whether the parser was able to capture any objects
	 */
	protected function parse(tokenise &$tokens) : bool {
		$this->document = new document($this);
		return $this->document->parse($tokens);
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
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return void
	 */
	public function compile(array $options = []) : string {
		$options = \array_merge($this->config['output'], $options);
		return $this->document->compile($options);
	}

	/**
	 * Compile the document as an HTML string and save it to the specified location
	 *
	 * @param array $options An array indicating output options, this is merged with htmldoc::$output
	 * @return string The compiled HTML
	 */
	public function save(string $file = null, array $options = []) {
		$css = $this->compile($options);

		// send back as string
		if (!$file) {
			return $css;

		// save file
		} elseif (\file_put_contents($file, $css) === false) {
			\trigger_error('File could not be written', E_USER_WARNING);
		} else {
			return true;
		}
		return false;
	}
}
