<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeHead extends \TRP\HealDocument\Plugin {
	public static function head($parent, $title = null, $charset = 'UTF-8'){
		$head = $parent->el('head');
		if(!empty($title)) $head->el('title')->te($title);
		$head->el('meta',['charset'=>$charset]);
		return $head;
	}

	public static function metadata($parent, $name, $content){
		return $parent->el('meta',['name'=>$name,'content'=>$content]);
	}

	public static function link($parent, $rel, $href){
		return $parent->el('link',['rel'=>$rel,'href'=>$href]);
	}

	public static function css($parent, $path){
		return $parent->link('stylesheet',$path);
	}
}
