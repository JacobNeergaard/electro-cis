<?php
require_once __DIR__.'/../header.inc.php';

$name = $mysqli->real_escape_string($_POST['name']);
$exclude = empty($_POST['exclude']) ? 0 : 1;
$data = $_POST['data'];
$data = str_replace("\r",'',$data);
$data = $mysqli->real_escape_string($data);

if(isset($_POST['footprint_id'])) {
	$footprint_id = $mysqli->real_escape_string($_POST['footprint_id']);
}
else {
	$footprint_id = null;
}

if($name && $data) {
	if($footprint_id) {
		$sql = "UPDATE footprint SET 
				`name`='$name',
				`exclude`='$exclude',
				`data`='$data'
			WHERE `id`='$footprint_id'";
		$mysqli->query($sql);
	}
	else {
		$sql = "INSERT INTO footprint (`name`,`exclude`,`data`)
			VALUES ('$name','$exclude','$data')";
		$mysqli->query($sql);
	}
}

Ufo::abort('dialog');
Ufo::update('main');
