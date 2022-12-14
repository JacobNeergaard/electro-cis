<?php
require_once __DIR__.'/../header.inc.php';

$item = $mysqli->real_escape_string($_GET['item']);
$sql = "SELECT `item`,`description`,`value`,`symbol_id`,`footprint_id`,
		IF(`datasheet`!='',1,0) as datasheet,`manufacturer`,`partnumber`,`mouser`
	FROM item WHERE `item`='$item'";
$query = $mysqli->query($sql);
if(!$rs = $query->fetch_object()) {
	Ufo::abort('dialog');
	exit;
}

$doc = new BootSome();

$java = "Ufo.post('dialog','mo_item/edit.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','enctype'=>'multipart/form-data','onsubmit'=>$java]);
$form->hidden('item',$item);

$modal = $form->modal();

$header = $modal->header();
$header->title('Edit');
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$fg = $mbody->form_horizontal();
$fg->label('ItemNo');
$fg->input(null,$item)->at(['readonly']);

$fg = $mbody->form_horizontal();
$fg->label('Description','description');
$fg->input('description',$rs->description)->at(['required']);

$fg = $mbody->form_horizontal();
$fg->label('Value','value');
$fg->input('value',$rs->value)->at(['required']);

$fg = $mbody->form_horizontal();
$fg->label('Symbol','symbol_id');
$select = $fg->select('symbol_id');
$sql = "SELECT id,name FROM symbol ORDER BY name";
$query = $mysqli->query($sql);
$select->option(null);
$select->options($query,$rs->symbol_id);

$fg = $mbody->form_horizontal();
$fg->label('Footprint','footprint_id');
$select = $fg->select('footprint_id');
$sql = "SELECT id,name FROM footprint ORDER BY name";
$query = $mysqli->query($sql);
$select->option(null);
$select->options($query,$rs->footprint_id);

$group = $mbody->form_horizontal();
$group->label('Datasheet','file');
$group->file('file')->at(['accept'=>'application/pdf']);
$group->text('Maksimum 10Mb');

if($rs->datasheet) {
	$group = $mbody->form_horizontal();
	$group->label('');
	$group->button('Download',null,'primary','mo_item/datasheet.php?item='.$item)->at(['target'=>'_blank']);
}

$fg = $mbody->form_horizontal();
$fg->label('Manufacturer','manufacturer');
$fg->input('manufacturer',$rs->manufacturer)->at(['required','maxlength'=>30]);

$fg = $mbody->form_horizontal();
$fg->label('Part number','partnumber');
$fg->input('partnumber',$rs->partnumber)->at(['required','maxlength'=>30]);

$fg = $mbody->form_horizontal();
$fg->label('Mouser','mouser');
$fg->input('mouser',$rs->mouser)->at(['required','maxlength'=>20]);

$footer = $modal->footer();
$footer->button('Save')->at(['type'=>'submit']);
$footer->button('Close',null,'secondary')->at(['onclick'=>"Ufo.abort('dialog')"]);

Ufo::output('dialog',$doc);
