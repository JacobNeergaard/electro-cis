<?php
require_once __DIR__.'/../header.inc.php';

if(isset($_GET['item'])) {
	$item = $mysqli->real_escape_string($_GET['item']);
	
	$sql = "SELECT datasheet,LENGTH(datasheet) as len FROM item WHERE item='$item'";
	$query = $mysqli->query($sql);
	
	if($rs = $query->fetch_object()) {
		header("Content-length: $rs->len");
		header("Content-Type: application/pdf");
		
		$name = utf8_decode('ItemNo-'.$item.'.pdf');
		$name = addslashes($name);
		header("Content-Disposition: inline; filename=\"$name\"");
		
		echo $rs->datasheet;
	}
}
