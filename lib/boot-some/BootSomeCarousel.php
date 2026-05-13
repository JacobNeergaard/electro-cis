<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeCarousel extends \TRP\HealDocument\Plugin {
	private $id;
	private $inner = null;
	private $items = 0;

	public static function carousel($parent, $id = 'slide'){
		return new BootSomeCarousel($parent,$id);
	}

	function __construct($parent,$id = 'slide'){
		$this->primary_element = $parent->el('div',['id'=>$id,'class'=>'carousel slide carousel-fade','data-bs-ride'=>'carousel']);
		$this->id = $id;
	}

	public function item($url,$alt){
		return new BootSomeCarouselItem($this->inner(), $url, $alt, $this->items++==0);
	}

	private function inner(){
		if(!$this->inner){
			$this->inner = $this->primary_element->el('div',['class'=>'carousel-inner']);
		}
		return $this->inner;
	}

	public function indicators(){
		$indicators = $this->primary_element->el('div',['class'=>'carousel-indicators']);
		for($i=0;$i<$this->items;$i++) {
			$li = $indicators->el('button',['type'=>'button','data-bs-target'=>'#'.$this->id,'data-bs-slide-to'=>$i]);
			if($i==0) $li->at(['class'=>'active'],true);
		}
	}

	public function control(){
		$a = $this->primary_element->el('button',['type'=>'button','class'=>'carousel-control-prev','data-bs-slide'=>'prev','data-bs-target'=>'#'.$this->id]);
		$a->el('span',['class'=>'carousel-control-prev-icon']);
	
		$a = $this->primary_element->el('button',['type'=>'button','class'=>'carousel-control-next','data-bs-slide'=>'next','data-bs-target'=>'#'.$this->id]);
		$a->el('span',['class'=>'carousel-control-next-icon']);
	}
}

class BootSomeCarouselItem extends \TRP\HealDocument\Wrapper {
	public function __construct($parent, $url, $alt, $active){
		$this->primary_element = $item = $parent->el('div',['class'=>'carousel-item']);
		if($active) $item->at(['class'=>'active'],true);
		$item->el('img',['src'=>$url,'alt'=>$alt,'class'=>'d-block w-100']);
	}

	public function caption($text = null){
		$caption = $this->primary_element->el('div',['class'=>'carousel-caption']);
		if($text) $caption->el('p')->te($text);
		return $caption;
	}
}
