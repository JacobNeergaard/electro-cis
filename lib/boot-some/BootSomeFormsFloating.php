<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
require_once __DIR__.'/BootSomeFormsInputGroup.php';
class BootSomeFormsFloating extends HealPlugin {
	public static function inputgroup($parent){
		return new BootSomeFormsInputGroup($parent);
	}

	public static function input($parent, $label, $name = null, $value = null){
		return new BootSomeFormsFloatingInput($parent, $label, $name, $value);
	}

	public static function password($parent, $label, $name = null){
		$element = new BootSomeFormsFloatingInput($parent, $label, $name);
		$element->at(['type'=>'password']);
		return $element;
	}

	public static function textarea($parent, $label, $name = null, $value = null){
		return new BootSomeFormsFloatingTextarea($parent, $label, $name, $value);
	}

	public static function file($parent, $label, $name = null, $icon = 'upload'){
		return new BootSomeFormsFloatingFile($parent, $label, $name, $icon);
	}

	public static function select($parent, $label, $name = null){
		return new BootSomeFormsFloatingSelect($parent, $label, $name);
	}

	public static function radio($parent, $label, $name = null, $value = null){
		return new BootSomeFormsFloatingRadio($parent, $label, $name, $value);
	}

	public static function checkbox($parent, $label, $name = null, $checked = false){
		return new BootSomeFormsFloatingCheckbox($parent, $label, $name, $checked);
	}

	public static function tokenselect($parent, $label, $name = null, $include_select = true){
		return new BootSomeFormsFloatingTokenSelect($parent, $label, $name, $include_select);
	}
}

trait BootSomeFormsFloatingInputBasic {
	protected $float_wrapper,$label,$input_group = null;
	public function generate_id($name){
		if(isset($name) && strpos($name, '[') === false){
			$id = $name;
		}
		else {
			$id = 'input_'.base64_encode(random_bytes(6));
		}
		$this->id($id);
	}
	public function get_wrapper(){
		return $this->float_wrapper;
	}
	public function get_input_group(){
		return $this->input_group;
	}
	public function disabled(bool $disable = true){
		if($disable){
			$this->primary_element->at(['disabled']);
		}
		return $this;
	}
	public function id($id) {
		$this->primary_element->at(['id'=>$id]);
		$this->label->at(['for'=>$id]);
	}
}

class BootSomeFormsFloatingInput extends HealWrapper {
	use BootSomeFormsFloatingInputBasic;
	public function __construct($parent, $label, $name = null, $value = null){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		}
		$this->float_wrapper = $parent->el('div',['class'=>'form-floating']);
		$this->primary_element = $this->float_wrapper->el('input',['class'=>'form-control','type'=>'text','placeholder'=>$label]);
		$this->label = $this->float_wrapper->el('label')->te($label);
		$this->generate_id($name);
		if(isset($name)){
			$this->primary_element->at(['name'=>$name]);
		}
		if(isset($value)){
			$this->primary_element->at(['value'=>$value]);
		}
	}

	public function datalist($datalist){
		$list_id = 'datalist_'.base64_encode(random_bytes(6));
		$this->primary_element->at(['list'=>$list_id]);
		$list = $this->float_wrapper->el('datalist',['id'=>$list_id]);
		foreach($datalist as $option){
			$list->el('option',['value'=>$option]);
		}
		return $this;
	}

}

class BootSomeFormsFloatingTextarea extends HealWrapper {
	use BootSomeFormsFloatingInputBasic;
	public function __construct($parent, $label, $name = null, $value = null){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		}
		$this->float_wrapper = $parent->el('div',['class'=>'form-floating']);
		$this->primary_element = $this->float_wrapper->el('textarea',['class'=>'form-control','placeholder'=>$label]);
		$this->label = $this->float_wrapper->el('label')->te($label);
		$this->generate_id($name);
		if(isset($name)){
			$this->primary_element->at(['name'=>$name]);
		}
		if(isset($value)){
			$this->primary_element->te($value);
		}
	}
}

class BootSomeFormsFloatingFile extends HealWrapper {
	use BootSomeFormsFloatingInputBasic;
	private $form_control, $button;
	public function __construct($parent, $label, $name = null, $icon = null){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		} else {
			$this->input_group = new BootSomeFormsInputGroup($parent);
		}
		$this->float_wrapper = $this->input_group->el('div',['class'=>'form-floating']);

		$onchange = "this.parentElement.querySelector('input[type=text]').value=this.files[0]?this.files[0].name:'';";
		$this->primary_element = $this->float_wrapper->el('input',['type'=>'file','class'=>'d-none','onchange'=>$onchange]);

