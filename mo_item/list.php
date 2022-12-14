<?php
require_once __DIR__.'/../header.inc.php';

$doc = new BootSome();

$form = $doc->form()->form_inline();
$form->button('Add','plus')->at(['onclick'=>"Ufo.get('dialog','mo_item/new.php');"]);

$table = $doc->table();
$tr = $table->thead()->tr();
$tr->th()->te('ItemNo');
$tr->th()->te('Description');
$tr->th()->te('Value');

$sql = "SELECT item,description,value FROM item";
$query = $mysqli->query($sql);

$tbody = $table->tbody();
while($rs = $query->fetch_object()) {
	$tr = $tbody->tr();
	$tr->at(['onclick'=>"Ufo.get('dialog','mo_item/edit.php?item=$rs->item');"]);
	$tr->td()->te($rs->item);
	$tr->td()->te($rs->description);
	$tr->td()->te($rs->value);
}

Ufo::output('content',$doc);
