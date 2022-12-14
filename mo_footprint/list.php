<?php
require_once __DIR__.'/../header.inc.php';

$doc = new BootSome();

$form = $doc->form()->form_inline();
$form->button('Add','plus')->at(['onclick'=>"Ufo.get('dialog','mo_footprint/modify.php');"]);
$form->button('Export','plus')->at(['onclick'=>"Ufo.get('dialog','mo_footprint/export.php');"]);

$table = $doc->table();
$tr = $table->thead()->tr();
$tr->th()->te('Footprint');
$tr->th()->te('BOM Exclude');

$sql = "SELECT `id`,`name`,`exclude` FROM footprint ORDER BY name";
$query = $mysqli->query($sql);

$tbody = $table->tbody();
while($rs = $query->fetch_object()) {
	$tr = $tbody->tr();
	$tr->at(['onclick'=>"Ufo.get('dialog','mo_footprint/modify.php?footprint_id=$rs->id');"]);
	$tr->td()->te($rs->name);
	$tr->td()->te($rs->exclude ? 'X' : '');
}

Ufo::output('content',$doc);
