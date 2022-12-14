<?php
require_once __DIR__.'/../header.inc.php';

$name = $mysqli->real_escape_string($_POST['name']);
$description = $mysqli->real_escape_string($_POST['description']);
$data = $_POST['data'];
$data = str_replace("\r",'',$data);
$data = $mysqli->real_escape_string($data);

if(isset($_POST['symbol_id'])) {
	$symbol_id = $mysqli->real_escape_string($_POST['symbol_id']);
}
else {
	$symbol_id = null;
}

if($name && $data) {
	if($symbol_id) {
		$sql = "UPDATE symbol SET 
				`name`='$name',
				`description`='$description',
				`data`='$data'
			WHERE `id`='$symbol_id'";
		$mysqli->query($sql);
	}
	else {
		$sql = "INSERT INTO symbol (`name`,`description`,`data`)
			VALUES ('$name','$description','$data')";
		$mysqli->query($sql);
	}
}

Ufo::abort('dialog');
Ufo::update('main');
