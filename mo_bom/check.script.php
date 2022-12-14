<?php
require_once __DIR__.'/../header.inc.php';

if(empty($_FILES['file']['tmp_name'])) {
	Ufo::nop();
	exit;
}

$file = fopen($_FILES['file']['tmp_name'], 'r');
$out = fopen('php://temp/maxmemory:'.(5*1024*1024),'r+');
$header = ['Reference','Value','Symbol','Footprint','ItemNo','Manufacturer','Part number'];
fputcsv($out, $header);

$doc = new BootSome();
$table = $doc->table();
$tr = $table->thead()->tr();
$tr->th()->te('Reference');
$tr->th()->te('Error');

$tbody = $table->tbody();
while(($row = fgetcsv($file, 0)) !== FALSE) {
	$error = [];
	$reference = utf8_encode($row[0]);
	$value = utf8_encode($row[1]);
	$symbol = utf8_encode($row[2]);
	$footprint = utf8_encode($row[3]);
	$item = utf8_encode($row[4]);
	
	if(strpos($footprint,'erp:')===0) {
		$footprint = substr($footprint, 4);
	}
	
	if(strpos($reference,'?')!==FALSE) {
		$error[] = "Unannotated";
	}
	if(empty($value)) {
		$error[] = "No Value";
	}
	if(empty($symbol)) {
		$error[] = "No Symbol";
	}
	if(empty($footprint)) {
		$error[] = "No Footprint";
	}
	if(empty($item)) {
		$error[] = "No ItemNo";
	}
	else {
		$sql = "SELECT `item`.`value`,`footprint`.`name` as footprint,`symbol`.`name` as symbol,
				`manufacturer`,`partnumber`,`footprint`.`exclude`
			FROM `item`
			LEFT JOIN `footprint` ON `item`.`footprint_id`=`footprint`.`id`
			LEFT JOIN `symbol` ON `item`.`symbol_id`=`symbol`.`id`
			WHERE `item`.`item`='$item'";
		$query = $mysqli->query($sql);
		
		if($rs = $query->fetch_object()) {
			if(empty($rs->value)) {
				$error[] = "DATABASE Value missing";
			}
			elseif($rs->value!==$value) {
				$error[] = "Value Mismatch ($rs->value) != ($value)";
			}
			if(empty($rs->footprint) && !empty($footprint)) {
				$error[] = "DATABASE Footprint missing";
			}
			elseif($rs->footprint!==$footprint && !empty($footprint)) {
				$error[] = "Footprint Mismatch ($rs->footprint) != ($footprint)";
			}
			if(empty($rs->symbol) && !empty($symbol)) {
				$error[] = "DATABASE Symbol missing";
			}
			elseif($rs->symbol!==$symbol && !empty($symbol)) {
				$error[] = "Symbol Mismatch ($rs->symbol) != ($symbol)";
			}
			
			if(!$rs->exclude) {
				$row[] = $rs->manufacturer;
				$row[] = $rs->partnumber;
				fputcsv($out, $row);
			}
		}
		else {
			$error[] = "ItemNo not found ($item)";
		}
	}
	
	if($error) {
		$tr = $tbody->tr('danger');
		$tr->td()->te($reference);
		$tr->td()->te(implode(', ',$error));
	}
}

rewind($out);
$output = stream_get_contents($out);
Ufo::call('savefile',$output,$_FILES['file']['name']);

Ufo::output('output',$doc);
