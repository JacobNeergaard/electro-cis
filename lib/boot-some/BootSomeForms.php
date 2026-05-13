<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

require_once __DIR__.'/BootSomeFormsInputGroup.php';

class BootSomeForms extends \TRP\HealDocument\Plugin {
	public static function form_row($parent){
		//Legacy Support
		return $parent->el('div',['class'=>'row']);
	}

	public static function form_group($parent, $col = null,$left = false){
		return new BootSomeFormsGroup($parent, $col, $left);
	}

	public static function form_horizontal($parent, $col = null){
		return new BootSomeFormsHorizontal($parent, $col);
	}

	public static function form_inline($parent){
		return new BootSomeFormsInline($parent);
	}

	public static function inputgroup($parent){
		return new BootSomeFormsInputGroup($parent);
	}

	public static function label($parent, $text = null, $for = null){
		$label = $parent->el('label',['class'=>'form-label']);
		if(isset($text)) $label->te($text,true);
		if(isset($for)) $label->at(['for'=>$for]);
		return $label;
	}

	public static function input($parent, $name, $value = null){
		$input = $parent->el('input',['id'=>$name,'name'=>$name,'class'=>'form-control']);
		if(isset($value)) $input->at(['value'=>$value]);
		return $input;
	}

	public static function select($parent, $name){
		return $parent->el('select',['id'=>$name,'name'=>$name,'class'=>'form-select']);
	}

	public static function optgroup($parent, $label){
		return $parent->el('optgroup',['label'=>$label]);
	}

	public static function option($parent, $text, $value = null, $selected = false){
		$option = $parent->el('option')->te($text);
		if($selected) $option->at(['selected']);
		if(isset($value)) $option->at(['value'=>$value]);
		return $option;
	}

	public static function options($parent, $iterable, $selected = null, $strict_compare = false){
		if(is_a($iterable, 'mysqli_result')){
			foreach($iterable as $row){
				$is_selected = isset($selected) && ($strict_compare ? $selected === $row['id'] : $selected == $row['id']);
				$parent->option($row['name'],$row['id'],$is_selected);
			}
		} else {
			foreach($iterable as $value => $text){
				$is_selected = isset($selected) && ($strict_compare ? $selected === $value : $selected == $value);
				$parent->option($text, $value, $is_selected);
			}
		}
		return $parent;
	}

	public static function password($parent, $name){
		return $parent->el('input',['type'=>'password','id'=>$name,'name'=>$name,'class'=>'form-control']);
	}

	public static function file($parent, $name, $multiple = false){
		$input = $parent->el('input',['class'=>'form-control','type'=>'file','id'=>$name]);
		if($multiple) $input->at(['multiple'])->at(['name'=>$name.'[]']);
		else $input->at(['name'=>$name]);
		return $input;
	}

	public static function textarea($parent, $name, $content = ''){
		return $parent->el('textarea',['id'=>$name,'name'=>$name,'class'=>'form-control'])->te($content);
	}

	public static function checkbox($parent, $name, $checked = false, $value = 'on', $text = null, $inline = false){
		if(!$inline) {
			$div = $parent->el('div',['class'=>'form-check']);
		}
		else {
			if(!isset($parent->inlinewrap)) {
				$parent->inlinewrap = $parent->el('div');
			}
			$div = $parent->inlinewrap->el('div',['class'=>'form-check form-check-inline']);
		}

		$checkbox = $div->el('input',['type'=>'checkbox','name'=>$name,'class'=>'form-check-input']);
		if($checked) $checkbox->at(['checked']);
		if($value != 'on') $checkbox->at(['value'=>$value]);
		if(empty($text)) {
			$checkbox->at(['class'=>'position-static'], true);
		}
		else {
			if(substr($name, -2)=='[]'){
				$id = substr($name, 0, -2).'_'.mt_rand(10000,99999);
			} else {
				$id = $name;
			}
			$checkbox->at(['id'=>$id]);
			$div->el('label', ['for'=>$id,'class'=>'form-check-label'])->te($text);
		}
		return $checkbox;
	}

