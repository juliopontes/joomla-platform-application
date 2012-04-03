<?php
$user = JFactory::getUser();

require_once JModuleHelper::getLayoutPath('mod_login', $user->guest ? 'default' : 'settings' );