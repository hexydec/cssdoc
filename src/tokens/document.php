<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class document {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected cssdoc $root;

	/**
	 * @var array An array of child token objects
	 */
	public array $rules = [];

	/**
	 * Constructs the comment object
	 *
	 * @param cssdoc $root The parent htmldoc object
	 */
	public function __construct(cssdoc $root, array $rules = []) {
		$this->root = $root;
		$this->rules = $rules;
	}

	/**
	 * Parses CSS tokens
	 *
	 * @param tokenise &$tokens A tokenise object
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
			if ($minify['empty'] && $item->isEmpty()) {
				unset($this->rules[$key]);
			}
		}
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return string The compiled document
	 */
	public function compile(array $options) : string {
		$b = $options['style'] !== 'minify';
		$css = '';

		// compile selectors
		$join = '';
		foreach ($this->rules AS $item) {
			$css .= $join.$item->compile($options);
			$join = $b ? "\n\n" : '';
		}
		return $css;
	}

	/**
	 * Find rules in the document that match the specified criteria
	 *
	 * @param ?array $selectors A string specifying the selectors to match, comma separate multiple selectors
	 * @param array|string $hasProp A string or array specifying the properties that any rules must contain
	 * @param array $media An array specifying how any media queries should be match, where the key is the property and the key the value. 'max-width' will match any rules where the value is lower that that specified, 'min-width' the value must be higher. Use 'media' to specify the media type
	 * @param bool $exact Denotes whether to match selectors exactly, if false, selectors will be matched from the left
	 * @return array A CSSdoc object
	 */
	public function find(?array $selectors, $hasProp = null, array $media = [], bool $exact = true) {
		$rules = [];
		foreach ($this->rules AS $item) {
			if (\get_class($item) === '\\hexydec\\css\\cssdoc' && $item->matches($selectors, $hasProp, $exact)) {
				$rules[] = $item;
			}
		}
		return $rules;
	}
}
