<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class property {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected cssdoc $root;

	/**
	 * @var string The name of the property
	 */
	protected string $name;

	/**
	 * @var array The values of the property
	 */
	protected array $value = [];

	/**
	 * @var bool Whether the property is important
	 */
	protected bool $important = false;

	/**
	 * @var bool Whether the property has a semi-colon to close it
	 */
	public bool $semicolon = false;

	/**
	 * @var string Any comment specified with the property
	 */
	protected ?string $comment = null;

	/**
	 * Constructs the property object
	 *
	 * @param cssdoc $root The parent cssdoc object
	 */
	public function __construct(cssdoc $root) {
		$this->root = $root;
	}

	/**
	 * Retrieves read only properties
	 *
	 * @param string $var The name of the property to retrieve
	 * @return ?string The value of the requested property, or null if the porperty doesn't exist
	 */
	public function __get(string $var) {
		if ($var === 'name') {
			return $this->name;
		}
		return null;
	}

	/**
	 * Parses CSS tokens
	 *
	 * @param tokenise $tokens A tokenise object
	 * @return bool Whether anything was parsed
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {
			$dash = '';
			do {
				if ($token['value'] === '-') {
					$dash = '-';
				} elseif ($token['type'] === 'string') {
					$prop = $dash.$token['value'];
					while (($token = $tokens->next()) !== null) {
						switch ($token['type']) {
							case 'important':
								$this->important = true;
								break;
							case 'string':
							case 'colon':
								$this->name = $prop;
							case 'comma':
								$item = new value($this->root, $this->name);
								if ($item->parse($tokens)) {
									$this->value[] = $item;
								}
								break;
							case 'semicolon':
								$this->semicolon = true;
								break 3;
							case 'curlyopen':
							case 'curlyclose':
								$tokens->prev();
								break 3;
						}
					}
				} elseif ($token['type'] === 'curlyclose') {
					$tokens->prev();
					break;
				}
			} while (($token = $tokens->next()) !== null);
		}
		return !empty($this->value);
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {

		// lowercase the property name
		if ($minify['lowerproperties'] && $this->name) {
			$this->name = \mb_strtolower($this->name);
		}

		// minify the values
		foreach ($this->value AS $item) {
			$item->minify($minify);
		}
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return string The compiled CSS property
	 */
	public function compile(array $options) : string {
		$b = $options['style'] !== 'minify';
		$css = $options['prefix'].$this->name.':'.($b ? ' ' : '');
		$join = '';
		foreach ($this->value AS $item) {
			$css .= $join.$item->compile($options);
			$join = $b ? ', ' : ',';
		}
		if ($this->important) {
			$css .= ($b ? ' ' : '').'!important';
		}
		if ($this->semicolon) {
			$css .= ';';
		}
		return $css;
	}
}
