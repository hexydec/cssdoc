<?php
spl_autoload_register(function (string $class) : bool {
	$classes = [
		'hexydec\\css\\cssdoc' => __DIR__.'/cssdoc/cssdoc.php',
		'hexydec\\css\\document' => __DIR__.'/cssdoc/tokens/document.php',
		'hexydec\\css\\directive' => __DIR__.'/cssdoc/tokens/directive.php',
		'hexydec\\css\\rule' => __DIR__.'/cssdoc/tokens/rule.php',
		'hexydec\\css\\selector' => __DIR__.'/cssdoc/tokens/selector.php',
		'hexydec\\css\\property' => __DIR__.'/cssdoc/tokens/property.php',
		'hexydec\\css\\value' => __DIR__.'/cssdoc/tokens/value.php'
	];
	if (isset($classes[$class])) {
		return require($classes[$class]);
	}
	return false;
});
