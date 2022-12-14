<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
class BootSomeCard extends BootSomeElement {
	public function header(){
		$element = new BootSomeElement('div');
		$this->appendChild($element);
		$element->at(['class'=>'card-header']);
		return $element;
	}

	public function body(){
		$element = new BootSomeElement('div');
		$this->appendChild($element);
		$element->at(['class'=>'card-body']);
		return $element;
	}

	public function listgroup(){
		$listgroup = new BootSomeCardListGroup('div');
		$this->appendChild($listgroup);
		$listgroup->at(['class'=>'list-group list-group-flush']);
		return $listgroup;
	}

	public function table(){
		$div = $this->el('div',['class'=>'table-responsive']);
		$element = new BootSomeTable('table');
		$div->appendChild($element);
		$element->at(['class'=>'table']);
		return $element;
	}

	public function footer(){
		$element = new BootSomeElement('div');
		$this->appendChild($element);
		$element->at(['class'=>'card-footer']);
		return $element;
	}
}

class BootSomeCardListGroup extends BootSomeElement {
	public function item($link = null){
		$item = $this->el($link ? 'a' : 'div',['class'=>'list-group-item']);
		if($link) $item->at(['href'=>$link]);
		return $item;
	}
}
