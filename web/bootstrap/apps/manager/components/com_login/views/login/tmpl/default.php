<?php
// No direct access.
defined('_JEXEC') or die;

$this->module = JModuleHelper::getModule('mod_login');

echo JModuleHelper::renderModule($this->module);