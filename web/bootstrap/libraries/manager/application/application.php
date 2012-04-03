<?php
class JApplication
{
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
		return md5(JFactory::getConfig()->get('secret') . $seed);
	}
}