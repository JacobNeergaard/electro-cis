<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

class Fragment implements Component {
	use NodeParent;
	protected \DOMDocumentFragment $fragment;

	public function __construct(protected ?HealDocument $doc = null){
		$this->doc ??=  new HealDocument();
		$this->fragment = $this->doc->createDocumentFragment();
	}

	public function appendTo(\DOMNode $parent): bool {
		if($parent->ownerDocument !== $this->doc){
			$result = $parent->ownerDocument->importNode($this->fragment, true);
			if($result !== false){
				$this->fragment = $result;
			} else {
				return false;
			}
		}
		$parent->appendChild($this->fragment);
		return true;
	}

	protected function appendChild($node){
		return $this->fragment->appendChild($node);
	}

	public function at(array $values, bool $append = false): Component {
		throw new \Exception("Not Supported");
	}
}
