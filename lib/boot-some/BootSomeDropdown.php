<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeDropDown extends \TRP\HealDocument\Plugin {
	public static function dropdown($parent, $text,$color = 'primary'){
		$div = $parent->el('div', ['class'=>'dropdown']);
		$div->el('button',['class'=>'btn btn-'.$color.' dropdown-toggle','data-bs-toggle'=>'dropdown'])->te($text);

		return new BootSomeDropDown($div);
	}

	public function __construct($parent){
		$this->primary_element = $parent->el('ul',['class'=>'dropdown-menu']);
	}

	public function a($href, $text = '', $active = false){
		$a = $this->primary_element->el('li')->el('a',['href'=>$href,'class'=>'dropdown-item'])->te($text);
		if($active) $a->at(['class'=>'active'], true);
		return $a;
	}
}
