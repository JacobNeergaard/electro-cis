<?php
require_once __DIR__.'/../header.inc.php';

if(isset($_GET['group']) && $_GET['group']) {
	$group = $mysqli->real_escape_string($_GET['group']);
	$sql = "SELECT `name`,`description_template` FROM structure WHERE `group`='$group'";
	$query = $mysqli->query($sql);
	if(!$rs = $query->fetch_object()) {
		Ufo::abort('dialog');
		exit;
	}
}
else {
	$group = '';
	$rs = (object) array('name'=>'','description_template'=>'');
}

$doc = new BootSome();

$java = "Ufo.post('dialog','mo_structure/modify.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','onsubmit'=>$java]);
if($group) $form->hidden('org_group',$group);

$modal = $form->modal();

$header = $modal->header();
$header->title((($group) ? 'Edit' : 'Add'));
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$fg = $mbody->form_horizontal();
$fg->label('Group','group');
$fg->input('group',$group)->at(['required','maxlength'=>6,'minlength'=>6,'placeholder'=>'10xxxx']);

$fg = $mbody->form_horizontal();
$fg->label('Navn','name');
$fg->input('name',$rs->name)->at(['required']);

$fg = $mbody->form_horizontal();
$fg->label('Template','description_template');
$fg->input('description_template',$rs->description_template);

$footer = $modal->footer();
$footer->button('Save')->at(['type'=>'submit']);
$footer->button('Close',null,'secondary')->at(['onclick'=>"Ufo.abort('dialog')"]);

Ufo::output('dialog',$doc);
