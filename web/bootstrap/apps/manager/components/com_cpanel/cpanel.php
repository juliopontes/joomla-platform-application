<?php
/**
 * @package 	Platform.Examples
 * @author 		CloudAccess.net LCC
 * @copyright 	(C) 2010 - CloudAccess.net LCC
 * @license 	GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// Prevent direct access to this file outside of a calling application.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

$controller = JController::getInstance('Cpanel');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();