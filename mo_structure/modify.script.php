<?php
require_once __DIR__.'/../header.inc.php';

$name = $mysqli->real_escape_string($_POST['name']);
$group = $mysqli->real_escape_string($_POST['group']);
$description_template = $mysqli->real_escape_string($_POST['description_template']);

if(isset($_POST['org_group'])) {
	$org_group = $mysqli->real_escape_string($_POST['org_group']);
}
else {
	$org_group = null;
}

if($name && $group) {
	if($org_group) {
		$sql = "UPDATE structure SET 
				`name`='$name',
				`description_template`='$description_template',
				`group`='$group'
			WHERE `group`='$org_group'";
		$mysqli->query($sql);
	}
	else {
		$sql = "INSERT INTO structure (`group`,`name`,`description_template`)
			VALUES ('$group','$name','$description_template')";
		$mysqli->query($sql);
	}
}

Ufo::abort('dialog');
Ufo::update('main');
