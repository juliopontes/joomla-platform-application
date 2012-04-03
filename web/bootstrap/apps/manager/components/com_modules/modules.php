<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Access check.
/*if (!JFactory::getUser()->authorise('core.manage', 'com_modules')) {
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}*/

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Modules');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();