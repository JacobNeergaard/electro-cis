<?php
error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Copenhagen');

define('TITLE','Electro :: CIS');

require_once __DIR__.'/lib/heal-document/HealDocument.php';
require_once __DIR__.'/lib/boot-some/BootSome.php';
require_once __DIR__.'/lib/ufo-ajax/ufo.php';

require_once __DIR__.'/config.inc.php';

$mysqli = new mysqli(DBHOST,DBUSER,DBPASS,DBBASE);
$mysqli->set_charset('utf8mb4');

function head() {
	BootSome::$head->link('shortcut icon','#');
	
	BootSome::$head->css('lib/bootstrap/bootstrap.min.css');
	BootSome::$head->css('lib/fontawesome/css/all.min.css');
	BootSome::$head->css('lib/boot-some//BootSome.css');
	
	BootSome::$head->el('script',['src'=>'lib/bootstrap/bootstrap.bundle.min.js']);
	BootSome::$head->el('script',['src'=>'lib/ufo-ajax/ufo.js']);
	BootSome::$head->el('script',['src'=>'lib/boot-some/BootSome.js']);
	BootSome::$head->el('script',['src'=>'lib/boot-some/BootSomeForms.js']);
}

function navbar() {
	$navbar = BootSome::$body->navbar(false);

	$navbar->brand()->te(TITLE);
	$navbar->toggler();

	$collapse = $navbar->collapse();
	$nav = $collapse->nav();

	$nav->a('#','Items')->at(['onclick'=>"Ufo.get('main','".ROOT."mo_item/list.php');"]);
	$nav->a('#','Structure')->at(['onclick'=>"Ufo.get('main','".ROOT."mo_structure/list.php');"]);
	$nav->a('#','Symbol')->at(['onclick'=>"Ufo.get('main','".ROOT."mo_symbol/list.php');"]);
	$nav->a('#','Footprint')->at(['onclick'=>"Ufo.get('main','".ROOT."mo_footprint/list.php');"]);
	$nav->a('#','BOM Check')->at(['onclick'=>"Ufo.get('main','".ROOT."mo_bom/check.php');"]);
}
