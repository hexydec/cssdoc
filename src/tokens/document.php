<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class document {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected $root;

	/**
	 * @var array An array of child token objects
	 */
	public $rules = [];

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
	 * @param array $config An array of configuration options
	 * @return bool Whether anything was parsed
	 */
	public function parse(tokenise $tokens) : bool {

		// parse tokens
		while (($token = $tokens->next()) !== null) {
			switch ($token['type']) {
				case 'directive':
					$item = new directive($this->root);
					$item->parse($tokens);
					$this->rules[] = $item;
					break;
				case 'curlyclose':
					$tokens->prev();
					break 2;
				case 'comment':
				case 'whitespace':
					break;
				default:
					$item = new rule($this->root);
					if ($item->parse($tokens)) {
						$this->rules[] = $item;
					}
					break;
			}
		}
		return !!$this->rules;
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {
		foreach ($this->rules AS $key => $item) {
			$item->minify($minify);

			// delete rules that have no properties
			if ($item->isEmpty()) {
				unset($this->rules[$key]);
			}
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
		$css = '';

		// compile selectors
		$join = '';
		foreach ($this->rules AS $item) {
			$css .= $join.$item->compile($options);
			$join = $b ? "\n\n" : '';
		}
		return $css;
	}
}
