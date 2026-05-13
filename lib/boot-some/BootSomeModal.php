<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeModal extends \TRP\HealDocument\Plugin {
	public static function modal($parent, $xl = true){
		$dialog = $parent->el('div',['class'=>'modal']);
		if($xl) $dialog->at(['class'=>'modal-xl'],true);

		$dialog = $dialog->el('div',['class'=>'modal-dialog']);

		$element = new BootSomeModal($dialog);
		$parent->el('div',['class'=>'modal-backdrop']);
		return $element;
	}

	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'modal-content']);
	}

	public function modalgroup($id = 'dialogbody'){
		$element = new BootSomeModal($this->primary_element);
		$element->at(['id'=>$id]);
		return $element;
	}

	public function header(){
		return new BootSomeModalHeader($this->primary_element);
	}

	public function footer(){
		$element = $this->primary_element->el('div',['class'=>'modal-footer']);
		return $element;
	}

	public function body($id = null){
		if(!isset($id)) {
			return $this->primary_element->el('div',['class'=>'modal-body']);
		} else {
			$body = $this->primary_element->el('form',['class'=>'modal-body']);
			if(!empty($id)) $body->at(['id'=>$id]);
			return $body;
		}
	}
}

class BootSomeModalHeader extends \TRP\HealDocument\Wrapper {
	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'modal-header']);
	}

	public function title($text){
		return $this->primary_element->el('h3',['class'=>'modal-title'])->te($text);
	}

	public function close(){
		return $this->primary_element->el('button',['class'=>'btn-close','type'=>'button']);
	}
}

class BootSomeModalGroup extends BootSome {
	public function header(){
		return new BootSomeModalHeader($this);
	}

	public function footer(){
		$element = $this->el('div',['class'=>'modal-footer']);
		return $element;
	}

	public function body($id = null){
		if(!isset($id)) {
			return $this->el('div',['class'=>'modal-body']);
		} else {
			$body = $this->el('form',['class'=>'modal-body']);
			if(!empty($id)) $body->at(['id'=>$id]);
			return $body;
		}
	}
}