	public static function radio($parent, $name, $value, $checked = false, $text = null, $inline = false){
		if(!$inline) {
			$div = $parent->el('div',['class'=>'form-check']);
		}
		else {
			if(!isset($parent->inlinewrap)) {
				$parent->inlinewrap = $parent->el('div');
			}
			$div = $parent->inlinewrap->el('div',['class'=>'form-check form-check-inline']);
		}

		$id = "$name:$value";
		$radio = $div->el('input',['type'=>'radio','name'=>$name,'id'=>$id,'value'=>$value,'class'=>'form-check-input']);
		if($checked) $radio->at(['checked']);
		if(empty($text)) {
			$radio->at(['class'=>'position-static'], true);
		}
		else {
			if(substr($name, -2)=='[]'){
				$id = substr($name, 0, -2).'_'.mt_rand(10000,99999).':'.$value;
				$checkbox->at(['id'=>$id]);
			}
			$div->el('label', ['for'=>$id,'class'=>'form-check-label'])->te($text);
		}
		return $radio;
	}

	public static function date($parent, $name, $value = null){
		$onclick = "if(typeof BootSomeForms!='undefined'&&typeof BootSomeForms.date=='function')BootSomeForms.date(this);";
		$input = $parent->el('input',['type'=>'date','name'=>$name,'id'=>$name,'class'=>'form-control','onclick'=>$onclick]);
		if(isset($value)) $input->at(['value'=>$value]);
		return $input;
	}
}

class BootSomeFormsGroup extends \TRP\HealDocument\Wrapper {
	public $inlinewrap;

	public function __construct($parent, $col = null, $left = false){
		$this->primary_element = $parent->el('div',['class'=>'mb-2']);
		if($left) $this->primary_element->at(['class'=>'text-end'],true);
		if($col) {
			$this->primary_element->at(['class'=>'col-md-'.(int) $col], true);
		}
	}

	public function text($text,$color = null){
		$text = $this->primary_element->el('small',['class'=>'form-text'])->te($text);
		if($color) $text->at(['class'=>'text-'.$color],true);
		return $text;
	}
}

class BootSomeFormsHorizontal extends \TRP\HealDocument\Wrapper {
	use BootSomeFormFields;

	public $col = 3;
	private $wrap = null;

	public function __construct($parent, $col = null){
		$this->primary_element = $parent->el('div',['class'=>'row']);
		if($col) $this->col = (int) $col;
	}

	public function wrap(){
		if(!isset($this->wrap)){
			$this->wrap = $this->primary_element->el('div',['class'=>'mb-2 col-sm-'.(12-$this->col)]);
		}
		return $this->wrap;
	}

	public function label($text = null, $for = null){
		$label = parent::label($text,$for);
		$label->at(['class'=>'col-sm-'.$this->col], true);
		return $label;
	}

	public function text($text,$color = null){
		$text = $this->wrap()->el('class',['class'=>'form-text'])->te($text);
		if($color) $text->at(['class'=>'text-'.$color],true);
		return $text;
	}
}

class BootSomeFormsInline extends \TRP\HealDocument\Wrapper {
	use BootSomeFormFields;

	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'row row-cols-sm-auto gx-2 form-inline']);
	}

	public function wrap(){
		return $this->primary_element->el('div',['class'=>'col-12']);
	}
}

trait BootSomeFormFields {
	public function input($name, $value = NULL){
		return $this->wrap()->input($name, $value);
	}

	public function password($name){
		return $this->wrap()->password($name);
	}

	public function file($name, $multiple = false){
		return $this->wrap()->file($name, $multiple);
	}

	public function select($name){
		return $this->wrap()->select($name);
	}

	public function textarea($name, $content = ''){
		return $this->wrap()->textarea($name, $content);
	}

	public function checkbox($name, $checked = false, $value = 'on', $text = null, $inline = false){
		return $this->wrap()->checkbox($name, $checked, $value, $text, $inline);
	}

	public function radio($name, $value, $checked = false, $text = null, $inline = false){
		return $this->wrap()->radio($name, $value, $checked, $text, $inline);
	}

	public function button($text, $icon = NULL, $color = 'primary', $link = null){
		return $this->wrap()->button($text, $icon, $color, $link);
	}

	public function inputgroup(){
		return $this->wrap()->inputgroup();
	}

	public function date($name, $value = null, $include_popover = true){
		return $this->wrap()->date($name, $value, $include_popover);
	}
}
