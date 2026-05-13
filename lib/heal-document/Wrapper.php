<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

abstract class Wrapper implements Component {
	protected Component $primary_element;

	public function el(string $name, array $attributes = [], bool $append = false): Component {
		return $this->primary_element->el($name, $attributes, $append);
	}

		public function at(array $values, bool $append = false): Component {
		$this->primary_element->at($values, $append);
		return $this;
	}
	public function te(string $str, bool $break_on_newline = false): Component {
		$this->primary_element->te($str, $break_on_newline);
		return $this;
	}
	public function co(string $str): Component {
		$this->primary_element->co($str);
		return $this;
	}
	public function fr(string|Fragment $contents): bool {
		return $this->primary_element->fr($str);
	}
	public function __call(string $name, array $arguments): ?Component {
		return HealDocument::try_plugin($this, $name, $arguments);
	}
}
