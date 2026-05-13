<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

trait NodeParent {
	public function el(string $name, array $attributes = [], bool $append = false): Component {
		$element = static::createElementHeal($name);
		$this->appendChild($element);
		$element->at($attributes,$append);
		return $element;
	}

	public function te(string $str, bool $break_on_newline = false): Component {
		if(!isset($str)) return $this;
		if($break_on_newline){
			$lines = explode("\n",str_replace("\r",'',$str));
			$firstline = true;
			foreach($lines as $line){
				if(!$firstline){
					$this->el('br');
				} else {
					$firstline = false;
				}
				$this->appendChild(new \DOMText($line));
			}
		} else {
			$this->appendChild(new \DOMText((string) $str));
		}
		// return $this to allow chaining
		return $this;
	}

	public function co(string $str): Component {
		$this->appendChild(new \DOMComment($str));
		// return $this to allow chaining
		return $this;
	}

	public function fr(string|Fragment $contents): bool {
		if($contents instanceof Fragment){
			return $contents->appendTo($this);
		} else {
			$fragment = $this->ownerDocument->createDocumentFragment();
			if(@$fragment->appendXML($contents)) {
				$this->appendChild($fragment);
				return true;
			}
			else {
				$this->te("Error in: \"".$contents."\"");
				return false;
			}
		}
	}

	public function __call(string $methodname, array $arguments): Component {
		return HealDocument::try_plugin($this, $methodname, $arguments);
	}

	protected static function createElementHeal(string $name): Component {
		return new Element($name);
	}
}
