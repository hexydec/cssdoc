<?php
declare(strict_types = 1);
namespace hexydec\css;

class selector {

	/**
	 * @var rule The parent rule object
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
	public function __construct(rule $root) {
		$this->root = $root;
	}

	/**
	 * Parses an array of tokens into an HTML documents
	 *
	 * @param array &$tokens An array of tokens generated by tokenise()
	 * @param array $config An array of configuration options
	 * @return bool Whether anything was parsed
	 */
	public function parse(array &$tokens) : bool {
		$join = null;
		$token = current($tokens);
		do {
			switch ($token['type']) {
				case 'whitespace':
					if (!$join) {
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
					while (($token = next($tokens)) !== false) {

						// capture brackets
						if ($brackets || $token['type'] == 'bracketopen') {
							$brackets = true;
							if ($token['type'] != 'whitespace') {
								$parts .= $token['value'];
								if ($token['type'] == 'bracketclose') {
									break;
								}
							}

						// capture selector
						} elseif (!in_array($token['type'], ['whitespace', 'comma', 'curlyopen'])) {
							$parts .= $token['value'];

						// stop here
						} else {
							prev($tokens);
							break;
						}
					}

					// save selector
					$this->selectors[] = [
						'selector' => $parts,
						'join' => $join
					];
					$join = null;
					break;
				case 'squareopen':
					$parts = '';
					while (($token = next($tokens)) !== false) {
						if ($token['type'] != 'whitespace') {
							if ($token['type'] != 'squareclose') {
								$parts .= $token['value'];
							} else {
								prev($tokens);
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
				case 'comma':
					prev($tokens);
					break 2;
			}
		} while (($token = next($tokens)) !== false);
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
		$space = $options['output'] != 'minify' ? ' ' : '';
		$css = '';
		foreach ($this->selectors AS $item) {
			if ($item['join']) {
				if ($item['join'] == ' ') {
					$css .= $item['join'];
				} else {
					$css .= $space.$item['join'].$space;
				}
			}
			$css .= $item['selector'];
		}
		return $css;
	}
}