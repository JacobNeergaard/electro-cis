<?php
require_once __DIR__.'/../header.inc.php';

$item = $mysqli->real_escape_string($_POST['item']);
$value = $mysqli->real_escape_string($_POST['value']);
$description = $mysqli->real_escape_string($_POST['description']);
$symbol_id = $mysqli->real_escape_string($_POST['symbol_id']);
$footprint_id = $mysqli->real_escape_string($_POST['footprint_id']);
$manufacturer = $mysqli->real_escape_string($_POST['manufacturer']);
$partnumber = $mysqli->real_escape_string($_POST['partnumber']);
$mouser = $mysqli->real_escape_string($_POST['mouser']);

if($item && $description) {
	$sql = "UPDATE item SET 
			`value`='$value',
			`description`='$description',
			`symbol_id`='$symbol_id',
			`footprint_id`='$footprint_id',
			`manufacturer`='$manufacturer',
			`partnumber`='$partnumber',
			`mouser`='$mouser'
		WHERE `item`='$item'";
	$mysqli->query($sql);
	
	if($_FILES['file']['tmp_name'] && $_FILES['file']['size']<(10*1024*1024)) {
		$tmp_name = $_FILES['file']['tmp_name'];
		$filetype = mime_content_type($tmp_name);
		
		if($filetype!='application/pdf') {
			Ufo::call('alert','Ikke en PDF fil');
			exit;
		}
		
		$content = addslashes(file_get_contents($tmp_name));
		
		$sql = "UPDATE item SET 
				`datasheet`='$content'
			WHERE `item`='$item'";
		$mysqli->query($sql);
	}
	
}

Ufo::abort('dialog');
Ufo::update('main');
