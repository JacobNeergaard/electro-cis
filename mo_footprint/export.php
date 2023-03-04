<?php
require_once __DIR__.'/../header.inc.php';

$sql = "SELECT `name`,`data` FROM footprint ORDER BY name";
$query = $mysqli->query($sql);

while($rs = $query->fetch_object()) {
	$filename = PATH_FOOTPRINT.'/'.$rs->name.'.kicad_mod';
	$myfile = fopen($filename, 'w');
	$res = fwrite($myfile, $rs->data);
	fclose($myfile);
	if($res===false) Ufo::call('alert','Write error: '.$filename);
}

Ufo::abort('dialog');
Ufo::call('alert','Done');
