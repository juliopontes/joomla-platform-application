<?php
/**
 * @package 	Platform.Examples
 * @author 		CloudAccess.net LCC
 * @copyright 	(C) 2010 - CloudAccess.net LCC
 * @license 	GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// Prevent direct access to this file outside of a calling application.
defined('_JEXEC') or die;
?>
<!DOCTYPE html>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js"></script>
	<jdoc:include type="head" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="apps/<?php echo $this->application; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<style>
	body {
		padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
	}
	</style>
	<link href="apps/<?php echo $this->application; ?>/assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
	<link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
</head>
<body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="<?php echo JRoute::_('index.php?option=com_dashboard'); ?>">Joomla! Application Manager</a>
          <jdoc:include type="modules" name="top" style="none" />
          <jdoc:include type="module" name="mod_login" style="none" />
        </div>
      </div>
    </div>
    <div class="container-fluid">
    	<jdoc:include type="modules" name="content-top" style="none" />
		<jdoc:include type="component" name="main" />
		<jdoc:include type="modules" name="content-bttom" style="none" />
    	<hr>
	    <footer>
	    	<p>&copy; 2010 - CloudAccess.net LCC</p>
	    </footer>
	</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="apps/<?php echo $this->application; ?>/assets/bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>