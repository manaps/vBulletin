<?php
// Let vB handle logged-in users accessing this page
if (!empty($vbulletin->userinfo['userid']))
{
	// Let vB handle what to do with logged-in users trying to register
	return;
}

// Test Detect FB Platform
if (is_facebookenabled() AND vB_Facebook::instance()->userIsLoggedIn()) /* logged into facebook */
{
	// Temporary fix for a potentially larger issue. Investigate all interactions with new FB Platform.
	return;
}

if ($vbulletin->options['dbtech_registration_active'] AND !in_array($vbulletin->GPC['a'], array('ver', 'act')) AND !in_array($_REQUEST['do'], array('activate', 'requestemail', 'emailcode', 'deleteactivation', 'killactivation', 'fbdisconnect')))
{
	if	(!REGISTRATION::$allowregistration
			AND (!$vbulletin->options['dbtech_registration_verify_email']	OR !$vbulletin->options['dbtech_registration_verifyemail_override'])
			AND (!$vbulletin->options['dbtech_registration_invites']		/*DBTECH_PRO_START*/OR !$vbulletin->options['dbtech_registration_invites_override']/*DBTECH_PRO_END*/)
		)
	{
		// pools closed
		eval(standard_error(fetch_error('noregister')));
	}
	
	if (!empty($vbulletin->options['dbtech_registration_daily_max_registrations']))
	{
		// Get beginning of today
		$beginOfDay = strtotime('midnight');
			
		// Fetch users who reigstered today
		$user = $db->query_first("
			SELECT COUNT(userid) AS registrations FROM " . TABLE_PREFIX . "user
			WHERE joindate >= " . $beginOfDay
		);

			if ($user['registrations'] >= $vbulletin->options['dbtech_registration_daily_max_registrations'])
			{
				// Global max
				eval(standard_error(fetch_error('dbtech_registration_maximum_daily_registrations')));
			}

	}

	if ($vbulletin->options['dbtech_registration_verify_email'] OR $vbulletin->options['dbtech_registration_invites'])
	{
		if ($_REQUEST['reg_do'] == 'verifyhash')
		{
			$vbulletin->input->clean_gpc('r', 'hash', TYPE_STR);

			// verify the hash
			REGISTRATION::verify_hash($vbulletin->GPC['hash']);
			$db->query_write("
				UPDATE " . TABLE_PREFIX . "dbtech_registration_email SET
					verified = '1'
				WHERE verifyhash = " . $db->sql_prepare($vbulletin->GPC['hash'])
			);

			// update the verified flag
			$emailInfo = $db->query_first_slave("
				SELECT email
				FROM " . TABLE_PREFIX . "dbtech_registration_email
				WHERE verifyhash = " . $db->sql_prepare($vbulletin->GPC['hash'])
			);

			// everything checks out - set cookie
			vbsetcookie('dbtech_verified_email_hash', $vbulletin->GPC['hash'], true, true, true);
			$_COOKIE[COOKIE_PREFIX . 'dbtech_verified_email_hash'] = $vbulletin->GPC['hash'];

			// make sure registration of this type are allowed
			REGISTRATION::verify_signup();

			// ini cache type
			$type = 'verify_emails';

			if ($db->query_first_slave("
				SELECT inviteid FROM " . TABLE_PREFIX . "dbtech_registration_invite
				WHERE email = " . $db->sql_prepare($emailInfo['email'])
			))
			{
				$type = 'invites';
			}

			// update cache
			++REGISTRATION::$cache['total'][$type]['verified'];

			// update datastore
			build_datastore('dbtech_registration_total', serialize(REGISTRATION::$cache['total']), 1);

			// track this
			REGISTRATION::build_log($email, 'dbtech_registration_verifyhash');

			// fire off "successfully verified email"
			$vbulletin->url = $vbulletin->options['bburl'] . '/register.php' . $vbulletin->session->vars['sessionurl_q'];
			eval(print_standard_redirect('dbtech_registration_email_verified', true, true));
		}
		else if ($_POST['reg_do'] == 'verifyemail')
		{
			$vbulletin->input->clean_gpc('r', 'dbtech_email', TYPE_STR);

			// make sure registration of this type are allowed
			REGISTRATION::verify_signup();

			if ($vbulletin->options['requireuniqueemail'])
			{
				if ($user = $vbulletin->db->query_first_slave("
					SELECT userid
					FROM " . TABLE_PREFIX . "user
					WHERE email = '" . $vbulletin->db->escape_string($vbulletin->GPC['dbtech_email']) . "'
					LIMIT 1
				"))
				{
					// track this
					REGISTRATION::build_log($vbulletin->GPC['dbtech_email'], 'dbtech_registration_emailtaken');

					// email already in use
					eval(standard_error(fetch_error('emailtaken', $vbulletin->session->vars['sessionurl'])));
				}
			}

			if (!$email = $db->query_first_slave("
				SELECT verifyhash, verified FROM " . TABLE_PREFIX . "dbtech_registration_email
				WHERE email = " . $db->sql_prepare($vbulletin->GPC['dbtech_email'])
			))
			{
				// verify email
				REGISTRATION::verify_email($vbulletin->GPC['dbtech_email']);

				// create the hash
				$hash = REGISTRATION::create_hash($vbulletin->GPC['dbtech_email']);

				// insert email
				$db->query_write("
					INSERT INTO " . TABLE_PREFIX . "dbtech_registration_email
						(email, verifyhash)
					VALUES
						(" . $db->sql_prepare($vbulletin->GPC['dbtech_email']) . ", " . $db->sql_prepare($hash) . ")
				");

				$username = $vbulletin->userinfo['username'];

				// send verification email
				eval(fetch_email_phrases('dbtech_registration_sent_verification_email', $vbulletin->userinfo['languageid']));
				require_once(DIR . '/includes/class_bbcode_alt.php');
				$plaintext_parser = new vB_BbCodeParser_PlainText($vbulletin, fetch_tag_list());
				$plaintext_parser->set_parsing_language($vbulletin->userinfo['languageid']);
				$message = $plaintext_parser->parse($message, 'privatemessage');
				vbmail($vbulletin->GPC['dbtech_email'], $subject, $message, true);

				// fire off "successfully sent email for verification"
				$vbulletin->url = $vbulletin->options['bburl'] . '/register.php' . $vbulletin->session->vars['sessionurl_q'];
				if (version_compare($vbulletin->versionnumber, '4.1.7') >= 0)
				{
					eval(print_standard_redirect(array('dbtech_registration_email_sent_x', $vbulletin->GPC['dbtech_email'], true, true)));
				}
				else
				{
					eval(print_standard_redirect('dbtech_registration_email_sent_x', true, true));
				}
			}

			if (!$email['verified'])
			{
				$username = $vbulletin->userinfo['username'];

				$hash = $email['verifyhash'];

				// send verification email
				eval(fetch_email_phrases('dbtech_registration_sent_verification_email', $vbulletin->userinfo['languageid']));
				require_once(DIR . '/includes/class_bbcode_alt.php');
				$plaintext_parser = new vB_BbCodeParser_PlainText($vbulletin, fetch_tag_list());
				$plaintext_parser->set_parsing_language($vbulletin->userinfo['languageid']);
				$message = $plaintext_parser->parse($message, 'privatemessage');
				vbmail($vbulletin->GPC['dbtech_email'], $subject, $message, true);

				$vbulletin->url = $vbulletin->options['bburl'] . '/register.php' . $vbulletin->session->vars['sessionurl_q'];
				if (version_compare($vbulletin->versionnumber, '4.1.7') >= 0)
				{
					eval(print_standard_redirect(array('dbtech_registration_email_sent_x', $vbulletin->GPC['dbtech_email'], true, true)));
				}
				else
				{
					eval(print_standard_redirect('dbtech_registration_email_sent_x', true, true));
				}
			}

			// update verify_emails sent cache
			++REGISTRATION::$cache['total']['verify_emails']['sent'];

			// update datastore
			build_datastore('dbtech_registration_total', serialize(REGISTRATION::$cache['total']), 1);

			// set cookie
			vbsetcookie('dbtech_verified_email_hash', $email['verifyhash'], true, true, true);

			// fire off "successfully verified email"
			$vbulletin->url = $vbulletin->options['bburl'] . '/register.php' . $vbulletin->session->vars['sessionurl_q'];
			eval(print_standard_redirect('dbtech_registration_email_verified', true, true));
		}
		else if	($vbulletin->options['dbtech_registration_verify_email'] AND empty($_COOKIE[COOKIE_PREFIX . 'dbtech_verified_email_hash']))
		{
			$navbits['register.php' . $vbulletin->session->vars['sessionurl_q']] = $vbphrase['register'];
			$navbits[''] = $vbphrase['dbtech_registration_verify_email'];
			$navbar = render_navbar_template(construct_navbits($navbits));

			// we need you to verify access to new registrations
			$pagetitle = $vbphrase['dbtech_registration_verify_email'];

			$templater = vB_Template::create('dbtech_registration_verify_email');
			$HTML = $templater->render();

			$templater = vB_Template::create('dbtech_registration');
				$templater->register_page_templates();
				$templater->register('HTML', 			$HTML);
				$templater->register('navclass', 		$navclass);
				$templater->register('navbar', 			$navbar);
				$templater->register('pagetitle', 		$pagetitle);
				$templater->register('pagedescription', $pagedescription);
				$templater->register('template_hook', 	$template_hook);
				$templater->register('includecss', 		$includecss);
				$templater->register('year',			date('Y'));
				$templater->register('jQueryVersion',	REGISTRATION::$jQueryVersion);
				$templater->register('version',			'2.0.7 Patch Level 2');
				$templater->register('versionnumber', 	'207pl2');
				$templater->register('headinclude', 	$headinclude);
			print_output($templater->render());
		}
	}

	if (!empty($_COOKIE[COOKIE_PREFIX . 'dbtech_verified_email_hash']))
	{
		if (!$exists = $db->query_first_slave("
			SELECT email, verified FROM " . TABLE_PREFIX . "dbtech_registration_email
			WHERE verifyhash = " . $db->sql_prepare($_COOKIE[COOKIE_PREFIX . 'dbtech_verified_email_hash'])
		))
		{
			// track this
			REGISTRATION::build_log($exists, 'dbtech_registration_invalid_hash');

			// no verified email exists
			eval(standard_error(fetch_error('dbtech_registration_invalid_hash', $vbulletin->options['contactuslink'])));
		}

		// make sure registration of this type are allowed
		REGISTRATION::verify_signup();

		// override closed registration option (hax)
		$vbulletin->options['allowregistration'] = 1;

		// set email
		$email = $emailconfirm = $_POST['email'] = $_POST['emailconfirm'] = $exists['email'];
	}

	if (REGISTRATION::$verified)
	{
		// Email has already been verified (hax)
		$vbulletin->options['verifyemail'] = 0;
	}
}