<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

class Element extends \DOMElement implements Component {
	use NodeParent;

	public function at(array $values, bool $append = false): Component {
		foreach($values as $name => $value){
			if(is_numeric($name)){
				$attr = $this->ownerDocument->createAttribute($value);
			} else {
				$attr = $this->ownerDocument->createAttribute($name);
				if(isset($value)){
					$value = htmlspecialchars((string) $value);

					if($append && $this->hasAttribute($name)){
						$value = htmlspecialchars($this->getAttribute($name)).' '.$value;
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
