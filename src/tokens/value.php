<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class value {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected $root;

	/**
	 * @var string The name of the parent property
	 */
	protected $name = null;

	/**
	 * @var bool Whether the value is within brackets
	 */
	protected $brackets = false;

	/**
	 * @var value Properties
	 */
	protected $properties = [];

	/**
	 * Constructs the property object
	 *
	 * @param cssdoc $root The parent cssdoc object
	 * @param string $name The name of the parent property
	 */
	public function __construct(cssdoc $root, ?string $name = null, bool $brackets = false) {
		$this->root = $root;
		if ($name) {
			$this->name = \mb_strtolower($name);
		}
		$this->brackets = $brackets;
	}

	/**
	 * Parses CSS tokens
	 *
	 * @param tokenise &$tokens A tokenise object
	 * @param array $config An array of configuration options
	 * @return bool Whether anything was parsed
	 */
	public function parse(tokenise $tokens) : bool {
		$comment = null;
		while (($token = $tokens->next()) !== null) {
			switch ($token['type']) {
				case 'string':
				case 'datauri':
				case 'join':
					$value = [];
					do {
						if (\in_array($token['type'], ['string', 'join', 'datauri'])) {
							$value[] = $token['value'];
						} else {
							$tokens->prev();
							break;
						}
					} while (($token = $tokens->next()) !== false);
					$this->properties[] = \implode('', $value);
					break;
				case 'colon':
				case 'quotes':
				case 'comma':
					$this->properties[] = $token['value'];
					break;
				case 'bracketopen':
					$item = new value($this->root, $this->properties ? end($this->properties) : $this->name, true);
					if ($item->parse($tokens)) {
						$this->properties[] = $item;
					}
					break;
				case 'comment':
					$comment = $token['value'];
					break;
				case 'semicolon':
					if ($this->brackets) { // allow semi-colon as a value within brackets for data URI's
						$this->properties[] = $token['value'];
					}
				case 'curlyopen':
				case 'curlyclose':
				case 'important':
					$tokens->prev();
				case 'bracketclose':
					break 2;
			}
		}
		return !empty($this->properties);
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {
		$config = $this->root->config;
		$name = $this->name;
		foreach ($this->properties AS &$item) {

			// value in brackets
			if (is_object($item)) {
				$item->minify($minify);
			} else {

				// starts with a 0
				if (mb_strpos($item, '0') === 0) {
					// remove unit from zero values
					if ($minify['zerounits'] && !$this->brackets && \preg_match('/^0(?:\.0*)?((?!m?s)[a-z%]++)$/i', $item, $match)) {
						$item = '0';
						if ($match[1] == 'ms') {
							$match[1] = 's';
						}
						if ($match[1] == 's') {
							$item .= 's';
						}
					}

					// remove leading zeros
					if ($minify['leadingzeros'] && \preg_match('/^0++(\.0*+[1-9][0-9%a-z]*+)$/', $item, $match)) {
						$item = $match[1];
					}
				}

				// quoted values
				if (($single = \mb_strpos($item, "'")) === 0 || \mb_strpos($item, '"') === 0) {

					// remove quotes where possible
					if ($minify['quotes'] && !in_array($name, $config['quoted']) && preg_match('/^("|\')([^ \'"()]++)\\1$/i', $item, $match)) {
						$item = $match[2];

					// or convert to double quotes
					} elseif ($minify['convertquotes'] && $single === 0) {
						$item = '"'.\addcslashes(\stripslashes(\trim($item, "'")), "'").'"';
					}

				// lowercase non quoted values
				} elseif ($minify['lowervalues'] && !\in_array($name, $config['casesensitive'])) {
					$item = \mb_strtolower($item);
				}

				// shorten hex values
				if ($minify['colors'] && \mb_strpos($item, '#') === 0) {

					// shorten hex values
					if (\preg_match('/^#(([a-f0-9])\\2)(([a-f0-9])\\4)(([a-f0-9])\\6)/i', $item, $match)) {
						$item = '#'.$match[2].$match[4].$match[6];
					}

					// replace with named colours
					$colour = \strtolower($item);
					if (isset($config['colors'][$colour])) {
						$item = $config['colors'][$colour];
					}
				}

				// shorten time values
				if ($minify['time'] && \mb_strpos($item, 'ms') && \preg_match('/([0-9]*)([1-9][0-9])0ms/i', $item, $match)) {
					$item = ltrim($match[1], '0').'.'.rtrim($match[2], '0').'s';
				}

				// shorten font-weight
				if ($minify['fontweight'] && $name === 'font-weight') {
					$convert = [
						'normal' => '400',
						'bold' => '700'
					];
					$key = \mb_strtolower($item);
					if (isset($convert[$key])) {
						$item = $convert[$key];
					}
				}
			}
		}
		unset($item);

		// shorten none to 0
		if ($minify['none'] && \in_array($name, $config['none']) && $this->properties[0] === 'none' && !isset($this->properties[1])) {
			$this->properties[0] = '0';
		}
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return void
	 */
	public function compile(array $options) : string {
		$b = $options['style'] != 'minify';
		$css = $options['prefix'];
		$join = '';
		$last = null;
		foreach ($this->properties AS $item) {
			if (\is_object($item)) {
				if ($last == 'and') {
					$css .= $join;
				}
				$css .= '('.$item->compile($options).')';
				$join = ' ';
			} elseif (\in_array($item, [':', '-', '+', ',']) && !\in_array(mb_strtolower($this->name), $this->root->config['spaced'])) {
				$css .= $item;
				$join = '';
			} else {
				$css .= $join.$item;
				$join = ' ';
			}
			$last = $item;
		}
		return $css;
	}
}
