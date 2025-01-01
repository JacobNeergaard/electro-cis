<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeCard extends HealPlugin {
	public static function card($parent){
		return new BootSomeCard($parent);
	}

	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'card']);
	}

	public function header(){
		return $this->primary_element->el('div',['class'=>'card-header']);
	}

	public function body(){
		return $this->primary_element->el('div',['class'=>'card-body']);
	}

	public function listgroup(){
		return new BootSomeCardListGroup($this->primary_element);
	}

	public function footer(){
		return $this->primary_element->el('div',['class'=>'card-footer']);
	}
}

class BootSomeCardListGroup extends HealWrapper {
	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'list-group list-group-flush']);
	}

	public function item($link = null){
		$item = $this->primary_element->el($link ? 'a' : 'div',['class'=>'list-group-item']);
		if($link) $item->at(['href'=>$link]);
		return $item;
	}
}
