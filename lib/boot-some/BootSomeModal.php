<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
trait BootSomeModalNode {
	public function header(){
		$element = new BootSomeModalHeader('div');
		$this->appendChild($element);
		$element->at(['class'=>'modal-header']);
		return $element;
	}

	public function footer(){
		$element = $this->el('div',['class'=>'modal-footer']);
		return $element;
	}

	public function body($id = null){
		if(!isset($id)) {
			 $body = $this->el('div');
		}
		else {
			$body = $this->el('form');
			if(!empty($id)) $body->at(['id'=>$id]);
		}
		$body->at(['class'=>'modal-body']);
		return $body;
	}
}

class BootSomeModal extends BootSomeElement {
	use BootSomeModalNode;

	public function modalgroup($id = 'dialogbody'){
		$element = new BootSomeModal('div');
		$this->appendChild($element);
		$element->at(['id'=>$id]);
		return $element;
	}
}

class BootSomeModalHeader extends BootSomeElement {
	public function title($text){
		return $this->el('h3',['class'=>'modal-title'])->te($text);
	}

	public function close(){
		return $this->el('button',['class'=>'btn-close','type'=>'button']);
	}
}

class BootSomeModalGroup extends BootSome {
	use BootSomeModalNode;
}