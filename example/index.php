<pre><?php

include '../lib/Lex/Autoloader.php';

Lex_Autoloader::register();

$template = file_get_contents('template.lex');
$data = array(
	'title'     => 'Lex is Awesome!',
	'name'      => 'World',
	'real_name' => new stdClass(),
	'show_real_name' => true,
	'nav_group' => 'cool-stuff',
	'projects'  => array(
		array(
			'name' => 'Fuel',
			'contributors' => array(
				array('name' => 'Dan'),
				array('name' => 'Jelmer'),
				array('name' => 'Harro'),
				array('name' => 'Frank'),
				array('name' => 'Phil'),
			),
		),
		array(
			'name' => 'Lex',
			'contributors' => array(
				array('name' => 'Dan'),
				array('name' => 'Phil'),
				array('name' => 'Ziggy')
			),
		),
	),
);
$data['real_name']->first = 'Lex';
$data['real_name']->last  = 'Luther';

$parser = new Lex_Parser();
$parser->scope_glue(':');
$parsed = $parser->parse($template, $data, 'callback');

echo "<hr /><h2>Original</h2>".htmlentities($template)."\n\n";
echo "<hr /><h2>Parsed</h2>".htmlentities($parsed)."\n\n";
echo "<hr /><h1>Output</h1></pre>".$parsed;

function callback($name, $attributes, $content)
{
	if ($name == 'em')
	{
		return '<em>'.$attributes['value'].'</em>';
	}
	$parser = new Lex_Parser();
	$parser->scope_glue(':');
	return $parser->parse($content, array('baz' => 'baz'));
}
