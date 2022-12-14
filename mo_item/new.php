<?php
require_once __DIR__.'/../header.inc.php';

$doc = new BootSome();

$java = "Ufo.post('dialog','mo_item/confirm.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','onsubmit'=>$java]);

$modal = $form->modal();

$header = $modal->header();
$header->title('Add');
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$fg = $mbody->form_horizontal();
$fg->label('Group','group');
$select = $fg->select('group')->at(['required']);
$sql = "SELECT `group` as id,CONCAT(`group`,' ',`name`) as name
	FROM structure WHERE `group` LIKE '%x' ORDER BY `group`";
$query = $mysqli->query($sql);
$select->option(null);
$select->options($query);

$fg = $mbody->form_horizontal();
$fg->label('E24','e24');
$select = $fg->select('e24');
$sql = "SELECT `number` as id,CONCAT(`number`,' - ',`value`) as name FROM structure_e24 ORDER BY `number`";
$query = $mysqli->query($sql);
$select->option(null);
$select->options($query);

$footer = $modal->footer();
$footer->button('Proceed')->at(['type'=>'submit']);
$footer->button('Close',null,'secondary')->at(['onclick'=>"Ufo.abort('dialog')"]);

Ufo::output('dialog',$doc);
