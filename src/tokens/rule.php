<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class rule {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected cssdoc $root;

	/**
	 * @var array An array of selectors
	 */
	protected array $selectors = [];

	/**
	 * @var array An array of properties
	 */
	public array $properties = [];

	/**
	 * @var string A comment
	 */
	protected ?string $comment = null;

	/**
	 * Constructs the comment object
	 *
	 * @param cssdoc $root The parent htmldoc object
	 */
	public function __construct(cssdoc $root) {
		$this->root = $root;
	}

	/**
	 * Parses CSS tokens
	 *
	 * @param tokenise &$tokens A tokenise object
	 * @return bool Whether anything was parsed
	 */
	public function parse(tokenise $tokens) : bool {
		if (($token = $tokens->current()) !== null) {

			// parse tokens
			$selector = true;
			do {
				switch ($token['type']) {
					case 'curlyopen':
						$selector = false;
						break;
					case 'directive':
						$tokens->prev();
					case 'curlyclose':
						break 2;
					case 'whitespace':
						break;
					case 'comment':
						$this->comment = $token['value'];
						break;
					default:
						if ($selector) {
							$item = new selector($this->root);
							if ($item->parse($tokens)) {
								$this->selectors[] = $item;
							}
						} else {
							$item = new property($this->root);
							if ($item->parse($tokens)) {
								$this->properties[] = $item;
							}
						}
						break;
				}
			} while (($token = $tokens->next()) !== null);
		}
		return $this->selectors && $this->properties;
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {

		// minify selectors
		foreach ($this->selectors AS $item) {
			$item->minify($minify);
		}

		// minify properties
		foreach ($this->properties AS $item) {
			$item->minify($minify);
		}

		// remove last semi-colon
		if ($this->properties && $minify['semicolons']) {
			\end($this->properties)->semicolon = false;
		}
	}

	public function isEmpty() {
		return !$this->properties;
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return string The compiled CSS rule
	 */
	public function compile(array $options) : string {
		$b = $options['style'] !== 'minify';
		$css = $options['prefix'];

		// compile selectors
		$join = '';
		foreach ($this->selectors AS $item) {
			$css .= $join.$item->compile($options);
			$join = $b ? ', ' : ',';
		}
		$css .= $b ? ' {' : '{';

		// compile properties
		$tab = $b ? "\n\t" : '';
		foreach ($this->properties AS $item) {
			$css .= $tab.$item->compile($options);
		}
		$css .= $b ? "\n".$options['prefix'].'}' : '}';
		return $css;
	}

	public function matches(?array $selectors, array $hasProp = [], bool $exact = true) : bool {
		$matches = false;

		// match selectors
		foreach ($this->selectors AS $selector) {
			$compiled = $selector->compile();
			if ($exact) {
				if (\in_array($compiled, $selector)) {
					$matches = true;
					break;
				}
			} else {
				foreach ($selectors AS $item) {
					if (\mb_stripos($compiled, $item) !== false) {
						$matches = true;
						break;
					}
				}
			}
		}

		// check props
		if ($matches && $hasProp) {
			foreach ($this->properties AS $item) {
				if (!\in_array($item->name, $hasProp)) {
					$matches = false;
					break;
				}
			}
		}
		return $matches;
	}
}
