<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeTable extends \TRP\HealDocument\Plugin {
	public static function table($parent){
		return new BootSomeTable($parent);
	}

	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'table-responsive'])->el('table',['class'=>'table']);
	}

	public function thead(){
		return new BootSomeTableNode($this->primary_element,'thead');
	}

	public function tbody(){
		return new BootSomeTableNode($this->primary_element,'tbody');
	}

	public function tfoot(){
		return new BootSomeTableNode($this->primary_element,'tfoot');
	}
}

class BootSomeTableNode extends \TRP\HealDocument\Wrapper {
	public function __construct($parent, $type){
		$this->primary_element = $parent->el($type);
	}

	public function tr($color = null){
		return new BootSomeTableRow($this->primary_element, $color);
	}

	public function tr_template($arr){
		$node = new BootSomeTableNode($this->primary_element,'template');
		return $node->at($arr);
	}
}

class BootSomeTableRow extends \TRP\HealDocument\Wrapper {
	public function __construct($parent, $color = null){
		$element = $this->primary_element = $parent->el('tr');
		if($color) $element->at(['class'=>'table-'.$color]);
	}

	public function td($color = null){
		$element = $this->primary_element->el('td');
		if($color) $element->at(['class'=>'table-'.$color]);
		return $element;
	}

	public function th($color = null){
		$element = $this->primary_element->el('th');
		if($color) $element->at(['class'=>'table-'.$color]);
		return $element;
	}
}
