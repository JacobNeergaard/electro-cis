<?php
require_once __DIR__.'/../header.inc.php';

$doc = new BootSome();

$form = $doc->form()->form_inline();
$form->button('Add','plus')->at(['onclick'=>"Ufo.get('dialog','mo_structure/modify.php');"]);

$table = $doc->table();
$tr = $table->thead()->tr();
$tr->th()->te('Group');
$tr->th()->te('Name');

$sql = "SELECT `group`,`name` FROM structure";
$query = $mysqli->query($sql);

$tbody = $table->tbody();
while($rs = $query->fetch_object()) {
	$tr = $tbody->tr();
	$url = urlencode($rs->group);
	$tr->at(['onclick'=>"Ufo.get('dialog','mo_structure/modify.php?group=$url');"]);
	$tr->td()->at(['monospace'])->te($rs->group);
	$tr->td()->te($rs->name);
}

Ufo::output('content',$doc);
