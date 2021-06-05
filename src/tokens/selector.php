<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class selector {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected $root;

	/**
	 * @var array An array of selectors
	 */
	protected $selectors = [];

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
		if (($token = $tokens->current()) !== null) {
			$join = null;
			do {
				switch ($token['type']) {
					case 'whitespace':
						if (!$join && $this->selectors) {
							$join = ' ';
						}
						break;
					case 'join':
						if ($token['value'] != '*') {
							$join = $token['value'];
							break;
						}
					case 'string':
						$this->selectors[] = [
							'selector' => $token['value'],
							'join' => $join
						];
						$join = null;
						break;
					case 'colon':
						$parts = ':';
						$brackets = false;
						while (($token = $tokens->next()) !== null) {

							// build up the selector
							if (!in_array($token['type'], ['whitespace', 'comma', 'curlyopen', 'bracketopen'])) {
								$parts .= $token['value'];

							// stop here
							} else {

								// save selector
								$this->selectors[] = [
									'selector' => $parts,
									'join' => $join
								];
								$join = null;

								// capture brackets
								if ($token['type'] === 'bracketopen') {
									$tokens->next();
									$obj = new selector($this->root);
									if ($obj->parse($tokens)) {
										$this->selectors[] = $obj;
									}

								// don't consume the current token
								} else {
									$tokens->prev();
								}
								break;
							}
						}
						break;
					case 'squareopen':
						$parts = '';
						while (($token = $tokens->next()) !== null) {
							if ($token['type'] != 'whitespace') {
								if ($token['type'] != 'squareclose') {
									$parts .= $token['value'];
								} else {
									$tokens->prev();
									// prev($tokens);
									break;
								}
							}
						}
						$this->selectors[] = [
							'selector' => '['.$parts.']',
							'join' => $join
						];
						$join = null;
						break;
					case 'curlyopen':
					case 'curlyclose':
						$tokens->prev();
					case 'bracketclose':
					case 'comma':
						break 2;
				}
			} while (($token = $tokens->next()) !== null);
		}
		return !empty($this->selectors);
	}

	/**
	 * Minifies the internal representation of the comment
	 *
	 * @param array $minify An array of minification options controlling which operations are performed
	 * @return void
	 */
	public function minify(array $minify) : void {
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return void
	 */
	public function compile(array $options) : string {
		$space = $options['style'] != 'minify' ? ' ' : '';
		$css = '';
		foreach ($this->selectors AS $item) {
			if (is_object($item)) {
				$css .= '('.$item->compile($options).')';
			} else {
				if ($item['join']) {
					if ($item['join'] == ' ') {
						$css .= $item['join'];
					} else {
						$css .= $space.$item['join'].$space;
					}
				}
				$css .= $item['selector'];
			}
		}
		return $css;
	}
}
