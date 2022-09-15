<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class value {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected cssdoc $root;

	/**
	 * @var string The name of the parent property
	 */
	protected ?string $name = null;

	/**
	 * @var bool Whether the value is within brackets
	 */
	protected bool $brackets = false;

	/**
	 * @var array Properties
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
	 * @return bool Whether anything was parsed
	 */
	public function parse(tokenise $tokens) : bool {
		while (($token = $tokens->next()) !== null) {
			switch ($token['type']) {
				case 'string':
				case 'datauri':
				case 'join':
					$value = [];
					do {
						if (\in_array($token['type'], ['string', 'join', 'datauri'], true)) {
							$value[] = $token['value'];
						} else {
							$tokens->prev();
							break;
						}
					} while (($token = $tokens->next()) !== null);
					$this->properties[] = \implode('', $value);
					break;
				case 'colon':
				case 'quotes':
				case 'comma':
					$this->properties[] = $token['value'];
					break;
				case 'bracketopen':
					$item = new value($this->root, !$this->brackets && $this->properties ? end($this->properties) : $this->name, true);
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
			if (\is_object($item)) {
				$item->minify($minify);
			} else {

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

				// shorten hex values
				} elseif ($minify['colors'] && \mb_strpos($item, '#') === 0) {

					// shorten hex values
					if (\preg_match('/^#(([a-f0-9])\\2)(([a-f0-9])\\4)(([a-f0-9])\\6)/i', $item, $match)) {
						$item = '#'.$match[2].$match[4].$match[6];
					}

					// replace with named colours
					$colour = \strtolower($item);
					if (isset($config['colors'][$colour])) {
						$item = $config['colors'][$colour];
					}

				// starts with a digit
				} elseif (\strpbrk($item[0], '-0123456789.') !== false && \preg_match('/^(-)?(0*)([0-9]*)(?:((?U)\\.[0-9]*)(0*))?([a-z%]*)$/i', $item, $match)) {

					// remove leading 00s
					if ($minify['leadingzeros'] && ($match[3] || $match[4])) {
						$match[2] = '';
					}

					// remove leading 00s
					if ($minify['trailingzeros']) {
						$match[5] = '';

						// remove decimal place if no other values
						if ($match[4] === '.') {
							$match[4] = '';
						}
					}

					$unit = \strtolower($match[6]);

					// shorten time values
					if ($minify['time'] && $unit === 'ms' && ($len = \mb_strlen($match[3])) >= 3 && $match[3][$len-1] === '0') {
						if (($match[4] = \rtrim(\mb_substr($match[3], -3), '0')) !== '') {
							$match[4] = '.'.$match[4];
						}
						$match[3] = $len > 3 ? \mb_substr($match[3], 0, -3) : '';
						$match[6] = 's';

					// remove unit on 0 values, not inside brackets where they must remain
					} elseif ($minify['zerounits'] && $match[2] === '0' && !$match[3] && !$match[4] && !\in_array($unit, ['s', 'ms'], true) && !$this->brackets) {
						$match[6] = '';
					}

					// reduce decimal places
					if (!\in_array($minify['decimalplaces'], [null, false], true) && \mb_strlen($match[4]) > $minify['decimalplaces']) {
						$match[4] = $minify['decimalplaces'] ? \mb_substr($match[4], 0, $minify['decimalplaces'] + 1) : '';
						$match[5] = '';
					}

					// rebuild value
					unset($match[0]);
					$item = \implode('', $match);
				}

				// quoted values
				if (($single = \mb_strpos($item, "'")) === 0 || \mb_strpos($item, '"') === 0) {

					// remove quotes where possible
					if ($minify['quotes'] && !\in_array($name, $config['quoted'], true) && \preg_match('/^("|\')((?!-?\\d)[-_a-z0-9.\\/]++)\\1$/i', $item, $match)) {
						$item = $match[2];

					// or convert to double quotes
					} elseif ($minify['convertquotes'] && $single === 0 && \mb_strpos($item, '"') === false) {
						$item = '"'.\str_replace("\\'", "'", \trim($item, "'")).'"';
					}

				// lowercase non quoted values
				} elseif ($minify['lowervalues'] && !\in_array($name, $config['casesensitive'], true)) {
					$item = \mb_strtolower($item);
				}
			}
		}
		unset($item);

		// shorten none to 0
		if ($minify['none'] && \in_array($name, $config['none']) && !isset($this->properties[1]) && \in_array($this->properties[0], ['none', 'transparent'], true)) {
			$this->properties[0] = '0';
		}

		// minify multiple values
		if ($minify['multiples'] && \in_array($name, $config['multiples'], true)) {

			// compile properties
			$props = [];
			$i = 0;
			$options = ['style' => 'minify', 'prefix' => ''];
			foreach ($this->properties AS $key => $item) {
				if (\is_object($item)) {
					$props[$i-1]['value'] .= '('.$item->compile($options).')';
					$props[$i-1]['keys'][] = $key;
				} else {
					$props[$i++] = [
						'keys' => [$key],
						'value' => $item
					];
				}
			}

			// delete properties where they are the same
			for ($i = 3; $i > 0; $i--) {

				// if the property exists
				if (isset($props[$i])) {

					// compare two behind unless it becomes less than 0, then compare the 0 key
					$compare = $i - 2;
					if ($props[$i]['value'] === $props[$compare < 0 ? 0 : $compare]['value']) {
						foreach ($props[$i]['keys'] AS $item) {
							unset($this->properties[$item]);
						}
					} else {
						break;
					}
				}
			}
		}
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return string The compiled CSS value
	 */
	public function compile(array $options) : string {
		$b = $options['style'] !== 'minify';
		$css = $options['prefix'];
		$join = '';
		$last = null;
		foreach ($this->properties AS $item) {
			if (\is_object($item)) {
				if (\in_array($last, ['and', '+', '-'], true)) {
					$css .= $join;
				}
				$css .= '('.$item->compile($options).')';
				$join = ' ';
			} elseif (\in_array($item, ['-', '+'], true) && !\in_array(\mb_strtolower($this->name), $this->root->config['spaced'], true)) {
				$css .= $item;
				$join = '';
			} elseif (\in_array($item, [':', ',', '*', '/'], true)) {
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