		$js = "this.parentElement.parentElement.querySelector('input[type=file]').click();";
		$this->form_control = $this->float_wrapper->el('input',['type'=>'text','readonly','class'=>'form-control','placeholder'=>$label,'onclick'=>$js]);
		$this->label = $this->float_wrapper->el('label')->te($label);
		$this->generate_id($name);
		if(isset($icon)){
			$js = "this.parentElement.querySelector('input[type=file]').click();event.preventDefault();";
			$this->button = $this->input_group->button(null, $icon, 'outline-secondary')->at(['onclick'=>$js]);
		}

		if(isset($name)){
			$this->primary_element->at(['name'=>$name]);
		}
	}

	public function onchange($js){
		$onchange = "this.parentElement.querySelector('input[type=text]').value=this.files[0]?this.files[0].name:'';";
		$this->primary_element->at(['onchange'=>$onchange.$js]);
		return $this;
	}

	public function disabled(bool $disable = true){
		if($disable){
			$this->primary_element->at(['disabled']);
			$this->form_control->at(['disabled']);
			$this->button->at(['disabled']);
		}
		return $this;
	}
}

class BootSomeFormsFloatingCheckbox extends HealWrapper {
	use BootSomeFormsFloatingInputBasic;
	private $form_control;
	public function __construct($parent, $label, $name = null, $checked = false){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		} else {
			$this->input_group = new BootSomeFormsInputGroup($parent);
		}
		$this->float_wrapper = $this->input_group->el('div',['class'=>'form-floating']);
		$this->form_control = $this->float_wrapper->el('div',['class'=>'form-control bootsome-checkbox']);
		$div = $this->form_control->el('div',['class'=>'form-check']);
		$this->primary_element = $div->el('input',['class'=>'form-check-input','type'=>'checkbox']);
		$this->label = $div->el('label',['class'=>'form-check-label'])->te($label);
		$this->generate_id($name);
		if($checked) $this->primary_element->at(['checked']);
		if(isset($name)){
			$this->primary_element->at(['name'=>$name]);
		}
	}

	public function disabled(bool $disable = true){
		if($disable){
			$this->primary_element->at(['disabled']);
			$this->form_control->at(['class'=>'bootsome-disabled'],true);
		}
	}
}

class BootSomeFormsFloatingSelect extends HealWrapper {
	use BootSomeFormsFloatingInputBasic;
	protected $option_elements = [], $option_names = [], $option_value_elements = [];
	public function __construct($parent, $label, $name = null){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		}
		$this->float_wrapper = $parent->el('div',['class'=>'form-floating']);
		$this->primary_element = $this->float_wrapper->el('select',['class'=>'form-select']);
		$this->label = $this->float_wrapper->el('label')->te($label);
		$this->generate_id($name);
		if(isset($name)){
			$this->primary_element->at(['name'=>$name]);
		}
	}

	public function option($text, $value = null, $selected = false){
		$this->option_elements[] = $option = $this->primary_element->el('option')->te($text);
		if($selected) $option->at(['selected']);
		if(isset($value)){
			$option->at(['value'=>$value]);
			$this->option_names[$value] = $text;
			$this->option_value_elements[$value] = $option;
		}
		return $option;
	}

	public function options($iterable, $selected = null, $strict_compare = false){
		if(is_a($iterable, 'mysqli_result')){
			foreach($iterable as $row){
				$is_selected = isset($selected) && ($strict_compare ? $selected === $row['id'] : $selected == $row['id']);
				$this->option($row['name'],$row['id'],$is_selected);
			}
		} else {
			foreach($iterable as $value => $text){
				$is_selected = isset($selected) && ($strict_compare ? $selected === $value : $selected == $value);
				$this->option($text, $value, $is_selected);
			}
		}
		return $this;
	}

	public function select($value, $insert_option = false){
		if(isset($this->option_value_elements[$value])){
			$this->option_value_elements[$value]->at(['selected']);
		} elseif($insert_option) {
			$text = $insert_option === true ? $value : $insert_option;
			$this->option($text, $value, true);
		}
	}

	public function foreach_option($callback){
		foreach($this->option_elements as $option){
			$callback($option);
		}
	}

	protected function get_option_label($value){
		return $this->option_names[$value] ?? '';
	}
}

