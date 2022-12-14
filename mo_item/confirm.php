<?php
require_once __DIR__.'/../header.inc.php';

if(isset($_POST['group']) && $_POST['group']) {
	$value = '';
	
	$group = $mysqli->real_escape_string($_POST['group']);
	$sql = "SELECT description_template FROM `structure` WHERE `group`='$group'";
	$description = $mysqli->query($sql)->fetch_object()->description_template;
	
	$group = substr($group,0,strpos($group,'x'));
	
	if(strlen($group)==3 && !empty($_POST['e24'])) {
		$e24 = $mysqli->real_escape_string($_POST['e24']);
		$sql = "SELECT value FROM `structure_e24` WHERE `number`='$e24'";
		$value = $mysqli->query($sql)->fetch_object()->value;
		
		$item = (int) $group.$_POST['e24'];
	}
	else {
		$sql = "SELECT item.item FROM item WHERE item.item LIKE '$group%' ORDER BY item";
		$query = $mysqli->query($sql);
		
		$item = 0;
		while($rs = $query->fetch_object()) {
			$test = (int) substr($rs->item,strlen($group));
			if($test>$item) {
				break;
			}
			else {
				$item = $test+1;
			}
		}
		$item = $group.str_pad($item,6-strlen($group),'0',STR_PAD_LEFT);
	}
}
else {
	Ufo::abort('dialog');
	exit;
}

$doc = new BootSome();

$java = "Ufo.post('dialog','mo_item/confirm.script.php','dialogform');return false;";
$form = $doc->form()->at(['id'=>'dialogform','onsubmit'=>$java]);
if($group) $form->hidden('org_group',$group);

$modal = $form->modal();

$header = $modal->header();
$header->title('Confirm');
$header->close()->at(['onclick'=>"Ufo.abort('dialog')"]);

$mbody = $modal->body();

$fg = $mbody->form_horizontal();
$fg->label('ItemNo','item');
$fg->input('item',$item)->at(['required','maxlength'=>6,'minlength'=>6]);

$fg = $mbody->form_horizontal();
$fg->label('Description','description');
$fg->input('description',$description)->at(['required']);

$fg = $mbody->form_horizontal();
$fg->label('Value','value');
$fg->input('value',$value)->at(['required']);

$footer = $modal->footer();
$footer->button('Save')->at(['type'=>'submit']);
$footer->button('Close',null,'secondary')->at(['onclick'=>"Ufo.abort('dialog')"]);

Ufo::output('dialog',$doc);
