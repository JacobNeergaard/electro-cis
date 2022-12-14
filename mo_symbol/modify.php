<?php
require_once __DIR__.'/../header.inc.php';

if(isset($_GET['symbol_id']) && $_GET['symbol_id']) {
	$symbol_id = $mysqli->real_escape_string($_GET['symbol_id']);
	$sql = "SELECT `name`,`description`,`data` FROM symbol WHERE `id`='$symbol_id'";
	$query = $mysqli->query($sql);
	if(!$rs = $query->fetch_object()) {
		Ufo::abort('dialog');
		exit;
	}
}
else {
	$symbol_id = null;
	$rs = (object) array('name'=>'','description'=>'','data'=>'');
}

$doc = new BootSome();

$java = "Ufo.post('dialog','mo_symbol/modify.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','onsubmit'=>$java]);
if($symbol_id) $form->hidden('symbol_id',$symbol_id);

$modal = $form->modal();

$header = $modal->header();
$header->title((($symbol_id) ? 'Edit' : 'Add'));
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$fg = $mbody->form_horizontal();
$fg->label('Navn','name');
$fg->input('name',$rs->name)->at(['required']);

$fg = $mbody->form_horizontal();
$fg->label('Description','description');
$fg->input('description',$rs->description);

$fg = $mbody->form_horizontal();
$fg->label('Data','data');
$fg->textarea('data',$rs->data);

$footer = $modal->footer();
$footer->button('Save')->at(['type'=>'submit']);
$footer->button('Close',null,'secondary')->at(['onclick'=>"Ufo.abort('dialog')"]);

Ufo::output('dialog',$doc);
