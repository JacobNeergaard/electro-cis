<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/BootSomeDocument.php';

require_once __DIR__.'/BootSomeAlert.php';
HealDocument::register_plugin('BootSomeAlert');

require_once __DIR__.'/BootSomeBasic.php';
HealDocument::register_plugin('BootSomeBasic');

require_once __DIR__.'/BootSomeCard.php';
HealDocument::register_plugin('BootSomeCard');

require_once __DIR__.'/BootSomeCarousel.php';
HealDocument::register_plugin('BootSomeCarousel');

require_once __DIR__.'/BootSomeDropdown.php';
HealDocument::register_plugin('BootSomeDropdown');

require_once __DIR__.'/BootSomeForms.php';
HealDocument::register_plugin('BootSomeForms');

require_once __DIR__.'/BootSomeFormsFloating.php';
HealDocument::register_plugin('BootSomeFormsFloating','floating');

require_once __DIR__.'/BootSomeHead.php';
HealDocument::register_plugin('BootSomeHead');

require_once __DIR__.'/BootSomeLayout.php';
HealDocument::register_plugin('BootSomeLayout');

require_once __DIR__.'/BootSomeModal.php';
HealDocument::register_plugin('BootSomeModal');

require_once __DIR__.'/BootSomeNavs.php';
HealDocument::register_plugin('BootSomeNavs');

require_once __DIR__.'/BootSomeNavbar.php';
HealDocument::register_plugin('BootSomeNavbar');

require_once __DIR__.'/BootSomeTables.php';
HealDocument::register_plugin('BootSomeTable');
