<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
trait BootSomeFormNode {
	private $inlinewrap = null; // https://github.com/twbs/bootstrap/issues/27987

	public function form_row(){
		//Legacy Support
		$element = $this->el('div',['class'=>'row']);
		return $element;
	}

	public function form_group($col = null,$left = false){
		$element = new BootSomeFormsGroup('div');
		$this->appendChild($element);
		$element->at(['class'=>'mb-2']);
		if($left) $element->at(['class'=>'text-end'],true);
		if($col) {
			$element->at(['class'=>'col-md-'.(int) $col], true);
		}
		return $element;
	}

	public function form_horizontal($col = null){
		$element = new BootSomeFormsHorizontal('div');
		$this->appendChild($element);
		$element->at(['class'=>'row']);
		if($col) $element->col = (int) $col;
		return $element;
	}

	public function form_inline(){
		$element = new BootSomeFormsInline('div');
		$this->appendChild($element);
		$element->at(['class'=>'row row-cols-sm-auto gx-2 form-inline']);
		return $element;
	}

	public function label($text = null, $for = null){
		$label = $this->el('label',['class'=>'form-label']);
		if(isset($text)) $label->te($text,true);
		if(isset($for)) $label->at(['for'=>$for]);
		return $label;
	}

	public function input($name, $value = null){
		$input = $this->el('input',['id'=>$name,'name'=>$name,'class'=>'form-control']);
		if(isset($value)) $input->at(['value'=>$value]);
		return $input;
	}

	public function select($name){
		return $this->el('select',['id'=>$name,'name'=>$name,'class'=>'form-select']);
	}

	public function option($text, $value = null, $selected = false){
		$option = $this->el('option')->te($text);
		if($selected) $option->at(['selected']);
		if(isset($value)) $option->at(['value'=>$value]);
		return $option;
	}

	public function options($iterable, $selected = null, $strict_compare = false){
		$options = [];
		if(is_a($iterable, 'mysqli_result')){
			foreach($iterable as $row){
				$is_selected = isset($selected) && ($strict_compare ? $selected === $row['id'] : $selected == $row['id']);
				$options[] = $this->option($row['name'],$row['id'],$is_selected);
			}
		} else {
			foreach($iterable as $value => $text){
				$is_selected = isset($selected) && ($strict_compare ? $selected === $value : $selected == $value);
				$options[] = $this->option($text, $value, $is_selected);
			}
		}
		return $options;
	}

	public function password($name){
		return $this->el('input',['type'=>'password','id'=>$name,'name'=>$name,'class'=>'form-control']);
	}

	public function file($name, $multiple = false){
		$input = $this->el('input',['class'=>'form-control','type'=>'file','id'=>$name]);
		if($multiple) $input->at(['multiple'])->at(['name'=>$name.'[]']);
		else $input->at(['name'=>$name]);
		return $input;
	}

	public function textarea($name, $content = ''){
		return $this->el('textarea',['name'=>$name,'class'=>'form-control'])->te($content);
	}

