<?php
use hexydec\css\cssdoc;

final class findCssdocTest extends \PHPUnit\Framework\TestCase {

	public function testCanFindRules() {
		$css = '
			#id {
				display: block;
				background: green;
				color: white;
			}
			input[type=submit].form__control-submit {
				border: 1px solid red;
				padding: 10px;
			}
		';
		$tests = [
			[
				'find' => '#id',
				'props' => [],
				'exact' => true,
				'output' => '#id {
					display: block;
					background: green;
					color: white;
				}'
			]
		];
		$obj = new cssdoc();
		$obj->load($css);
		foreach ($tests AS $item) {
			$doc = $obj->find($item['find'], $item['props'], [], $item['exact']);
			$this->assertEquals($item['output'], $doc->compile());
		}
	}
}
