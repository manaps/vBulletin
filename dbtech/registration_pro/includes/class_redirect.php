<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Jon Dickinson AKA Pandemikk					  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################

/**
* Handles Advanced Registration functionality.
*
* @package	Advanced Registration
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class redirect extends REGISTRATION
{
	/**
	* Verifies that the user can continue browsing
	*
	* @param	array	redirect table fields
	*/
	public static function verify_redirect($redirect, $session)
	{
		if (!$redirect['active'])
		{
			// Not active
			return false;
		}

		if (($redirect['options'] & 1) AND parent::$vbulletin->session->vars['isbot'])
		{
			// Bots can still "view"
			return false;
		}

		$addresses = preg_split('#\s+#', $redirect['whitelist'], -1, PREG_SPLIT_NO_EMPTY);
		if (in_array(IPADDRESS, $addresses))
		{
			// whitelisted
			return false;
		}

		if (empty($session[$redirect['type']]))
		{
			// why isn't this set?
			return false;
		}

		if ($redirect['type'] == 'firstactivity')
		{
			// convert timestamp to seconds (i hate denormalizing)
			$session['firstactivity'] = TIMENOW - (int)$session['firstactivity'];
		}

		if ((int)$redirect['amount'] - $session[$redirect['type']] > 0)
		{
			// they still have time to lurk
			return false;
		}

		if (isset($_COOKIE[COOKIE_PREFIX . 'dbtech_registration_persistent_' . $redirect['redirectid']]))
		{
			if ($_COOKIE[COOKIE_PREFIX . 'dbtech_registration_persistent_' . $redirect['redirectid']] == '0')
			{
				// they've been redirected already and this isn't a persistent redirect
				return false;
			}
		}

		if (self::$vbulletin->options['dbtech_registration_log_redirects'])
		{
			// log it
			$logid = self::$db->insert('dbtech_registration_redirect_log', array(
				'ipaddress' 	=> IPADDRESS,
				'type' 			=> $redirect['type'],
				'amount' 		=> intval($redirect['amount']),
				'persistent' 	=> $redirect['persistent'],
				'dateline' 		=> TIMENOW,
			));

			// track this
			parent::build_log('', 'dbtech_registration_redirect', serialize(array('redirect_log' => $logid)));
		}

		// stick a cookie on 'em
		vbsetcookie('dbtech_registration_persistent_' . $redirect['redirectid'], $redirect['persistent'], true, true, true);

		return true;
	}
}