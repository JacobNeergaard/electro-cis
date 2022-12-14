<?php
require_once __DIR__.'/../header.inc.php';

$myfile = fopen(PATH_SYMBOL.'library.lib', 'w');

fwrite($myfile, "EESchema-LIBRARY Version 2.4\n");
fwrite($myfile, "#encoding utf-8\n");

$sql = "SELECT data FROM symbol ORDER BY name";
$query = $mysqli->query($sql);

while($rs = $query->fetch_object()) {
	fwrite($myfile, $rs->data."\n");
}

fwrite($myfile, "#\n");
fwrite($myfile, "#End Library\n");

Ufo::abort('dialog');
Ufo::call('alert','Done');
