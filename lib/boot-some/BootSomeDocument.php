<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSome extends \TRP\HealDocument\HealDocument {
	public static $doc;
	public static $head;
	public static $body;
	public static $dialog;

	public static function document($title,$language = null,$autoecho = true) {
		self::$doc = new BootSome();
		list(self::$head,self::$body) = self::$doc->html($title,$language);
		self::$head->metadata('viewport','width=device-width, initial-scale=1');
		self::$body->at(['id'=>'body','onload'=>'BootSome.load();']);
		self::$dialog = self::$body->el('dialog',['id'=>'dialog']);
		if($autoecho) register_shutdown_function(['BootSome','document_end']);
	}

	public function html($title, $language = null, $charset='UTF-8'){
		$html = $this->el('html');
		$html->at(['lang'=>$language ? $language : 'en']);
		return [$html->head($title, $charset),$html->el('body')];
	}

	public static function document_end() {
		echo self::$doc;
	}
}
