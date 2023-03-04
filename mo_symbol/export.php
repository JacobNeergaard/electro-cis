<?php
require_once __DIR__.'/../header.inc.php';

$myfile = fopen(PATH_SYMBOL.'/library.kicad_sym', 'w');

fwrite($myfile, "(kicad_symbol_lib (version 20220914) (generator kicad_symbol_editor)\n");

$sql = "SELECT data FROM symbol ORDER BY name";
$query = $mysqli->query($sql);

while($rs = $query->fetch_object()) {
	foreach(explode(PHP_EOL,$rs->data) as $line) {
		$line = str_replace("\r",'',$line);
		fwrite($myfile, '  '.$line."\n");
	}
}

fwrite($myfile, ")\n");

Ufo::abort('dialog');
Ufo::call('alert','Done');
