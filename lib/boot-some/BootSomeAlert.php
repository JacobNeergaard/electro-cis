<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeAlert extends \TRP\HealDocument\Plugin {
	public static function alert($parent, $color = null,$center = false){
		if($color===null) $color = 'primary';
		$alert = $parent->el('div',['class'=>'alert alert-'.$color]);
		if($center) $alert->at(['class'=>'text-center'],true);
		return $alert;
	}
}
