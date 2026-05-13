<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

interface PluginInterface {
	public static function can_create(string $component_name): bool;
	public static function create(Component $parent, string $component_name, ...$arguments): Component;
}
