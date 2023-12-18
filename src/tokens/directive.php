<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class directive {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected cssdoc $root;

	/**
	 * @var string The name of the directive
	 */
	protected string $directive;

	/**
	 * @var array The contents of the directive, split into parts
	 */
	public array $content = [];

	/**
	 * @var array An array of properties
	 */
	public array $properties = [];

	/**
	 * @var document A document object
	 */
	public ?document $document = null;

	/**
	 * @var bool Whether the object has an empty body
	 */
	public bool $empty = false;

	/**
	 * Constructs the comment object
	 *
	 * @param cssdoc $root The parent cssdoc object
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
			$properties = false;
			$root = $this->root;
			do {
				switch ($token['type']) {
					case 'directive':
						$this->directive = $token['value'];
						break;
					case 'string':
					case 'colon':
					case 'bracketopen':
						if ($properties) {
							$item = new property($root);
							if ($item->parse($tokens)) {
								$this->properties[] = $item;
							}
							break;
						}
					case 'quotes':
						$tokens->prev();
						$item = new value($root, $this->directive);
						if ($item->parse($tokens)) {
							$this->content[] = $item;
						}
						break;
					case 'curlyopen':
						if (\in_array($this->directive, $root->config['nested'], true)) {
							$item = new document($root);
							if ($item->parse($tokens)) {
								$this->document = $item;
							} else {
								$this->empty = true;
							}
						} else {
							$properties = true;
						}
						break;
					case 'semicolon':
					case 'curlyclose':
						break 2;
				}
			} while (($token = $tokens->next()) !== null);
		}
		return !empty($this->directive);
	}

	/**
	 * Minifies the internal representation of the diirective
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {

		// minify directive properties
		foreach ($this->content AS $item) {
			$item->minify($minify);
		}

		// minify properties
		$props = $this->properties;
		foreach ($props AS $item) {
			$item->minify($minify);
		}

		if ($minify['semicolons'] && !empty($props)) {
			\end($props)->semicolon = false;
		}

		// minify document
		if ($this->document !== null) {
			$this->document->minify($minify);
			if ($minify['empty'] && !$this->document->rules) {
				$this->document = null;
			}
		}
	}

	/**
	 * Detects if the directive is empty
	 *
	 * @return bool Whether the directive is empty
	 */
	public function isEmpty() : bool {
		if (\in_array($this->directive, $this->root->config['nested'], true)) {
			return $this->document === null && $this->empty;
		} else {
			return empty($this->properties) && empty($this->content);
		}
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return string The compiled CSS
	 */
	public function compile(array $options) : string {
		$b = $options['style'] !== 'minify';
		$css = $this->directive;
		$join = ' ';

		// compile content
		foreach ($this->content AS $item) {
			$css .= $join.$item->compile($options);
			$join = $b ? ', ' : ',';
		}
		if (empty($this->properties) && $this->document === null) {
			$css .= ';';
		}

		// compile properties
		if (!empty($this->properties)) {
			$css .= $b ? ' {' : '{';

			// compile properties
			$tab = $b ? "\n\t".$options['prefix'] : '';
			foreach ($this->properties AS $item) {
				$css .= $tab.$item->compile($options);
			}
			$css .= $b ? "\n".$options['prefix'].'}' : '}';
		}

		// compile document
		if ($this->document !== null) {
			$css .= $b ? ' {' : '{';
			$tab = $b ? "\n\t".$options['prefix'] : '';
			$css .= $tab.$this->document->compile($options);
			$css .= $b ? "\n".$options['prefix'].'}' : '}';
		}
		return $css;
	}
}
