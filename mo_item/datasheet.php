<?php
require_once __DIR__.'/../header.inc.php';

if(isset($_GET['item'])) {
	$item = $mysqli->real_escape_string($_GET['item']);
	
	$sql = "SELECT datasheet FROM item WHERE item='$item'";
	$query = $mysqli->query($sql);
	
	if($rs = $query->fetch_object()) {
		WildFileHeader::filename('ItemNo-'.$item.'.pdf');
		WildFileHeader::type('application/pdf');
		echo $rs->datasheet;
	}
}
