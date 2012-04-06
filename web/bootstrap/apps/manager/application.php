<?php
define('JPATH_BASE', __DIR__);
define('JPATH_THEMES', __DIR__.'/themes');
define('JPATH_CACHE', __DIR__.'/cache');
define('JPATH_PLUGINS', __DIR__.'/plugins');

class Manager extends CoreApplicationWeb
{
	public function isSite()
	{
		return false;
	}

	public function isAdmin()
	{
		return true;
	}

	/**
	 * Login authentication function
	 *
	 * @param	array	Array('username' => string, 'password' => string)
	 * @param	array	Array('remember' => boolean)
	 *
	 * @return	boolean True on success.
	 * @see		JApplication::login
	 * @since	1.5
	 */
	public function login($credentials, $options = array())
	{
		//The minimum group
		$options['group'] = 'Public Backend';

		//Make sure users are not autoregistered
		$options['autoregister'] = false;

		//Set the application login entry point
		if (!array_key_exists('entry_url', $options)) {
			$options['entry_url'] = JURI::base().'index.php?option=com_users&task=login';
		}

		// Set the access control action to check.
		$options['action'] = 'core.login.admin';

		$result = parent::login($credentials, $options);

		return $result;
	}

	/**
	 * Method to run the Web application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of action that populates a JDocument object so that output
	 * can be rendered to the client.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   11.3
	 */
	protected function doExecute()
	{
		//import toolbar
		jimport('legacy.toolbar.toolbar');
		JLoader::register('JToolBarHelper', JPATH_LIBRARIES.'/core/toolbar/helper.php');
		
		parent::doExecute();
		
		//add custom template page to theme
		if ($this->component == 'com_login')
		{
			$this->_themeCustomLayout['com_login'] = 'login.php';
		}
	}
}