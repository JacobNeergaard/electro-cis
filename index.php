<?php
require_once __DIR__.'/header.inc.php';

BootSome::document(TITLE,'en');

$lastpage = $_COOKIE['lastpage'] ?? __ROOT__.'/mo_item/list.php';

head();
BootSome::$body->at(['onload'=>"Ufo.get('main','$lastpage');"],true);
navbar();

BootSome::$body->container(false,'main')->at(['id'=>'content']);
