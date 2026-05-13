<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

require_once __DIR__.'/BootSomeLayout.php';

class BootSomeFormsInputGroup extends \TRP\HealDocument\Wrapper {
	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'input-group']);
	}

	public function text($text = null){
		$div = $this->primary_element->el('div',['class'=>'input-group-text']);
		if(!empty($text)){
			$div->te($text);
		}
		return $div;
	}

	public function icon($icon){
		$element = $this->text();
		BootSomeLayout::icon($element,$icon);
	}
}
