<?php

?>
<form action="<?php echo JRoute::_('index.php?option=com_login'); ?>" method="post">
	<label><?php echo JText::_('MOD_LOGIN_LABEL_USERNAME'); ?></label>
	<input type="text" name="username" />
	<label><?php echo JText::_('MOD_LOGIN_LABEL_PASSWORD'); ?></label>
	<input type="password" name="password" />
	<input type="submit" value="<?php echo JText::_('MOD_LOGIN_BT_LOGIN'); ?>" />
	<input type="hidden" name="task" value="login" />
	<?php echo JHtml::_('form.token'); ?>
</form>