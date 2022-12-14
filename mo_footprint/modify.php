<?php
require_once __DIR__.'/../header.inc.php';

if(isset($_GET['footprint_id']) && $_GET['footprint_id']) {
	$footprint_id = $mysqli->real_escape_string($_GET['footprint_id']);
	$sql = "SELECT `name`,`exclude`,`data` FROM footprint WHERE `id`='$footprint_id'";
	$query = $mysqli->query($sql);
	if(!$rs = $query->fetch_object()) {
		Ufo::abort('dialog');
		exit;
	}
}
else {
	$footprint_id = null;
	$rs = (object) array('name'=>'','exclude'=>0,'data'=>'');
}

$doc = new BootSome();

$java = "Ufo.post('dialog','mo_footprint/modify.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','onsubmit'=>$java]);
if($footprint_id) $form->hidden('footprint_id',$footprint_id);

$modal = $form->modal();

$header = $modal->header();
$header->title((($footprint_id) ? 'Edit' : 'Add'));
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$fg = $mbody->form_horizontal();
$fg->label('Navn','name');
$fg->input('name',$rs->name)->at(['required']);

$fg = $mbody->form_horizontal();
$fg->label('BOM Exclude','exclude');
$fg->checkbox('exclude',$rs->exclude);

$fg = $mbody->form_horizontal();
$fg->label('Data','data');
$fg->textarea('data',$rs->data);

$footer = $modal->footer();
$footer->button('Save')->at(['type'=>'submit']);
$footer->button('Close',null,'secondary')->at(['onclick'=>"Ufo.abort('dialog')"]);

Ufo::output('dialog',$doc);
