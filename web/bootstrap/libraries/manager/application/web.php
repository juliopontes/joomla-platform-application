<?php
jimport('joomla.application.router');
jimport('legacy.request.request');
jimport('legacy.menu.menu');

abstract class CoreApplicationWeb extends JApplicationWeb
{
	/**
	 * 
	 * 
	 * @var boolean
	 */
	protected $_autoassign = true;

	protected $_themeCustomLayout = array();

	/**
	 * The application message queue.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $messageQueue = array();

	/**
	 * Return name of application
	 */
	public function getName()
	{
		return get_class($this);
	}

	/**
	 * Returns the application JPathway object.
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JMenu  JMenu object.
	 *
	 * @since   11.1
	 */
	public function getMenu($name = null, $options = array())
	{
		if (!isset($name))
		{
			$name = $this->getName();
		}

		jimport('joomla.application.menu');
		$menu = JMenu::getInstance($name, $options);

		if ($menu instanceof Exception)
		{
			return null;
		}

		return $menu;
	}
	
/**
	 * Gets a user state.
	 *
	 * @param   string  $key      The path of the state.
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  The user state or null.
	 *
	 * @since   11.1
	 */
	public function getUserState($key, $default = null)
	{
		$session = JFactory::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->get($key, $default);
		}

		return $default;
	}

	/**
	 * Sets the value of a user state variable.
	 *
	 * @param   string  $key    The path of the state.
	 * @param   string  $value  The value of the variable.
	 *
	 * @return  mixed  The previous state, if one existed.
	 *
	 * @since   11.1
	 */
	public function setUserState($key, $value)
	{
		$session = JFactory::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->set($key, $value);
		}

		return null;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string  $key      The key of the user state variable.
	 * @param   string  $request  The name of the variable passed in a request.
	 * @param   string  $default  The default value for the variable if not found. Optional.
	 * @param   string  $type     Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 *
	 * @return  The request user state.
	 *
	 * @since   11.1
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		$cur_state = $this->getUserState($key, $default);
		$new_state = $this->input->get($request, null, $type);

		// Save the new value only if it was set in this request.
		if ($new_state !== null)
		{
			$this->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Gets a configuration value.
	 *
	 * An example is in application/japplication-getcfg.php Getting a configuration
	 *
	 * @param   string  $varname  The name of the value to get.
	 * @param   string  $default  Default value to return
	 *
	 * @return  mixed  The user state.
	 *
	 * @since   11.1
	 */
	public function getCfg($varname, $default = null)
	{
		return $this->get($varname, $default);
	}

	/**
	 * Gets the client id of the current running application.
	 *
	 * @return  integer  A client identifier.
	 *
	 * @since   11.1
	 */
	public function getClientId()
	{
		return $this->getClient()->id;
	}

	/**
	 * Return client object
	 * 
	 */
	public function getClient()
	{
		return JApplicationHelper::getClientInfo($this->getName(),true);
	}

	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Checks the user session.
	 *
	 * If the session record doesn't exist, initialise it.
	 * If session is new, create session variables
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function checkSession()
	{
		$db = JFactory::getDBO();
		$session = JFactory::getSession();
		$user = JFactory::getUser();

		$query = $db->getQuery(true);
		$query->select($query->qn('session_id'))
			->from($query->qn('#__session'))
			->where($query->qn('session_id') . ' = ' . $query->q($session->getId()));

		$db->setQuery($query, 0, 1);
		$exists = $db->loadResult();

		// If the session record doesn't exist initialise it.
		if (!$exists)
		{
			$query->clear();
			if ($session->isNew())
			{
				$query->insert($query->qn('#__session'))
					->columns($query->qn('session_id') . ', ' . $query->qn('client_id') . ', ' . $query->qn('time'))
					->values($query->q($session->getId()) . ', ' . (int) $this->getClientId() . ', ' . $query->q((int) time()));
				$db->setQuery($query);
			}
			else
			{
				$query->insert($query->qn('#__session'))
					->columns(
						$query->qn('session_id') . ', ' . $query->qn('client_id') . ', ' . $query->qn('guest') . ', ' .
						$query->qn('time') . ', ' . $query->qn('userid') . ', ' . $query->qn('username')
					)
					->values(
						$query->q($session->getId()) . ', ' . (int) $this->getClientId() . ', ' . (int) $user->get('guest') . ', ' .
						$query->q((int) $session->get('session.timer.start')) . ', ' . (int) $user->get('id') . ', ' . $query->q($user->get('username'))
					);

				$db->setQuery($query);
			}

			// If the insert failed, exit the application.
			if (!$db->query())
			{
				throw new Exception($db->getErrorMSG());
			}

			// Session doesn't exist yet, so create session variables
			if ($session->isNew())
			{
				$session->set('registry', new JRegistry('session'));
				$session->set('user', new JUser);
			}
		}
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
		if ($this->_autoassign)
		{
			define('JDEBUG', $this->get('debug'));
			
			//load library language
			$this->language->load('lib_joomla');
			//set factory language
			JFactory::$language = $this->language;
			
			$this->router = new JRouter();
			
			$name = $this->getName();
			$this->component = $this->input->get('option',$this->get('default_option',null));
			
			$output = JComponentHelper::renderComponent($this->component);
			
			$this->document->application = $this->getClient()->name;
			$this->document->setBuffer($output,array('type' => 'component', 'name' => 'main', 'title' => ''));
		}
	}

/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->messageQueue))
		{
			$session = JFactory::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}

		// Enqueue the message.
		$this->messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   11.1
	 */
	public function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->messageQueue))
		{
			$session = JFactory::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}

		return $this->messageQueue;
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function render()
	{
		// Setup the document options.
		$options = array(
			'template' => $this->get('theme'),
			'file' => 'index.php',
			'params' => new JRegistry()
		);
	
		if (!empty($this->_themeCustomLayout[$this->component]))
		{
			$options['file'] = $this->_themeCustomLayout[$this->component];
		}

		if ($this->get('themes.base'))
		{
			$options['directory'] = $this->get('themes.base');
		}
		// Fall back to constants.
		else
		{
			$options['directory'] = (defined('JPATH_BASE') ? JPATH_BASE : __DIR__) . '/themes';
		}
		
		// Parse the document.
		$this->document->parse($options);
		// Render the document.
		$data = $this->document->render($this->get('cache_enabled'), $options);

		// Set the application output data.
		$this->setBody($data);
	}

	/**
	 * Login authentication function.
	 *
	 * Username and encoded password are passed the onUserLogin event which
	 * is responsible for the user validation. A successful validation updates
	 * the current session record with the user's details.
	 *
	 * Username and encoded password are sent as credentials (along with other
	 * possibilities) to each observer (authentication plugin) for user
	 * validation.  Successful validation will update the current session with
	 * the user details.
	 *
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function login($credentials, $options = array())
	{
		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($credentials, $options);

		if ($response->status === JAuthentication::STATUS_SUCCESS)
		{
			// Validate that the user should be able to login (different to being authenticated).
			// This permits authentication plugins blocking the user
			$authorisations = $authenticate->authorise($response, $options);
			foreach ($authorisations as $authorisation)
			{
				$denied_states = array(JAuthentication::STATUS_EXPIRED, JAuthentication::STATUS_DENIED);
				if (in_array($authorisation->status, $denied_states))
				{
					// Trigger onUserAuthorisationFailure Event.
					$this->triggerEvent('onUserAuthorisationFailure', array((array) $authorisation));

					// If silent is set, just return false.
					if (isset($options['silent']) && $options['silent'])
					{
						return false;
					}

					// Return the error.
					switch ($authorisation->status)
					{
						case JAuthentication::STATUS_EXPIRED:
							return JError::raiseWarning('102002', JText::_('JLIB_LOGIN_EXPIRED'));
							break;
						case JAuthentication::STATUS_DENIED:
							return JError::raiseWarning('102003', JText::_('JLIB_LOGIN_DENIED'));
							break;
						default:
							return JError::raiseWarning('102004', JText::_('JLIB_LOGIN_AUTHORISATION'));
							break;
					}
				}
			}

			// Import the user plugin group.
			JPluginHelper::importPlugin('user');

			// OK, the credentials are authenticated and user is authorised.  Lets fire the onLogin event.
			$results = $this->triggerEvent('onUserLogin', array((array) $response, $options));

			/*
			 * If any of the user plugins did not successfully complete the login routine
			 * then the whole method fails.
			 *
			 * Any errors raised should be done in the plugin as this provides the ability
			 * to provide much more information about why the routine may have failed.
			 */

			if (!in_array(false, $results, true))
			{
				// Set the remember me cookie if enabled.
				if (isset($options['remember']) && $options['remember'])
				{
					jimport('joomla.utilities.simplecrypt');

					// Create the encryption key, apply extra hardening using the user agent string.
					$key = self::getHash(@$_SERVER['HTTP_USER_AGENT']);

					$crypt = new JSimpleCrypt($key);
					$rcookie = $crypt->encrypt(serialize($credentials));
					$lifetime = time() + 365 * 24 * 60 * 60;

					// Use domain and path set in config for cookie if it exists.
					$cookie_domain = $this->getCfg('cookie_domain', '');
					$cookie_path = $this->getCfg('cookie_path', '/');
					setcookie(self::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, $cookie_path, $cookie_domain);
				}

				return true;
			}
		}

		// Trigger onUserLoginFailure Event.
		$this->triggerEvent('onUserLoginFailure', array((array) $response));

		// If silent is set, just return false.
		if (isset($options['silent']) && $options['silent'])
		{
			return false;
		}

		// If status is success, any error will have been raised by the user plugin
		if ($response->status !== JAuthentication::STATUS_SUCCESS)
		{
			JLog::add($response->error_message, JLog::WARNING, 'jerror');
		}

		return false;
	}

	/**
	 * Logout authentication function.
	 *
	 * Passed the current user information to the onUserLogout event and reverts the current
	 * session record back to 'anonymous' parameters.
	 * If any of the authentication plugins did not successfully complete
	 * the logout routine then the whole method fails. Any errors raised
	 * should be done in the plugin as this provides the ability to give
	 * much more information about why the routine may have failed.
	 *
	 * @param   integer  $userid   The user to load - Can be an integer or string - If string, it is converted to ID automatically
	 * @param   array    $options  Array('clientid' => array of client id's)
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function logout($userid = null, $options = array())
	{
		// Get a user object from the JApplication.
		$user = JFactory::getUser($userid);

		// Build the credentials array.
		$parameters['username'] = $user->get('username');
		$parameters['id'] = $user->get('id');

		// Set clientid in the options array if it hasn't been set already.
		if (!isset($options['clientid']))
		{
			$options['clientid'] = $this->getClientId();
		}

		// Import the user plugin group.
		JPluginHelper::importPlugin('user');

		// OK, the credentials are built. Lets fire the onLogout event.
		$results = $this->triggerEvent('onUserLogout', array($parameters, $options));

		// Check if any of the plugins failed. If none did, success.

		if (!in_array(false, $results, true))
		{
			// Use domain and path set in config for cookie if it exists.
			$cookie_domain = $this->getCfg('cookie_domain', '');
			$cookie_path = $this->getCfg('cookie_path', '/');
			setcookie(self::getHash('JLOGIN_REMEMBER'), false, time() - 86400, $cookie_path, $cookie_domain);

			return true;
		}

		// Trigger onUserLoginFailure Event.
		$this->triggerEvent('onUserLogoutFailure', array($parameters));

		return false;
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param   string  $seed  Seed string.
	 *
	 * @return  string  A secure hash
	 *
	 * @since   11.1
	 */
	public static function getHash($seed)
	{
		return md5($this->get('secret') . $seed);
	}

	/**
	 * Method to get the template name. This is needed for compatability with JApplication.
	 *
	 * @return string The theme name.
	 *
	 * @since 0.1
	 */
	public function getTemplate()
	{
		return $this->get('theme');
	}

	/**
	 * Boolean TRUE for FRONTEND applications
	 */
	abstract function isSite();

	/**
	 * Boolean TRUE for MANAGER applications
	 */
	abstract function isAdmin();
}