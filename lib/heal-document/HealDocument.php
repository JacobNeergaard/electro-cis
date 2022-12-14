<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/

trait HealNodeParent {
	public function el($name, $attributes = [], $append = false){
		$element = static::createElementHeal($name);
		$this->appendChild($element);
		$element->at($attributes,$append);
		return $element;
	}

	public function te($str, $break_on_newline = false){
		if($break_on_newline){
			$lines = explode("\n",$str);
			$firstline = true;
			foreach($lines as $line){
				if(!$firstline){
					$this->el('br');
				} else {
					$firstline = false;
				}
				$this->appendChild(new DOMText($line));
			}
		} else {
			$this->appendChild(new DOMText($str));
		}
		// return $this to allow chaining
		return $this;
	}

	public function co($str){
		$this->appendChild(new DOMComment($str));
		// return $this to allow chaining
		return $this;
	}

	public function fr($str){
		$fragment = $this->ownerDocument->createDocumentFragment();
		if(@$fragment->appendXML($str)) {
			$this->appendChild($fragment);
			return true;
		}
		else {
			$this->te("Error in: \"".$str."\"");
			return false;
		}
	}

	protected static function createElementHeal($name){
		return new HealElement($name);
	}
}

class HealDocument extends DOMDocument {
	use HealNodeParent;

	private $toStringFormat;

	public function __construct($version = 'html', $encoding = ''){
		if($version == 'html'){
			$this->toStringFormat = 'html';
			parent::__construct('1.0',$encoding);
		} else {
			$this->toStringFormat = 'xml';
			parent::__construct($version, $encoding);
		}
	}

	public function __toString(){
		$this->formatOutput = true;
		if($this->toStringFormat == 'html'){
			if(isset($this->firstChild->nodeName) && strtolower($this->firstChild->nodeName) == 'html'){
				return "<!DOCTYPE html>".PHP_EOL.$this->saveHTML();
			} else {
				return $this->saveHTML();
			}
		} else {
			return $this->saveXML();
		}
	}
}

class HealElement extends DOMElement {
	use HealNodeParent;

	public function at($values, $append = false){
		foreach($values as $name => $value){
			if(is_numeric($name)){
				$attr = $this->ownerDocument->createAttribute($value);
			} else {
				$attr = $this->ownerDocument->createAttribute($name);
				if(isset($value)){
					$value = htmlspecialchars($value);

					if($append && $this->hasAttribute($name)){
						$value = $this->getAttribute($name).' '.$value;
					}
					$attr->value = $value;
				} else {
					if($append && $this->hasAttribute($name)){
						continue;
					}
				}
			}
			$this->appendChild($attr);
		}

		// return $this to allow chaining
		return $this;
	}
}