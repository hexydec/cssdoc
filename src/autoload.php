<?php
declare(strict_types = 1);
\spl_autoload_register(function (string $class) : void {
	$classes = [
		'hexydec\\css\\cssdoc' => __DIR__.'/cssdoc.php',
		'hexydec\\css\\document' => __DIR__.'/tokens/document.php',
		'hexydec\\css\\directive' => __DIR__.'/tokens/directive.php',
		'hexydec\\css\\rule' => __DIR__.'/tokens/rule.php',
		'hexydec\\css\\selector' => __DIR__.'/tokens/selector.php',
		'hexydec\\css\\property' => __DIR__.'/tokens/property.php',
		'hexydec\\css\\value' => __DIR__.'/tokens/value.php'
	];
	if (isset($classes[$class])) {
		require($classes[$class]);
	}
});
