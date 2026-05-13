<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeNavs extends \TRP\HealDocument\Plugin {
	public static function navs($parent, $type = null){
		return new BootSomeNavs($parent, $type);
	}

	public function __construct($parent, $type = null){
		$this->primary_element = $parent->el('ul',['class'=>'nav']);
		if($type) $this->primary_element->at(['class'=>'nav-'.$type],true);
	}

	public function item(){
		return new BootSomeNavsNode($this->primary_element);
	}
}

class BootSomeNavsNode extends \TRP\HealDocument\Wrapper {
	public function __construct($parent){
		$this->primary_element = $parent->el('li',['class'=>'nav-item']);
	}

	public function a($href, $text = '', $active = false){
		$a = $this->primary_element->el('a',['href'=>$href,'class'=>'nav-link']);
		if(!empty($text)) $a->te($text);
		if($active) $a->at(['class'=>'active'], true);
		return $a;
	}
}
