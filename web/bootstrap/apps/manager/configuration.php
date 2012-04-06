 <?php
/**
 * @package 	Platform.Examples
 * @author 		CloudAccess.net LCC
 * @copyright 	(C) 2010 - CloudAccess.net LCC
 * @license 	GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// Prevent direct access to this file outside of a calling application.
defined('_JEXEC') or die;

/**
 * Web configuration class.
 *
 * @package Platform.Examples
 * @since 0.1
 */
final class JConfig
{
	/**
	 * The application theme.
	 *
	 * @var string
	 * @since 0.1
	 */
	//default component
	public $default_option = 'com_login';
	//theme
	public $theme = 'bluestork';
	//database options
	public $dbtype = 'mysql';
	public $host = 'localhost';
	public $user = 'root';
	public $password = '';
	public $db = 'joomla_platform_example';
	public $dbprefix = 'jos_';
}