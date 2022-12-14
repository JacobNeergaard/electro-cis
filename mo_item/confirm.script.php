<?php
require_once __DIR__.'/../header.inc.php';

$item = $mysqli->real_escape_string($_POST['item']);
$value = $mysqli->real_escape_string($_POST['value']);
$description = $mysqli->real_escape_string($_POST['description']);

if($item && $description) {
	$sql = "INSERT INTO item (`item`,`value`,`description`)
		VALUES ('$item','$value','$description')";
	$mysqli->query($sql);
	
	Ufo::get('dialog','mo_item/edit.php?item='.$item);
}
else {
	Ufo::abort('dialog');
}


Ufo::update('main');
