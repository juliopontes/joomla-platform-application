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

class LoginController extends JController
{
	private $redirect_component = 'com_cpanel';
	
	public function login()
	{
		$user = JFactory::getUser();
		
		if (!$user->guest)
		{
			$this->setRedirect('index.php?option='.$this->redirect_component);
			$this->redirect();
		}
		
		$input = JFactory::getApplication()->input;
		
		$result = JFactory::getApplication()->login(array(
				'username' => $input->get('username'),
				'password' => $input->get('password')
		));
		
		if (!$result)
		{
			$this->setRedirect('index.php?option=com_login&view=login');
		}
		
		$this->setRedirect('index.php',JText::_(''));
	}

	public function logout()
	{
		$result = JFactory::getApplication()->logout();
		
		if (!$result)
		{
			$this->setRedirect('index.php?option=com_login&view=login');
		}
		
		$this->setRedirect('index.php',JText::_(''));
	}

	public function display($cachable = false, $urlparams = array())
	{
		$user = JFactory::getUser();
		
		if (!$user->guest)
		{
			$this->setRedirect('index.php?option='.$this->redirect_component);
			$this->redirect();
		}
		
		parent::display();
	}
}