class BootSomeFormsFloatingRadio extends BootSomeFormsFloatingSelect {
	private $value, $name, $onchange, $disabled = false;
	public function __construct($parent, $label, $name = null, $value = null){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		} else {
			$this->input_group = new BootSomeFormsInputGroup($parent);
		}
		$this->float_wrapper = $this->input_group->el('div',['class'=>'form-floating']);
		$this->primary_element = $this->float_wrapper->el('div',['class'=>'form-control bootsome-radio']);
		$this->label = $this->float_wrapper->el('label')->te($label);
		$this->name = $name;
		$this->value = $value;
	}

	public function option($text, $value = null, $checked = false, $id = null){
		$id = $id ?? 'radio_'.base64_encode(random_bytes(6));
		$wrapper = $this->primary_element->el('div',['class'=>'form-check']);
		$this->option_elements[] = $option = $wrapper->el('input',['class'=>'form-check-input','type'=>'radio','id'=>$id]);
		$wrapper->el('label',['class'=>'form-check-label','for'=>$id])->te($text);
		if(isset($value)){
			$option->at(['value'=>$value]);
			$this->option_names[$value] = $text;
			$this->option_value_elements[$value] = $option;
		}
		if((isset($this->value) && $this->value == $value) || $checked){
			$option->at(['checked']);
		}
		if(!empty($this->name)){
			$option->at(['name'=>$this->name]);
		}
		if(!empty($this->onchange)){
			$option->at(['onchange'=>$this->onchange]);
		}
		if($this->disabled){
			$option->at(['disabled']);
		}
		return $option;
	}

	public function disabled(bool $disable = true){
		$this->disabled = true;
		if($disable){
			$this->primary_element->at(['class'=>'bootsome-disabled'],true);
			foreach($this->option_elements as $option){
				$option->at(['disabled']);
			}
		}
		return $this;
	}

	public function onchange($js){
		$this->onchange = $js;
		foreach($this->options as $option){
			$option->at(['onchange'=>$js]);
		}
		return $this;
	}
}

class BootSomeFormsFloatingTokenSelect extends BootSomeFormsFloatingSelect {
	protected $container, $name, $token_class = 'btn btn-outline-secondary',
			$disabled = false,
			$onchange_select = 'BootSomeTokenSelect.set(this);',
			$onchange_token ='BootSomeTokenSelect.remove(this,event);',
			$token_elements = [], $token_value_elements = [];
	public function __construct($parent, $label, $name = null, $include_select = true){
		if(is_a($parent, '\BootSomeFormsInputGroup')){
			$this->input_group = $parent;
		}
		$this->float_wrapper = $parent->el('div',['class'=>'form-floating bootsome-token-field']);
		$this->primary_element = $this->float_wrapper->el('select',['class'=>'form-select bootsome-token-select','onchange'=>$this->onchange_select]);
		$this->label = $this->float_wrapper->el('label')->te($label);
		$this->generate_id($name);
		$this->name = $name;
		$this->option('');
		$this->container = $this->float_wrapper->el('div',['class'=>'bootsome-token-container']);
		$template = $this->container->el('template',['data-tmpl-name'=>'bootsome-token-template']);
		$this->build_token($template, '', '');
	}

	public function disabled(bool $disable = true){
		if($disable){
			$this->primary_element->at(['disabled']);
			foreach($this->token_elements as $token){
				$token->at(['onclick'=>'']);
			}
		}
		$this->disabled = $disable;
		return $this;
	}

	public function onchange($js, $include_builtin_js = true, $postfix = ''){
		if($include_builtin_js){
			$this->onchange_select = 'BootSomeTokenSelect.set(this);'.$js.$postfix;
			$this->onchange_token = $js.'BootSomeTokenSelect.remove(this,event);'.$postfix;
		} else {
			$this->onchange_select = $this->onchange_token = $js.$postfix;
		}
		$this->primary_element->at(['onchange'=>$this->onchange_select]);
		foreach($this->token_elements as $token){
			$token->at(['onclick'=>$this->onchange_token]);
		}
		return $this;
	}

	public function token_class($class, $overwrite = false){
		if($overwrite){
			$this->token_class = $class;
		} else {
			$this->token_class = $this->token_class.' '.$class;
		}
		foreach($this->token_elements as $token){
			$token->at(['class'=>$this->token_class]);
		}
	}

	public function token($value, $label = null){
		$token = $this->build_token($this->container, $value, $label ?? $this->get_option_label($value));
		$this->token_value_elements[$value] = $token;
		if(isset($this->option_value_elements[$value])){
			$this->option_value_elements[$value]->at(['disabled']);
		}
		return $token;
	}

	private function build_token($parent, $value, $label){
		$token = $parent->el('button',[
			'class'=>$this->token_class,
			'data-token-value'=>$value,
			'data-tmpl'=>'data-tokenValue:value',
			'type'=>'button',
		]);
		if(!$this->disabled){
			$token->at(['onclick'=>$this->onchange_token]);
		}
		$token->el('span',['data-tmpl'=>'content:label'])->te($label);
		$token_input = $token->el('input',['type'=>'hidden','value'=>$value,'data-tmpl'=>'value:value']);
		if(!empty($this->name)){
			$token_input->at(['name'=>$this->name.'[]']);
		}
		$this->token_elements[] = $token;
		return $token;
	}

	public function tokens($iterable,$associative = false){
		if(is_a($iterable, 'mysqli_result')){
			foreach($iterable as $row){
				$this->token($row['id'],$row['name'] ?? null);
			}
		} else {
			foreach($iterable as $key => $value){
				if($associative){
					$this->token($key, $value);
				} else {
					$this->token($value);
				}
			}
		}
		return $this;
	}
}
