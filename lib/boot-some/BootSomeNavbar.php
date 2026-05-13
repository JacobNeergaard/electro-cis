<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeNavbar extends \TRP\HealDocument\Plugin {
	public static function navbar($parent, $fluid = true, $nav_classes = ''){
		$classes = array_filter(explode(' ','navbar '.$nav_classes));
		$add_expand = true;
		foreach($classes as $class){
			if(substr($class, 0, 14) == 'navbar-expand-'){
				$add_expand = false;
				break;
			}
		}
		if($add_expand){
			$classes[] = 'navbar-expand-md';
		}

		$nav = $parent->el('nav',['class'=>implode(' ',$classes)]);

		return new BootSomeNavbar($nav, $fluid);
	}

	public function __construct($parent, bool $fluid){
		$this->primary_element = $parent->el('div',['class'=>$fluid?'container-fluid':'container']);
	}

	public function nav(){
		return new BootSomeNavbarNav($this->primary_element);
	}

	public function brand($link = null){
		$a = $this->primary_element->el($link ? 'a' : 'div',['class'=>'navbar-brand']);
		if($link) $a->at(['href'=>$link]);
		return $a;
	}

	public function toggler($id = 'navbarMain'){
		$this->primary_element->el('button',[
			'class'=>'navbar-toggler',
			'data-bs-toggle'=>'collapse',
			'data-bs-target'=>'#'.$id
		])->el('span',['class'=>'navbar-toggler-icon']);
	}

	public function collapse($id = 'navbarMain'){
		return new BootSomeNavbarCollapse($this->primary_element, $id);
	}
}

class BootSomeNavbarCollapse extends \TRP\HealDocument\Wrapper {
	public function __construct($parent, $id){
		$this->primary_element = $parent->el('div',[
			'id'=>$id,
			'class'=>'collapse navbar-collapse',
			'data-toggle'=>"collapse",
			'data-target'=>'#'.$id.'.show'
		]);
	}

	public function nav(){
		return new BootSomeNavbarNav($this->primary_element);
	}
}

class BootSomeNavbarNav extends \TRP\HealDocument\Wrapper {
	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'navbar-nav']);
	}

	public function a($href = null, $text = '', $active = false){
		$a = $this->primary_element->el('a',['class'=>'nav-item nav-link']);
		if(!empty($text)) $a->te($text);
		if(isset($href)){
			$a->at(['href'=>$href]);
		} else {
			$a->at(['data-bs-toggle'=>'collapse','data-bs-target'=>'.navbar-collapse.show']);
		}
		if($active) $a->at(['class'=>'active'], true);
		return $a;
	}

	public function dropdown($text, $active = false){
		$div = $this->primary_element->el('div',['class'=>'nav-item dropdown']);
		if($active) $div->at(['class'=>'active'], true);
		$div->el('a',['class'=>'nav-link dropdown-toggle','data-bs-toggle'=>'dropdown','role'=>'button'])->te($text);

		return new BootSomeNavbarDropDown($div);
	}

	public function dropdown_icon($icon, $active = false){
		$div = $this->primary_element->el('div',['class'=>'nav-item dropdown']);
		if($active) $div->at(['class'=>'active'], true);
		$div->el('a',['class'=>'nav-link dropdown-toggle','data-bs-toggle'=>'dropdown','role'=>'button'])->icon($icon);

		return new BootSomeNavbarDropDown($div);
	}
}

class BootSomeNavbarDropDown extends \TRP\HealDocument\Wrapper {
	public function __construct($parent){
		$this->primary_element = $parent->el('div',['class'=>'dropdown-menu dropdown-menu-end']);
	}

	public function a($href, $text = '', $active = false){
		$a = $this->primary_element->el('a',['class'=>'dropdown-item']);
		if(!empty($text)) $a->te($text);
		if(isset($href)){
			$a->at(['href'=>$href]);
		} else {
			$a->at(['data-bs-toggle'=>'collapse','data-bs-target'=>'.navbar-collapse.show']);
		}
		if($active) $a->at(['class'=>'active'], true);
		return $a;
	}
	public function divider(){
		$this->primary_element->el('div',['class'=>'dropdown-divider']);
	}
}
