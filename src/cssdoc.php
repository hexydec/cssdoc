<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class cssdoc extends config implements \ArrayAccess, \Iterator {

	/**
	 * @var array<string> $tokens Regexp components keyed by their corresponding codename for tokenising CSS
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
		if (!empty($config)) {
			$this->config = \array_replace_recursive($this->config, $config);
		}
	}

	/**
	 * Calculates the length property
	 *
	 * @param string $var The name of the property to retrieve, currently 'length' and output
	 * @return mixed The number of children in the object for length, the output config, or null if the parameter doesn't exist
	 */
	public function __get(string $var) : mixed {
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
	public function offsetSet(mixed $i, mixed $value) : void {
		if (\is_null($i)) {
			$this->document->rules[] = $value;
		} else {
			$this->document->rules[$i] = $value;
		}
	}

	/**
	 * Array access method allows you to check that a key exists in the configuration array
	 *
	 * @param mixed $i The key to be checked
	 * @return bool Whether the key exists in the config array
	 */
	public function offsetExists(mixed $i) : bool {
		return isset($this->document->rules[$i]);
	}

	/**
	 * Removes a key from the configuration array
	 *
	 * @param mixed $i The key to be removed
	 */
	public function offsetUnset(mixed $i) : void {
		unset($this->document->rules[$i]);
	}

	/**
	 * Retrieves a value from the configuration array with the specified key
	 *
	 * @param mixed $i The key to be accessed, can be a string or integer
	 * @return mixed The requested value or null if the key doesn't exist
	 */
	public function offsetGet(mixed $i) : mixed { // return reference so you can set it like an array
		return $this->document->rules[$i] ?? null;
	}

	/**
	 * Retrieve the document node in the current position
	 *
	 * @return document|rule The child node at the current pointer position
	 */
	public function current() : mixed {
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
	 * Open a CSS file from a URL
	 *
	 * @param string $url The address of the CSS file to retrieve
	 * @param resource $context A resource object made with stream_context_create()
	 * @param ?string &$error A reference to any user error that is generated
	 * @return string|false The loaded CSS, or false on error
	 */
	public function open(string $url, $context = null, ?string &$error = null) : string|false {

		// check resource
		if ($context !== null && !\is_resource($context)) {
			$error = 'The supplied context is not a valid resource';

		// open a handle to the stream
		} elseif (($handle = @\fopen($url, 'rb', false, $context)) === false) {
			$error = 'Could not open file "'.$url.'"';

		// retrieve the stream contents
		} elseif (($css = \stream_get_contents($handle)) === false) {
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

			// load CSS
			if ($this->load($css, $charset, $error)) {
				return $css;
			}
		}
		return false;
	}

	/**
	 * Parse a CSS string into the object
	 *
	 * @param string $css A string containing valid CSS
	 * @param string $charset The charset of the document
	 * @param ?string &$error A reference to any user error that is generated
	 * @return bool Whether the input CSS was parsed
	 */
	public function load(string $css, string $charset = null, ?string &$error = null) : bool {

		// detect the charset
		if ($charset || ($charset = $this->getCharsetFromCss($css)) !== null) {
			$css = \mb_convert_encoding($css, (string) \mb_internal_encoding(), $charset);
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
	 * Reads the charset defined in the Content-Type meta tag, or detects the charset from the CSS content
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
	 * @return document|false A document object or false if the string could not be parsed
	 */
	protected function parse(string $css) : document|false {

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
		if ($this->document !== null) {
			$minify = \array_merge($this->config['minify'], $minify);
			$this->document->minify($minify);
		}
	}

	/**
	 * Compile the document to a string
	 *
	 * @param array $options An array indicating output options, this is merged with cssdoc::$output
	 * @return string The document as a string
	 */
	public function compile(array $options = []) : ?string {
		if ($this->document !== null) {
			$options = \array_merge($this->config['output'], $options);
			return $this->document->compile($options);
		}
		return null;
	}

	/**
	 * Compile the document and save it to the specified location
	 *
	 * @param string|null $file The file location to save the document to, or null to just return the compiled code
	 * @param array $options An array indicating output options, this is merged with cssdoc::$output
	 * @return string|false The compiled CSS, or false if the file could not be saved
	 */
	public function save(string $file = null, array $options = []) : string|false {
		$css = $this->compile($options);

		// save file
		if ($file && \file_put_contents($file, $css) === false) {
			\trigger_error('File could not be written', E_USER_WARNING);
			return false;
		}

		// send back as string
		return $css;
	}

	// public function collection(array $rules) {
	// 	$this->document = new document($this, $rules);
	// }

	// /**
	//  * Find rules in the document that match the specified criteria
	//  *
	//  * @param string $selector A string specifying the selectors to match, comma separate multiple selectors
	//  * @param array|string $hasProp A string or array specifying the properties that any rules must contain
	//  * @param array $media An array specifying how any media queries should be match, where the key is the property and the key the value. 'max-width' will match any rules where the value is lower that that specified, 'min-width' the value must be higher. Use 'media' to specify the media type
	//  * @param bool $exact Denotes whether to match selectors exactly, if false, selectors will be matched from the left
	//  * @return cssdoc A CSSdoc object
	//  */
	// public function find(?string $selector, $hasProp = null, array $media = [], bool $exact = true) : cssdoc {

	// 	// normalise selectors
	// 	$selector = $selector === null ? null : \array_map('\\trim', \explode(',', $selector));
	// 	if (!\is_array($hasProp)) {
	// 		$hasProp = [$hasProp];
	// 	}

	// 	// find rules
	// 	$rules = $this->document->find($selector, $hasProp, $media);

	// 	// attach to a new document
	// 	$obj = new cssdoc($this->config);
	// 	$obj->collection($rules);
	// 	return $obj;
	// }

	// public function prop(string $prop, ?string $func = null) {
	//
	// }
}
