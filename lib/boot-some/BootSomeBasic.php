<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeBasic extends \TRP\HealDocument\Plugin {
	public static function img($parent, $src, $alt, $fluid = false){
		$img = $parent->el('img',['src'=>$src,'alt'=>$alt]);
		if($fluid) $img->at(['class'=>'img-fluid']);
		return $img;
	}

	public static function p($parent, $text, $break_on_newline = true){
		return $parent->el('p')->te($text, $break_on_newline);
	}

	public static function a($parent, $href, $text = ''){
		$a = $parent->el('a', ['href'=>$href]);
		if(!empty($text)) $a->te($text);
		return $a;
	}

	public static function button($parent, $text, $icon = null, $color = 'primary', $link = null){
		$button = $parent->el($link ? 'a' : 'button',['class'=>'btn btn-'.$color]);
		if($link) $button->at(['href'=>$link]);
		else $button->at(['type'=>'button']);
		if($icon) $button->el('i',['class'=>'fas fa-'.$icon]);
		if($text) $button->el('span')->te($text);
		return $button;
	}

	public static function hidden($parent, $name, $value){
		return $parent->el('input',['type'=>'hidden','name'=>$name,'value'=>$value,'id'=>$name]);
	}

	public static function progress($parent, $value){
		$div = $parent->el('div',['class'=>'progress']);
		$div->el('div',['class'=>'progress-bar','style'=>'width: '.$value.'%']);
		return $div;
	}

	public static function form($parent, $action = '', $method = 'get'){
		$attr = [];
		if(!empty($action)){
			$attr['action'] = $action;
			$attr['method'] = $method;
		} else {
			$attr['onsubmit'] = 'return false;';
		}
		return $parent->el('form', $attr);
	}
}
