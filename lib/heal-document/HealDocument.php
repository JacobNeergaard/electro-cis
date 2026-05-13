<?php
/*
HealDocument is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/heal-document/blob/master/LICENSE.txt
*/
declare(strict_types=1);
namespace TRP\HealDocument;
require_once __DIR__."/Component.php";
require_once __DIR__."/NodeParent.php";
require_once __DIR__."/Element.php";
require_once __DIR__."/Fragment.php";
require_once __DIR__."/Wrapper.php";
require_once __DIR__."/PluginInterface.php";
require_once __DIR__."/Plugin.php";

class HealDocument extends \DOMDocument implements Component {
	use NodeParent;

	private static $plugins = [], $plugin_name_cache = [], $has_prefixed_plugins = false;

	private $toStringFormat;

	public function __construct(string $version = 'html', string $encoding = ''){
		if($version == 'html'){
			$this->toStringFormat = 'html';
			parent::__construct('1.0',$encoding);
		} else {
			$this->toStringFormat = 'xml';
			parent::__construct($version, $encoding);
		}
	}

	public function __toString(): string {
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

	public function at(array $values, bool $append = false): Component {
		throw new \Exception("Not Supported");
	}

	public static function register_plugin(string $classname, ?string $prefix = null): void {
		if(!class_exists($classname)){
			throw new \Exception("HealDocument can't find class '$classname'");
		}
		if(!is_subclass_of($classname, '\TRP\HealDocument\PluginInterface')){
			throw new \Exception("HealDocument can't register plugin '$classname', because it doesn't implement \TRP\HealDocument\PluginInterface or extend the \TRP\HealDocument\Plugin abstract class.");
		}

		if(isset($prefix)){
			self::$has_prefixed_plugins = true;
			self::$plugins[$prefix] = $classname;
		} else {
			self::$plugins[] = $classname;
		}
		if(!empty(self::$plugin_name_cache)){
			self::$plugin_name_cache = [];
		}
	}

	public static function try_plugin(Component $parent, string $fullname, array $arguments): Component {
		if(isset(self::$plugin_name_cache[$fullname])){
			$classname = self::$plugin_name_cache[$fullname][0];
			$name = self::$plugin_name_cache[$fullname][1] ?? $fullname;
			return $classname::create($parent, $name, ...$arguments);
		}
		if(self::$has_prefixed_plugins){
			$split_name = explode('_',$fullname,2);
			if(count($split_name) == 2){
				list($prefix,$name) = $split_name;
			} else {
				$prefix = null;
				$name = $split_name[0];
			}
			if(!empty($prefix) && isset(self::$plugins[$prefix])){
				$classname = self::$plugins[$prefix];
				if($classname::can_create($name)){
					self::$plugin_name_cache[$fullname] = [$classname, $name];
					return $classname::create($parent, $name, ...$arguments);
				}
			}
		}
		foreach(self::$plugins as $plugin){
			if($plugin::can_create($fullname)){
				self::$plugin_name_cache[$fullname] = [$plugin, null];
				return $plugin::create($parent, $fullname, ...$arguments);
			}
		}
		throw new \Exception("HealDocument can't find function '$fullname'");
	}
}
