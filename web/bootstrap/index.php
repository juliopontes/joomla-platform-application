<?php
/**
 * An example JApplicationWeb application built on the Joomla Platform.
 * 
 * To run this example, copy or soft-link this folder to your web server tree.
 * 
 * @package 	Platform.Examples
 * @author 		CloudAccess.net LCC
 * @copyright 	(C) 2010 - CloudAccess.net LCC
 * @license 	GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_ROOT', __DIR__);
define('JPATH_APPS', JPATH_ROOT.'/apps');
define('JPATH_LIBRARIES', __DIR__.'/libraries');

// Increase error reporting to that any errors are displayed.
// Note, you would not use these settings in production.
//error_reporting(E_ALL);
//ini_set('display_errors', true);

// Bootstrap the application.
require dirname(dirname(__DIR__)).'/bootstrap.php';

//override joomla lib
require_once JPATH_LIBRARIES.'/manager/application/helper.php';
require_once JPATH_LIBRARIES.'/manager/application/component/helper.php';
require_once JPATH_LIBRARIES.'/manager/application/module/helper.php';

//import manager lib
JLoader::import('manager.application.application', JPATH_LIBRARIES);
JLoader::import('manager.application.web', JPATH_LIBRARIES);
JLoader::import('manager.toolbar.helper', JPATH_LIBRARIES);
JLoader::import('manager.version.version', JPATH_LIBRARIES);

final class JBootsrap extends JApplicationWeb
{
	/**
	 * Class constructor.
	 *
	 * @param   mixed  $input   An optional argument to provide dependency injection for the application's
	 *                          input object.  If the argument is a JInput object that object will become
	 *                          the application's input object, otherwise a default input object is created.
	 * @param   mixed  $config  An optional argument to provide dependency injection for the application's
	 *                          config object.  If the argument is a JRegistry object that object will become
	 *                          the application's config object, otherwise a default config object is created.
	 * @param   mixed  $client  An optional argument to provide dependency injection for the application's
	 *                          client object.  If the argument is a JApplicationWebClient object that object will become
	 *                          the application's client object, otherwise a default client object is created.
	 *
	 * @since   11.3
	 */
	public function __construct(JInput $input = null, JRegistry $config = null, JApplicationWebClient $client = null)
	{
		// If a input object is given use it.
		if ($input instanceof JInput)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			$this->input = new JInput;
		}

		// If a config object is given use it.
		if ($config instanceof JRegistry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new JRegistry;
		}

		// If a client object is given use it.
		if ($client instanceof JApplicationWebClient)
		{
			$this->client = $client;
		}
		// Instantiate a new web client object.
		else
		{
			$this->client = new JApplicationWebClient;
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData(__DIR__.'\configuration.php','JBootstrapConfig'));

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Setup the response object.
		$this->response = new stdClass;
		$this->response->cachable = false;
		$this->response->headers = array();
		$this->response->body = array();

		// Set the system URIs.
		$this->loadSystemUris();
	}
	
	/**
	 * Loading bootstrap configuration and set dbo
	 */
	public function getDbo()
	{
		$options = array(
			'driver' => $this->get('dbtype'),
			'host' => $this->get('host'),
			'database' => $this->get('db'),
			'user' => $this->get('user'),
			'password' => $this->get('password'),
			'prefix' => $this->get('dbprefix'),
		);
		
		return JDatabaseDriver::getInstance($options);
	}

	/**
	 * Registering clients
	 * 
	 * @throws RuntimeException
	 */
	public function registerApplications()
	{
		$db = $this->getDbo();
		
		$db->setQuery('SELECT c.id, c.alias, c.path, c.home FROM #__clients c WHERE c.state = 1');
		$apps = $db->loadObjectList();
		
		if (empty($apps))
		{
			throw new RuntimeException(JText::_('JBOOSTRAP_APPLICATIONS_NOT_FOUND'));
		}
		
		foreach ($apps as $app)
		{
			$client = new stdClass();
			$client->id = $app->id;
			$client->name = $app->alias;
			$client->path = JPATH_APPS.'/'.$app->path;
			
			//check if is default app
			if ($app->home)
			{
				$this->default_app = $app->alias;
			}
			
			JApplicationHelper::addClientInfo($client);
		}
	}

	/**
	 * Overrides the parent doExecute method to run the web application.
	 *
	 * This method should include your custom code that runs the application.
	 *
	 * @return void
	 *
	 * @since 0.1
	 */
	protected function doExecute()
	{
		$this->registerApplications();
		
		$appName = $this->input->get('app',$this->default_app);
		// Instantiate the application.
		
		//our application class
		require_once JPATH_APPS.'/'.$appName.'/application.php';

		$application = JApplicationWeb::getInstance($appName);
	
		// Initialise the application.
		$application->initialise();
		
		// Store the application.
		JFactory::$application = $application;
		
		// Execute the application.
		$application->execute();
	}
}

$bootstrap = new JBootsrap();
$bootstrap->execute();