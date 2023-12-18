<?php
declare(strict_types = 1);
namespace hexydec\css;

class config {

	/**
	 * @var array<array> $config Object configuration array
	 */
	protected array $config = [
		'nested' => ['@media', '@supports', '@keyframes', '@-webkit-keyframes', '@-moz-keyframes', '@-o-keyframes', '@document', '@-moz-document', '@container', '@layer'], // directive that can have nested rules
		'spaced' => ['calc', 'min', 'max', 'clamp'], // values where spaces between operators must be retained
		'quoted' => ['content', 'format', 'counters', '@charset', 'syntax', 'font-feature-settings', '-webkit-font-feature-settings', '-moz-font-feature-settings', 'quotes', 'text-overflow'], // directives or properties where the contained values must be quoted
		'casesensitive' => ['url'], // property values that should not be lowercased
		'none' => ['border', 'background', 'outline'], // properties that can be changed to 0 when none
		'multiples' => ['margin', 'padding', 'border-width', 'border-style', 'border-color', 'border-radius'],
		'colors' => [
			'#f0ffff' => 'azure',
			'#f5f5dc' => 'beige',
			'#ffe4c4' => 'bisque',
			'#a52a2a' => 'brown',
			'#ff7f50' => 'coral',
			'#ffd700' => 'gold',
			'#008000' => 'green',
			'#808080' => 'grey',
			'#4b0082' => 'indigo',
			'#fffff0' => 'ivory',
			'#f0e68c' => 'khaki',
			'#faf0e6' => 'linen',
			'#000080' => 'navy',
			'#808000' => 'olive',
			'#ffa500' => 'orange',
			'#da70d6' => 'orchid',
			'#cd853f' => 'peru',
			'#ffc0cb' => 'pink',
			'#dda0dd' => 'plum',
			'#f00' => 'red',
			'#fa8072' => 'salmon',
			'#a0522d' => 'sienna',
			'#c0c0c0' => 'silver',
			'#fffafa' => 'snow',
			'#d2b48c' => 'tan',
			'#008080' => 'teal',
			'#ff6347' => 'tomato',
			'#ee82ee' => 'violet',
			'#f5deb3' => 'wheat',
			'black' => '#000',
			'indianred' => '#cd5c5c',
			'lightcoral' => '#f08080',
			'darksalmon' => '#e9967a',
			'lightsalmon' => '#ffa07a',
			'crimson' => '#dc143c',
			'firebrick' => '#b22222',
			'darkred' => '#8b0000',
			'lightpink' => '#ffb6c1',
			'hotpink' => '#ff69b4',
			'deeppink' => '#ff1493',
			'mediumvioletred' => '#c71585',
			'palevioletred' => '#db7093',
			'orangered' => '#ff4500',
			'darkorange' => '#ff8c00',
			'lightyellow' => '#ffffe0',
			'lemonchiffon' => '#fffacd',
			'lightgoldenrodyellow' => '#fafad2',
			'papayawhip' => '#ffefd5',
			'moccasin' => '#ffe4b5',
			'peachpuff' => '#ffdab9',
			'palegoldenrod' => '#eee8aa',
			'darkkhaki' => '#bdb76b',
			'lavender' => '#e6e6fa',
			'thistle' => '#d8bfd8',
			'fuchsia' => '#ff00ff',
			'magenta' => '#ff00ff',
			'mediumorchid' => '#ba55d3',
			'mediumpurple' => '#9370db',
			'rebeccapurple' => '#663399',
			'blueviolet' => '#8a2be2',
			'darkviolet' => '#9400d3',
			'darkorchid' => '#9932cc',
			'darkmagenta' => '#8b008b',
			'slateblue' => '#6a5acd',
			'darkslateblue' => '#483d8b',
			'mediumslateblue' => '#7b68ee',
			'greenyellow' => '#adff2f',
			'chartreuse' => '#7fff00',
			'lawngreen' => '#7cfc00',
			'limegreen' => '#32cd32',
			'palegreen' => '#98fb98',
			'lightgreen' => '#90ee90',
			'mediumspringgreen' => '#00fa9a',
			'springgreen' => '#00ff7f',
			'mediumseagreen' => '#3cb371',
			'seagreen' => '#2e8b57',
			'forestgreen' => '#228b22',
			'darkgreen' => '#006400',
			'yellowgreen' => '#9acd32',
			'olivedrab' => '#6b8e23',
			'darkolivegreen' => '#556b2f',
			'mediumaquamarine' => '#66cdaa',
			'darkseagreen' => '#8fbc8b',
			'lightseagreen' => '#20b2aa',
			'darkcyan' => '#008b8b',
			'lightcyan' => '#e0ffff',
			'paleturquoise' => '#afeeee',
			'aquamarine' => '#7fffd4',
			'turquoise' => '#40e0d0',
			'mediumturquoise' => '#48d1cc',
			'darkturquoise' => '#00ced1',
			'cadetblue' => '#5f9ea0',
			'steelblue' => '#4682b4',
			'lightsteelblue' => '#b0c4de',
			'powderblue' => '#b0e0e6',
			'lightblue' => '#add8e6',
			'skyblue' => '#87ceeb',
			'lightskyblue' => '#87cefa',
			'deepskyblue' => '#00bfff',
			'dodgerblue' => '#1e90ff',
			'cornflowerblue' => '#6495ed',
			'royalblue' => '#4169e1',
			'mediumblue' => '#0000cd',
			'darkblue' => '#00008b',
			'midnightblue' => '#191970',
			'cornsilk' => '#fff8dc',
			'blanchedalmond' => '#ffebcd',
			'navajowhite' => '#ffdead',
			'burlywood' => '#deb887',
			'rosybrown' => '#bc8f8f',
			'sandybrown' => '#f4a460',
			'goldenrod' => '#daa520',
			'darkgoldenrod' => '#b8860b',
			'chocolate' => '#d2691e',
			'saddlebrown' => '#8b4513',
			'honeydew' => '#f0fff0',
			'mintcream' => '#f5fffa',
			'aliceblue' => '#f0f8ff',
			'ghostwhite' => '#f8f8ff',
			'whitesmoke' => '#f5f5f5',
			'seashell' => '#fff5ee',
			'oldlace' => '#fdf5e6',
			'floralwhite' => '#fffaf0',
			'antiquewhite' => '#faebd7',
			'lavenderblush' => '#fff0f5',
			'mistyrose' => '#ffe4e1',
			'gainsboro' => '#dcdcdc',
			'lightgray' => '#d3d3d3',
			'darkgray' => '#a9a9a9',
			'dimgray' => '#696969',
			'lightslategray' => '#778899',
			'slategray' => '#708090',
			'darkslategray' => '#2f4f4f'
		],
		'minify' => [
			'selectors' => true, // minify selectors where possible
			'semicolons' => true, // remove last semi-colon in each rule
			'zerounits' => true, // remove the unit from 0 values where possible (0px => 0)
			'leadingzeros' => true, // remove leading 0 from fractional values (0.5 => .5)
			'trailingzeros' => true, // remove any trailing 0's from fractional values (74.0 => 74)
			'decimalplaces' => 4, // maximum number of decimal places for a value
			'multiples' => true, // minify multiple values (margin: 20px 10px 20px 10px => margin: 20px 10px)
			'quotes' => true, // remove quotes where possible (background: url("test.png") => background: url(test.png))
			'convertquotes' => true, // convert single quotes to double quotes (content: '' => content: "")
			'colors' => true, // shorten hex values and replace with named values where shorter (color: #FF0000 => color: red)
			'time' => true, // shorten time values where possible (500ms => .5s)
			'fontweight' => true, // shorten font-weight values (font-weight: bold => font-weight: 700)
			'none' => true, // replace none with 0 where possible (border: none => border: 0)
			'lowerproperties' => true, // lowercase property names (DISPLAY: BLOCK => display: BLOCK)
			'lowervalues' => true, // lowercase values where possible (DISPLAY: BLOCK => DISPLAY: block)
			'empty' => true // delete empty rules and @directives
		],
		'output' => [
			'style' => 'minify', // the output style, either 'minify' or 'beautify'
			'prefix' => '' // a string to prefix every line with in beautify mode, used for adding indents to
		]
	];
}