<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
trait BootSomeNavbarCommon {
	public function nav(){
		$element = new BootSomeNavbarNav('div');
		$this->appendChild($element);

		$element->at(['class'=>'navbar-nav']);
		return $element;
	}
}

class BootSomeNavbar extends BootSomeElement {
	use BootSomeNavbarCommon;

	public function brand($link = null){
		$a = $this->el($link ? 'a' : 'div',['class'=>'navbar-brand']);
		if($link) $a->at(['href'=>$link]);
		return $a;
	}

	public function toggler($id = 'navbarMain'){
		$toggle = $this->el('button',[
			'class'=>'navbar-toggler',
			'data-bs-toggle'=>'collapse',
			'data-bs-target'=>'#'.$id
		]);
		$toggle->el('span',['class'=>'navbar-toggler-icon']);
	}

	public function collapse($id = 'navbarMain'){
		$element = new BootSomeNavbarCollapse('div');
		$this->appendChild($element);
		$element->at([
			'id'=>$id,
			'class'=>'collapse navbar-collapse',
			'data-toggle'=>"collapse",
			'data-target'=>'#'.$id.'.show'
		]);
		return $element;
	}
}

class BootSomeNavbarCollapse extends BootSomeElement {
	use BootSomeNavbarCommon;
}

class BootSomeNavbarNav extends BootSomeElement {
	public function a($href = null, $text = '', $active = false){
		$a = $this->el('a',['class'=>'nav-item nav-link']);
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
		$div = $this->el('div',['class'=>'nav-item dropdown']);
		if($active) $div->at(['class'=>'active'], true);
		$div->el('a',['class'=>'nav-link dropdown-toggle','data-bs-toggle'=>'dropdown','role'=>'button'])->te($text);

		$element = new BootSomeNavbarDropDown('div');
		$div->appendChild($element);
		$element->at(['class'=>'dropdown-menu dropdown-menu-end']);
		return $element;
	}

	public function dropdown_icon($icon, $active = false){
		$div = $this->el('div',['class'=>'nav-item dropdown']);
		if($active) $div->at(['class'=>'active'], true);
		$div->el('a',['class'=>'nav-link dropdown-toggle','data-bs-toggle'=>'dropdown','role'=>'button'])->icon($icon);

		$element = new BootSomeNavbarDropDown('div');
		$div->appendChild($element);
		$element->at(['class'=>'dropdown-menu dropdown-menu-end']);
		return $element;
	}
}

class BootSomeNavbarDropDown extends BootSomeElement {
	public function a($href, $text = '', $active = false){
		$a = $this->el('a',['class'=>'dropdown-item']);
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
		$this->el('div',['class'=>'dropdown-divider']);
	}
}
