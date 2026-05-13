<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;

abstract class Plugin extends Wrapper implements PluginInterface {
	public static function can_create(string $component_name): bool {
		return method_exists(static::class, $component_name) && (new \ReflectionMethod(static::class, $component_name))->isStatic();
	}

	public static function create(Component $parent, string $component_name, ...$arguments): Component {
		if(method_exists(static::class, $component_name)){
			$object = static::$component_name($parent, ...$arguments);
			if(is_a($object, static::class) && !isset($object->primary_element)){
				$object->primary_element = $parent;
			}
			if(!($object instanceof Component)){
				throw new \Exception("\TRP\HealDocument\Plugin failed to create element '$component_name': Returned element didn't implement \TRP\HealDocument\Component");
			}
			return $object;
		} else {
			throw new \Exception("\TRP\HealDocument\Plugin failed to find method '$component_name'");
		}
	}
}
