<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
class BootSomeFormsGroup extends BootSomeElement {
	public function text($text){
		return $this->el('small',['class'=>'form-text'])->te($text);
	}
}

class BootSomeFormsHorizontal extends BootSomeElement {
	use BootSomeFormFields;

	public $col = 3;
	private $wrap = null;

	private function wrap(){
		if(!$this->wrap){
			$element = new BootSomeElement('div');
			$this->appendChild($element);
			$element->at(['class'=>'mb-2 col-sm-'.(12-$this->col)]);
			$this->wrap = $element;
		}
	}

	public function label($text = null, $for = null){
		$label = parent::label($text,$for);
		$label->at(['class'=>'col-sm-'.$this->col], true);
		return $label;
	}

	public function text($text){
		$this->wrap->el('class',['class'=>'form-text'])->te($text);
	}
}

class BootSomeFormsInline extends BootSomeElement {
	use BootSomeFormFields;

	private $wrap = null;

	private function wrap(){
		$this->wrap = new BootSomeElement('div');
		$this->appendChild($this->wrap);
		$this->wrap->at(['class'=>'col-12']);
	}
}

class BootSomeFormsInputGroup extends BootSomeElement {
	public function text($text){
		return $this->el('div',['class'=>'input-group-text'])->te($text);
	}
}

trait BootSomeFormFields {
	public function input($name, $value = NULL){
		$this->wrap();
		return $this->wrap->input($name, $value);
	}

	public function password($name){
		$this->wrap();
		return $this->wrap->password($name);
	}

	public function file($name, $multiple = false){
		$this->wrap();
		return $this->wrap->file($name, $multiple);
	}

	public function select($name){
		$this->wrap();
		return $this->wrap->select($name);
	}

	public function textarea($name, $content = ''){
		$this->wrap();
		return $this->wrap->textarea($name, $content);
	}

	public function checkbox($name, $checked = false, $value = 'on', $text = null, $inline = false){
		$this->wrap();
		return $this->wrap->checkbox($name, $checked, $value, $text, $inline);
	}

	public function radio($name, $value, $checked = false, $text = null, $inline = false){
		$this->wrap();
		return $this->wrap->radio($name, $value, $checked, $text, $inline);
	}

	public function button($text, $icon = NULL, $color = 'primary', $link = null){
		$this->wrap();
		return $this->wrap->button($text, $icon, $color, $link);
	}

	public function inputgroup(){
		$this->wrap();
		return $this->wrap->inputgroup();
	}

	public function date($name, $value = null, $include_popover = true){
		$this->wrap();
		return $this->wrap->date($name, $value, $include_popover);
	}
}
