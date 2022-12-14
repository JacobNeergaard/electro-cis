<?php
require_once __DIR__.'/../header.inc.php';

$doc = new BootSome();

$form = $doc->form()->form_inline();
$form->button('Add','plus')->at(['onclick'=>"Ufo.get('dialog','mo_symbol/modify.php');"]);
$form->button('Export','plus')->at(['onclick'=>"Ufo.get('dialog','mo_symbol/export.php');"]);

$table = $doc->table();
$tr = $table->thead()->tr();
$tr->th()->te('Symbol');
$tr->th()->te('Description');

$sql = "SELECT `id`,`name`,`description` FROM symbol ORDER BY name";
$query = $mysqli->query($sql);

$tbody = $table->tbody();
while($rs = $query->fetch_object()) {
	$tr = $tbody->tr();
	$tr->at(['onclick'=>"Ufo.get('dialog','mo_symbol/modify.php?symbol_id=$rs->id');"]);
	$tr->td()->te($rs->name);
	$tr->td()->te($rs->description);
}

Ufo::output('content',$doc);
