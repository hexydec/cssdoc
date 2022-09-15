<?php
declare(strict_types = 1);
namespace hexydec\css;
use \hexydec\tokens\tokenise;

class selector {

	/**
	 * @var cssdoc The parent CSSdoc object
	 */
	protected cssdoc $root;

	/**
	 * @var array An array of selectors
	 */
	protected array $selectors = [];

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
			$join = null;
			do {
				switch ($token['type']) {
					case 'whitespace':
						if (!$join && $this->selectors) {
							$join = ' ';
						}
						break;
					case 'join':
						if ($token['value'] !== '*') {
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
							if (!\in_array($token['type'], ['whitespace', 'comma', 'curlyopen', 'bracketopen', 'bracketclose'], true)) {
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
							if ($token['type'] !== 'whitespace') {
								if ($token['type'] !== 'squareclose') {
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
		foreach ($this->selectors AS &$item) {

			// minify sub-selector
			if (\is_object($item)) {
				$item->minify($minify);

			// change double colon to single colon
			} elseif ($minify['selectors'] && (\mb_strpos($item['selector'], '::before') === 0 || \mb_strpos($item['selector'], '::after') === 0)) {
				$item['selector'] = mb_substr($item['selector'], 1);

			// quoted attributes
			} elseif (\strpbrk($item['selector'], '\'"') !== false && \preg_match('/^((?U).*)([\'"])((?:\\\\.|[^\\2])*)(\\2)(.*)$/i', $item['selector'], $match)) {

				// remove quotes from strings where possible (must only contain alphanumeric, underscore and dash, not start with a digit, double dash, or dash then digit)
				if ($minify['selectors'] && \preg_match('/^-?[a-z_][a-z0-9_-]*+$/i', $match[3])) {
					$match[2] = $match[4] = '';

				// convert quotes
				} elseif ($minify['convertquotes'] && $match[2] === "'") {
					$match[2] = $match[4] = '"';
					$match[3] = \str_replace(["\\'", '"'], ["'", '\\"'], $match[3]);
				}

				// recompile
				unset($match[0]);
				$item['selector'] = \implode('', $match);
			}
		}
		unset($item);
	}

	/**
	 * Compile the property to a string
	 *
	 * @param array $options An array of compilation options
	 * @return string The compiled CSS selector
	 */
	public function compile(array $options) : string {
		$space = $options['style'] !== 'minify' ? ' ' : '';
		$css = '';
		foreach ($this->selectors AS $item) {
			if (\is_object($item)) {
				$css .= '('.$item->compile($options).')';
			} else {
				if ($item['join']) {
					if ($item['join'] === ' ') {
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
