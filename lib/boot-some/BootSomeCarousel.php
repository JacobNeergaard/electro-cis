<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
class BootSomeCarousel extends BootSomeElement {
	private $id;
	private $inner = null;
	private $items = 0;

	function __construct($element,$id = 'slide'){
		$this->id = $id;
		parent::__construct($element);
	}

	public function item($url,$alt){
		$this->inner();
		$item = new BootSomeCarouselItem('div');
		$this->inner->appendChild($item);
		$item->at(['class'=>'carousel-item']);
		if($this->items++==0) $item->at(['class'=>'active'],true);
		$item->el('img',['src'=>$url,'alt'=>$alt,'class'=>'d-block w-100']);
		return $item;
	}

	private function inner(){
		if(!$this->inner){
			$element = new BootSomeElement('div');
			$this->appendChild($element);
			$element->at(['class'=>'carousel-inner']);
			$this->inner = $element;
		}
	}

	public function indicators(){
		$indicators = $this->el('div',['class'=>'carousel-indicators']);
		for($i=0;$i<$this->items;$i++) {
			$li = $indicators->el('button',['type'=>'button','data-bs-target'=>'#'.$this->id,'data-bs-slide-to'=>$i]);
			if($i==0) $li->at(['class'=>'active'],true);
		}
	}

	public function control(){
		$a = $this->el('button',['type'=>'button','class'=>'carousel-control-prev','data-bs-slide'=>'prev','data-bs-target'=>'#'.$this->id]);
		$a->el('span',['class'=>'carousel-control-prev-icon']);
	
		$a = $this->el('button',['type'=>'button','class'=>'carousel-control-next','data-bs-slide'=>'next','data-bs-target'=>'#'.$this->id]);
		$a->el('span',['class'=>'carousel-control-next-icon']);
	}
}

class BootSomeCarouselItem extends BootSomeElement {
	public function caption($text = null){
		$caption = $this->el('div',['class'=>'carousel-caption']);
		if($text) $caption->el('p')->te($text);
		return $caption;
	}
}