	public function checkbox($name, $checked = false, $value = 'on', $text = null, $inline = false){
		if(!$inline) {
			$div = new HealElement('div');
			$this->appendChild($div);
			$div->at(['class'=>'form-check']);
			$this->inlinewrap = null;
		}
		else {
			if(!$this->inlinewrap) {
				$this->inlinewrap = new HealElement('div');
				$this->appendChild($this->inlinewrap);
			}
			$div = new HealElement('div');
			$this->inlinewrap->appendChild($div);
			$div->at(['class'=>'form-check form-check-inline']);
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

	public function radio($name, $value, $checked = false, $text = null, $inline = false){
		if(!$inline) {
			$div = new HealElement('div');
			$this->appendChild($div);
			$div->at(['class'=>'form-check']);
			$this->inlinewrap = null;
		}
		else {
			if(!$this->inlinewrap) {
				$this->inlinewrap = new HealElement('div');
				$this->appendChild($this->inlinewrap);
			}
			$div = new HealElement('div');
			$this->inlinewrap->appendChild($div);
			$div->at(['class'=>'form-check form-check-inline']);
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

	public function date($name, $value = null){
		$onclick = "if(typeof BootSomeForms!='undefined'&&typeof BootSomeForms.date=='function')BootSomeForms.date(this);";
		$input = $this->el('input',['type'=>'date','name'=>$name,'id'=>$name,'class'=>'form-control','onclick'=>$onclick]);
		if(isset($value)) $input->at(['value'=>$value]);
		return $input;
	}

	public function inputgroup(){
		$element = new BootSomeFormsInputGroup('div');
		$this->appendChild($element);
		$element->at(['class'=>'input-group']);
		return $element;
	}

	public function button($text, $icon = null, $color = 'primary', $link = null){
		$button = $this->el($link ? 'a' : 'button',['class'=>'btn btn-'.$color]);
		if($link) $button->at(['href'=>$link]);
		else $button->at(['type'=>'button']);
		if($icon) $button->el('i',['class'=>'fas fa-'.$icon]);
		if($text) $button->el('span')->te($text);
		return $button;
	}

	public function hidden($name, $value){
		return $this->el('input',['type'=>'hidden','name'=>$name,'value'=>$value,'id'=>$name]);
	}
}

trait BootSomeNodeParent {
	use BootSomeFormNode;

	protected static function createElementHeal($name){
		return new BootSomeElement($name);
	}

	public function html($title, $language = null, $charset='UTF-8'){
		$html = $this->el('html');
		$html->at(['lang'=>$language ? $language : 'en']);
		return [$html->head($title, $charset),$html->el('body')];
	}

	public function head($title = null, $charset = 'UTF-8'){
		$head = $this->el('head');
		if(!empty($title)) $head->el('title')->te($title);
		$head->el('meta',['charset'=>$charset]);
		return $head;
	}

	public function metadata($name, $content){
		return $this->el('meta',['name'=>$name,'content'=>$content]);
	}

	public function link($rel, $href){
		return $this->el('link',['rel'=>$rel,'href'=>$href]);
	}

	public function css($path){
		return $this->link('stylesheet',$path);
	}

	public function img($src, $alt){
		return $this->el('img',['src'=>$src,'alt'=>$alt]);
	}

	public function p($text, $break_on_newline = true){
		return $this->el('p')->te($text, $break_on_newline);
	}

	public function a($href, $text = ''){
		$a = $this->el('a', ['href'=>$href]);
		if(!empty($text)) $a->te($text);
		return $a;
	}

	public function form($action = '', $method = 'get'){
		$attr = [];
		if(!empty($action)){
			$attr['action'] = $action;
			$attr['method'] = $method;
		} else {
			$attr['onsubmit'] = 'return false;';
		}
		return $this->el('form', $attr);
	}

	public function container($fluid = false, $element = 'div'){
		$head = $this->el($element)->at(['class'=>$fluid?'container-fluid':'container']);
		return $head;
	}

	public function row(){
		$element = new BootSomeRow('div');
		$this->appendChild($element);
		$element->at(['class'=>'row']);
		return $element;
	}

	public function navbar($fluid = true, $nav_classes = ''){
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

		$nav = $this->el('nav',['class'=>implode(' ',$classes)]);

		$element = new BootSomeNavbar('div');
		$nav->appendChild($element);
		$element->at(['class'=>$fluid?'container-fluid':'container']);
		return $element;
	}

	public function navs($type = null){
		$element = new BootSomeNavs('ul');
		$this->appendChild($element);
		$element->at(['class'=>'nav']);
		if($type) $element->at(['class'=>'nav-'.$type],true);
		return $element;
	}

	public function modal(){
		$dialog = $this->el('div',['class'=>'modal']);
		$dialog = $dialog->el('div',['class'=>'modal-dialog']);

		$element = new BootSomeModal('div');
		$dialog->appendChild($element);
		$element->at(['class'=>'modal-content']);

		$this->el('div',['class'=>'modal-backdrop']);
		return $element;
	}

	public function carousel($id = 'slide'){
		$element = new BootSomeCarousel('div',$id);
		$this->appendChild($element);
		$element->at(['id'=>$id,'class'=>'carousel slide carousel-fade','data-bs-ride'=>'carousel']);
		return $element;
	}

	public function card(){
		$element = new BootSomeCard('div');
		$this->appendChild($element);
		$element->at(['class'=>'card']);
		return $element;
	}

	public function pagination($total, $limit, $page, $url){
		if($total<=$limit) return; 

		$pages = (ceil($total/$limit));
		$nav = $this->el('nav')->el('ul',['class'=>'pagination']);

		$li = $nav->el('li',['class'=>'page-item']);
		$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te('«');
		if($page==1) {
			$li->at(['class'=>'disabled'],true);
		}
		else {
			$button->at($url($page-1));
			$button->at(['accesskey'=>'p']);
		}

		if($page>4 && $pages>7) {
			$li = $nav->el('li',['class'=>'page-item']);
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te('1');
			$button->at($url(1));

			$li = $nav->el('li',['class'=>'page-item']);
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te('…');
			$li->at(['class'=>'disabled'],true);

			if($page<($pages-4)) {
				$start = $page - 1;
			}
			else {
				$start = ($page<($pages-3)) ? $page - 1 : $pages-4;
			}
		}
		else {
			$start = 1;
		}

		if($pages>7) {
			if($page<5) {
				$end = 5;
			}
			else {
				$end = ($page<($pages-3)) ? $page + 1 : $pages;
			}
		}
		else {
			$end = $pages;
		}

		for($i=$start;$i<=$end;$i++) {
			$li = $nav->el('li',['class'=>'page-item']);
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te($i);
			if($page==$i) {
				$li->at(['class'=>'active'],true);
			}
			else {
				$button->at($url($i));
			}
		}

		if($page<($pages-3) && $pages>7) {
			$li = $nav->el('li',['class'=>'page-item']);
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te('…');
			$li->at(['class'=>'disabled'],true);

			$li = $nav->el('li',['class'=>'page-item']);
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te($pages);
			$button->at($url($pages));
		}

		$li = $nav->el('li',['class'=>'page-item']);
		$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te('»');
		if($page==ceil($total/$limit)) {
			$li->at(['class'=>'disabled'],true);
		}
		else {
			$button->at($url($page+1));
			$button->at(['accesskey'=>'n']);
		}
		
		return $nav;
	}

	public function alert($color = null,$center = false){
		if($color===null) $color = 'primary';
		$alert = $this->el('div',['class'=>'alert alert-'.$color]);
		if($center) $alert->at(['class'=>'text-center'],true);
		return $alert;
	}

	public function badge($color = 'primary'){
		$this->at(['class'=>'badge bg-'.$color],true);
		if(in_array($color,['warning','info','light'])) {
			$this->at(['class'=>'text-dark'],true);
		}
		return $this;
	}

	public function spinner(){
		return $this->el('i',['class'=>'fas fa-2x fa-spinner fa-spin'],true);
	}

	public function breadcrumb($input = [],$prefix = '') {
		$ol = $this->el('nav')->el('ol',['class'=>'breadcrumb']);

		foreach($input as $item) {
			if(isset($item['link'])) {
				$a = $ol->el('li',['class'=>'breadcrumb-item'])->el('a',['href'=>$prefix.$item['link']])->te($item['name']);
			}
			else {
				$a = $ol->el('li',['class'=>'breadcrumb-item active'])->te($item['name']);
			}
		}
	}

	public function dropdown($text,$color = 'primary'){
		$div = $this->el('div', ['class'=>'dropdown']);
		$div->el('button',['class'=>'btn btn-'.$color.' dropdown-toggle','data-bs-toggle'=>'dropdown'])->te($text);

		$element = new BootSomeDropDown('ul');
		$div->appendChild($element);
		$element->at(['class'=>'dropdown-menu']);
		return $element;
	}

	public function ratio($aspect = '16x9'){
		return $this->el('div',['class'=>'ratio ratio-'.$aspect]);
	}

	public function jumbotron(){
		//Legacy Support
		return $this->el('div',['class'=>'px-4 py-5 mb-4 bg-light rounded-3']);
	}

	public function table(){
		$div = $this->el('div',['class'=>'table-responsive']);
		$element = new BootSomeTable('table');
		$div->appendChild($element);
		$element->at(['class'=>'table']);
		return $element;
	}

	public function icon($icon,$fullclass = false,$color = false){
		$icon = $this->el('i',['class'=>$fullclass ? $icon : 'fas fa-'.$icon]);
		if($color) $icon->at(['class'=>'text-'.$color],true);
		return $this;
	}

	public function display(...$class){
		if(!$class) return;
		$class = 'd-'.implode(' d-',$class);
		return $this->at(['class'=>$class],true);
	}
}

class BootSome extends HealDocument {
	use BootSomeNodeParent;

	public static $doc;
	public static $head;
	public static $body;
	public static $dialog;

	public static function document($title,$language = null,$autoecho = true) {
		self::$doc = new BootSome();
		list(self::$head,self::$body) = self::$doc->html($title,$language);
		self::$head->metadata('viewport','width=device-width, initial-scale=1');
		self::$body->at(['id'=>'body','onload'=>'BootSomeLoad();']);
		self::$dialog = self::$body->el('dialog',['id'=>'dialog']);
		if($autoecho) register_shutdown_function(['BootSome','document_end']);
	}

	public static function document_end() {
		echo self::$doc;
	}
}

class BootSomeElement extends HealElement {
	use BootSomeNodeParent;
}

class BootSomeRow extends BootSomeElement {
	public function col(...$class) {
		$class = implode(' ',$class);
		return $this->el('div',['class'=>$class]);
	}
}

require_once(__DIR__.'/BootSomeCarousel.php');
require_once(__DIR__.'/BootSomeCard.php');
require_once(__DIR__.'/BootSomeDropdown.php');
require_once(__DIR__.'/BootSomeNavbar.php');
require_once(__DIR__.'/BootSomeNavs.php');
require_once(__DIR__.'/BootSomeForms.php');
require_once(__DIR__.'/BootSomeModal.php');
require_once(__DIR__.'/BootSomeTables.php');
