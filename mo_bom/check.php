<?php
require_once __DIR__.'/../header.inc.php';

$doc = new BootSome();
$doc->el('h1')->te('BOM Check');

$div = $doc->el('div')->at(['id'=>'output']);

$form = $div->form()->at(['id'=>'convert']);

$group = $form->form_horizontal(4);
$group->label('Input','file');
$group->file('file')->at(['onchange'=>"Ufo.post('main','mo_bom/check.script.php','convert')"],HEAL_ATTR_APPEND);

Ufo::output('content',$doc);
