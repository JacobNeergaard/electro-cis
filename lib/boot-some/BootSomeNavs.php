<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
class BootSomeNavs extends HealElement {
	public function item(){
		$element = new BootSomeNavsNode('li');
		$this->appendChild($element);
		$element->at(['class'=>'nav-item']);
		return $element;
	}
}

class BootSomeNavsNode extends BootSomeElement {
	public function a($href, $text = '', $active = false){
		$a = $this->el('a',['href'=>$href,'class'=>'nav-link']);
		if(!empty($text)) $a->te($text);
		if($active) $a->at(['class'=>'active'], true);
		return $a;
	}
}
