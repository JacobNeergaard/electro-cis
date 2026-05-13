<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

interface Component {
	public function el(string $name, array $attributes = [], bool $append = false): Component;
	public function at(array $values, bool $append = false): Component;
	public function te(string $str, bool $break_on_newline = false): Component;
	public function co(string $str): Component;
	public function fr(string|Fragment $contents): bool;
}
