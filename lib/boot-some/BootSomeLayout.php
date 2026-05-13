<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeLayout extends \TRP\HealDocument\Plugin {
	public static function container($parent, $fluid = false, $element = 'div'){
		$head = $parent->el($element)->at(['class'=>$fluid?'container-fluid':'container']);
		return $head;
	}

	public static function row($parent,...$class){
		return new BootSomeRow($parent,$class);
	}

	public static function row_gutter($parent,...$class){
		$row = new BootSomeRow($parent,$class);
		$row->at(['class'=>'row_gutter'],true);
		return $row;
	}

	public static function pagination(object $parent,int $total,int $limit,int $page,object $url){
		if($total<=$limit) return $parent;

		$pages = (ceil($total/$limit));
		$nav = $parent->el('nav')->el('ul',['class'=>'pagination']);

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
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te((string) $i);
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
			$button = $li->el('button',['type'=>'button','class'=>'page-link'])->te((string) $pages);
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

	public static function badge($parent, $color = 'primary'){
		$parent->at(['class'=>'badge bg-'.$color],true);
		if(in_array($color,['warning','info','light'])) {
			$parent->at(['class'=>'text-dark'],true);
		}
		return $parent;
	}

	public static function spinner($parent){
		return $parent->el('i',['class'=>'fas fa-2x fa-spinner fa-spin'],true);
	}

	public static function breadcrumb($parent, $input = [],$prefix = '') {
		$ol = $parent->el('nav')->el('ol',['class'=>'breadcrumb']);

		foreach($input as $item) {
			if(isset($item['link'])) {
				$a = $ol->el('li',['class'=>'breadcrumb-item'])->el('a',['href'=>$prefix.$item['link']])->te($item['name']);
			}
			else {
				$a = $ol->el('li',['class'=>'breadcrumb-item active'])->te($item['name']);
			}
		}
		return $ol;
	}

	public static function ratio($parent, $aspect = '16x9'){
		return $parent->el('div',['class'=>'ratio ratio-'.$aspect]);
	}

	public static function jumbotron($parent){
		//Legacy Support
		return $parent->el('div',['class'=>'px-4 py-5 mb-4 bg-light rounded-3']);
	}

	public static function icon($parent, $icon,$fullclass = false,$color = false){
		$icon = $parent->el('i',['class'=>$fullclass ? $icon : 'fas fa-'.$icon]);
		if($color) $icon->at(['class'=>'text-'.$color],true);
		return $parent;
	}

	public static function display($parent, ...$class){
		if(!empty($class)){
			$parent->at(['class'=>'d-'.implode(' d-',$class)],true);
		}
		return $parent;
	}
}

class BootSomeRow extends \TRP\HealDocument\Wrapper {
	public function __construct($parent,$class){
		$class = implode(' ',array_merge(['row'],$class));
		$this->primary_element = $parent->el('div',['class'=>$class]);
	}

	public function col(...$class) {
		$class = implode(' ',$class);
		return $this->primary_element->el('div',['class'=>$class]);
	}
}
