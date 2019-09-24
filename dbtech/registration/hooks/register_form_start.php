<?php
if ($vbulletin->options['dbtech_registration_active'] AND $vbulletin->options['dbtech_registration_use_custom_page'])
{
	//
	// STUFF I WILL CLEAN LATER
	//
	if ($errorlist)
	{
		$checkedoff['adminemail']	= iif($vbulletin->GPC['options']['adminemail'], 'checked="checked"');
		$checkedoff['showemail']	= iif($vbulletin->GPC['options']['showemail'], 'checked="checked"');
	}
	else
	{
		$checkedoff['adminemail']	= iif(bitwise($vbulletin->bf_misc_regoptions['adminemail'], $vbulletin->options['defaultregoptions']), 'checked="checked"');
		$checkedoff['showemail']	= iif(bitwise($vbulletin->bf_misc_regoptions['receiveemail'], $vbulletin->options['defaultregoptions']), 'checked="checked"');
	}

	$htmlonoff = ($vbulletin->options['allowhtml'] ? $vbphrase['on'] : $vbphrase['off']);
	$bbcodeonoff = ($vbulletin->options['allowbbcode'] ? $vbphrase['on'] : $vbphrase['off']);
	$imgcodeonoff = ($vbulletin->options['allowbbimagecode'] ? $vbphrase['on'] : $vbphrase['off']);
	$videocodeonoff = ($vbulletin->options['allowbbvideocode'] ? $vbphrase['on'] : $vbphrase['off']);
	$smiliesonoff = ($vbulletin->options['allowsmilies'] ? $vbphrase['on'] : $vbphrase['off']);

	// get facebook profile data to pre-populate custom profile fields
	$fb_importform_skip_fields = array();
	$fb_profilefield_info = array();
	if (is_facebookenabled() AND vB_Facebook::instance()->userIsLoggedIn())
	{
		$fb_profilefield_info = get_vbprofileinfo();
	}

	// if applicable, set up some facebook data
	if (is_facebookenabled())
	{
		// make sure current user is logged in
		if (vB_Facebook::instance()->userIsLoggedIn())
		{
			// if users are allowed to import info from facebook, generate the form
			$fbimportform = construct_fbimportform('register', $fb_importform_skip_fields);

			// populate form fields with information from facebook if its available
			$fb_userinfo = vB_Facebook::instance()->getFbUserInfo();
			if (!empty($fb_userinfo))
			{
				$show['fb_email'] = (!empty($fb_userinfo['email']) ? true : false);
				$username = (!empty($fb_userinfo['name']) ? htmlspecialchars_uni($fb_userinfo['name']) : $username);
				$email = (!empty($fb_userinfo['email'])?$fb_userinfo['email']:$email);
				$emailconfirm = (!empty($fb_userinfo['email'])?$fb_userinfo['email']:$emailconfirm);
				$timezonesel = (!empty($fb_userinfo['timezone'])?$fb_userinfo['timezone']:$timezonesel);
				$fbname = $fb_userinfo['name'];
				$fbprofileurl = get_fbprofileurl();
				$fbprofilepicurl = !empty($fb_userinfo['pic']) ? $fb_userinfo['pic'] : get_fbprofilepicurl();;
			}
		}
	}
	
	$show['email'] = ($vbulletin->options['enableemail'] AND $vbulletin->options['displayemails']) ? true : false;
	//
	// / STUFF I WILL CLEAN LATER
	//

	// Fetch the instance class
	require_once(DIR . '/dbtech/registration/includes/class_register.php');
	
	if (!REGISTRATION_REGISTER::fetch_instance_by_criterias())
	{
		// No instances satisfied with user criteria
		print_no_permission();
	}
	
	if (!empty(REGISTRATION::$cache['instance'][REGISTRATION_REGISTER::$instanceid]['daily_max']))
	{
		// Get beginning of today
		$beginOfDay = strtotime('midnight', TIMENOW);
	
		// Fetch users who reigstered today
		$user = $db->query_first("
			SELECT COUNT(userid) AS registrations FROM " . TABLE_PREFIX . "user
			WHERE joindate >= " . $beginOfDay
		);

			if ($user['registrations'] >= REGISTRATION::$cache['instance'][REGISTRATION_REGISTER::$instanceid]['daily_max'])
			{
				// Global max
				eval(standard_error(fetch_error('dbtech_registration_maximum_daily_registrations')));
			}

	}

	$instance_sections = REGISTRATION::$db->fetchAll('
		SELECT * FROM $dbtech_registration_instance_section
		WHERE instanceid = :instanceid
		ORDER BY displayorder ASC
	', array(
		':instanceid' => REGISTRATION_REGISTER::$instanceid
	));
	
	foreach ($instance_sections AS $instance_section)
	{	
		if (!REGISTRATION::$cache['section'][$instance_section['sectionid']]['active'])
		{
			// Globally inactive
			continue;
		}
	
		if (!$instance_section['active'])
		{
			// Per-instance inactive
			continue;
		}

		// Ini the section's array
		$section[$instance_section['sectionid']] = array();
	}

	$instance_fields = REGISTRATION::$db->fetchAll('
		SELECT * FROM $dbtech_registration_instance_field
		WHERE instanceid = :instanceid
		ORDER BY displayorder ASC
	', array(
		':instanceid' => REGISTRATION_REGISTER::$instanceid
	));
	
	$profilefields = $section = array();
	
	foreach ($instance_fields AS $instance_field)
	{	
		if (!REGISTRATION::$cache['field'][$instance_field['fieldid']]['active'])
		{
			// Globally inactive
			continue;
		}
	
		if (!$instance_field['active'])
		{
			// Per-instance inactive
			continue;
		}

		// Add field to array
		$section[$instance_field['sectionid']][$instance_field['fieldid']] = REGISTRATION::$cache['field'][$instance_field['fieldid']]['type'];

		if (substr(REGISTRATION::$cache['field'][$instance_field['fieldid']]['type'], 0, 5) == 'field')
		{
			// Grab profilefieldid
			$field = substr(REGISTRATION::$cache['field'][$instance_field['fieldid']]['type'], 5);

			if (substr($field, -6) == '_title')
			{
				// Grab profilefieldid
				$field = substr($field, 0, -6);
			}

			$profilefields[] = $field;
		}
	}

	if (!empty($profilefields))
	{
		$profilefields = REGISTRATION::$db->fetchAllKeyed('
			SELECT * FROM $profilefield
			WHERE profilefieldid IN(:profilefields)
		', 'profilefieldid', array(
			':profilefields'	=> implode(',', $profilefields)
		));
	}

	foreach ($section AS $sectionid => $fieldtypes)
	{
		$fields = array();
		foreach ($fieldtypes AS $fieldid => $field)
		{
			switch ($field)
			{
				case 'username':
					$templater = vB_Template::create('dbtech_registration_custompage_username');
						$templater->register('username', $username);
					$fields[] = $templater->render();
					break;
				case 'usergroup':
					$data = @unserialize($vbulletin->options['dbtech_registration_usergroup_chooser']);
					if (!is_array($data) OR !$data[0])
					{
						break;
					}

					$selectbits = '';
					foreach ($data AS $val)
					{
						$templater = vB_Template::create('userfield_select_option');
							$templater->register('key', $val);
							$templater->register('val', $vbulletin->usergroupcache[$val]['title']);
						$selectbits .= $templater->render();
					}

					$templater = vB_Template::create('dbtech_registration_custompage_usergroup');
						$templater->register('selectbits', $selectbits);
					$fields[] = $templater->render();
					break;
				case 'password':
					$templater = vB_Template::create('dbtech_registration_custompage_password');
						$templater->register('password', $password);
						$templater->register('passwordconfirm', $passwordconfirm);
					$fields[] = $templater->render();
					break;
				case 'email':
					$templater = vB_Template::create('dbtech_registration_custompage_email');
						$templater->register('email', $email);
						$templater->register('emailconfirm', $emailconfirm);
					$fields[] = $templater->render();
					break;
				case 'coppa':
					if (!$show['coppa'])
					{
						break;
					}
					
					$bgclass1 = 'alt1';
					$usecoppa = $show['coppa'];
					
					$templater = vB_Template::create('dbtech_registration_custompage_coppa');
						$templater->register('parentemail', $parentemail);
					$fields[] = $templater->render();
					break;
				case 'human_verification':
					// human verification, which we can bypass if user has been verified on facebook
					if (fetch_require_hvcheck('register') AND (!is_facebookenabled() OR (is_facebookenabled() AND !vB_Facebook::instance()->userIsLoggedIn())))
					{
						require_once(DIR . '/includes/class_humanverify.php');
						$verify =& vB_HumanVerify::fetch_library($vbulletin);
						$human_verify = $verify->output_token();
						$fields[] = $human_verify;
					}
					break;
				case 'birthday':
					// Birthday
					if ($vbulletin->options['reqbirthday'] AND !$vbulletin->options['usecoppa'])
					{
						$fb_importform_skip_fields[] = 'birthday';
						
						if ($vbulletin->options['fb_userfield_birthday'] AND !empty($fb_profilefield_info['birthday']) AND !$vbulletin->GPC['day'] AND !$vbulletin->GPC['month'] AND !$vbulletin->GPC['year'])
						{
							list($bd_month, $bd_day, $bd_year) = explode('/', $fb_profilefield_info['birthday']);
							$vbulletin->GPC['day'] = intval($bd_day);
							$vbulletin->GPC['month'] = intval($bd_month);
							$vbulletin->GPC['year'] = intval($bd_year);
						}
						
						$show['birthday'] = true;
						$monthselected[str_pad($vbulletin->GPC['month'], 2, '0', STR_PAD_LEFT)] = 'selected="selected"';
						$dayselected[str_pad($vbulletin->GPC['day'], 2, '0', STR_PAD_LEFT)] = 'selected="selected"';
						$year = !$vbulletin->GPC['year'] ? '' : $vbulletin->GPC['year'];

						// Default Birthday Privacy option to show all
						if (empty($errorlist))
						{
							$sbselected = array(2 => 'selected="selected"');
						}
						$templater = vB_Template::create('modifyprofile_birthday');
							$templater->register('birthdate', $birthdate);
							$templater->register('dayselected', $dayselected);
							$templater->register('monthselected', $monthselected);
							$templater->register('sbselected', $sbselected);
							$templater->register('year', $year);
						$birthdayfields = $templater->render();
					}
					else
					{
						$show['birthday'] = false;

						$birthdayfields = '';
					}
					
					$fields[] = $birthdayfields;
					break;
				case 'referrer':
					// Referrer
					if ($vbulletin->options['usereferrer'] AND !$vbulletin->userinfo['userid'])
					{
						exec_switch_bg();
						if ($errorlist)
						{
							$referrername = $vbulletin->GPC['referrername'];
						}
						else if ($vbulletin->GPC[COOKIE_PREFIX . 'referrerid'])
						{
							if ($referrername = $db->query_first_slave("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC[COOKIE_PREFIX . 'referrerid']))
							{
								$referrername = $referrername['username'];
							}
						}
						$show['referrer'] = true;

						$templater = vB_Template::create('dbtech_registration_custompage_referrer');
							$templater->register('referrername', $referrername);
						$fields[] = $templater->render();
					}
					else
					{
						$show['referrer'] = false;
					}
					break;
				case 'avatar':
					break;
				case 'receive_email':
					$templater = vB_Template::create('dbtech_registration_custompage_receive_email');
						$templater->register('checkedoff', $checkedoff);
					$fields[] = $templater->render();
					break;
				case 'timezone':
					$vbulletin->input->clean_array_gpc('p', array(
						'timezoneoffset' => TYPE_NUM
					));

					// where do we send in timezoneoffset?
					if ($vbulletin->GPC['timezoneoffset'])
					{
						$timezonesel = $vbulletin->GPC['timezoneoffset'];
					}
					else
					{
						$timezonesel = $vbulletin->options['timeoffset'];
					}
					
					require_once(DIR . '/includes/functions_misc.php');
					$timezoneoptions = '';
					foreach (fetch_timezone() AS $optionvalue => $timezonephrase)
					{
						$optiontitle = $vbphrase["$timezonephrase"];
						$optionselected = iif($optionvalue == $timezonesel, 'selected="selected"', '');
						$timezoneoptions .= render_option_template($optiontitle, $optionvalue, $optionselected, $optionclass);
					}
					$templater = vB_Template::create('modifyoptions_timezone');
						$templater->register('selectdst', $selectdst);
						$templater->register('timezoneoptions', $timezoneoptions);
					$timezoneoptions = $templater->render();
					
					$fields[] = $timezoneoptions;
					break;
				default:
					# Custom Profile Fields
					$profilefield = $profilefields[substr($field, strlen('field'))];
					
					$profilefieldname = $field;
					$optionalname = $profilefieldname . '_opt';
					$optionalfield = '';
					$optional = '';
					$profilefield['title'] = $vbphrase[$profilefieldname . '_title'];
					$profilefield['description'] = $vbphrase[$profilefieldname . '_desc'];
					$profilefield['currentvalue'] = '';
					
					if ($errorlist AND isset($vbulletin->GPC['userfield']["$profilefieldname"]))
					{
						$profilefield['currentvalue'] = $vbulletin->GPC['userfield']["$profilefieldname"];
					}
					
					// add profile data from facebook as a default if available
					if ($profilefield['type'] == 'input' OR $profilefield['type'] == 'textarea')
					{
						switch($profilefieldname)
						{
							case $vbulletin->options['fb_userfield_biography']:
								$profilefield['data'] = $fb_profilefield_info['biography'];
								$fb_importform_skip_fields[] = 'biography';
								break;

							case $vbulletin->options['fb_userfield_location']:
								$profilefield['data'] = $fb_profilefield_info['location'];
								$fb_importform_skip_fields[] = 'location';
								break;

							case $vbulletin->options['fb_userfield_interests']:
								$profilefield['data'] = $fb_profilefield_info['interests'];
								$fb_importform_skip_fields[] = 'interests';
								break;

							case $vbulletin->options['fb_userfield_occupation']:
								$profilefield['data'] = $fb_profilefield_info['occupation'];
								$fb_importform_skip_fields[] = 'occupation';
								break;
						}
					}

					$custom_field_holder = '';

					if ($profilefield['type'] == 'input')
					{
						if (empty($profilefield['currentvalue']) AND !empty($profilefield['data']))
						{
							$profilefield['currentvalue'] = $profilefield['data'];
						}
						else
						{
							$profilefield['currentvalue'] = htmlspecialchars_uni($profilefield['currentvalue']);
						}
						$templater = vB_Template::create('userfield_textbox');
							$templater->register('profilefield', $profilefield);
							$templater->register('profilefieldname', $profilefieldname);
						$custom_field_holder = $templater->render();
					}
					else if ($profilefield['type'] == 'textarea')
					{
						if (empty($profilefield['currentvalue']) AND !empty($profilefield['data']))
						{
							$profilefield['currentvalue'] = $profilefield['data'];
						}
						else
						{
							$profilefield['currentvalue'] = htmlspecialchars_uni($profilefield['currentvalue']);
						}
						$templater = vB_Template::create('userfield_textarea');
							$templater->register('profilefield', $profilefield);
							$templater->register('profilefieldname', $profilefieldname);
						$custom_field_holder = $templater->render();
					}
					else if ($profilefield['type'] == 'select')
					{
						$data = unserialize($profilefield['data']);
						$selectbits = '';

						if ($profilefield['optional'])
						{
							$optional = htmlspecialchars_uni($vbulletin->GPC['userfield']["$optionalname"]);

							$templater = vB_Template::create('userfield_optional_input');
								$templater->register('optional', $optional);
								$templater->register('optionalname', $optionalname);
								$templater->register('profilefield', $profilefield);
								$templater->register('tabindex', $tabindex);
							$optionalfield = $templater->render();
						}

						$foundselect = 0;
						foreach ($data AS $key => $val)
						{
							$key++;
							$selected = '';
							if (isset($profilefield['currentvalue']))
							{
								if ($key == $profilefield['currentvalue'])
								{
									$selected = 'selected="selected"';
									$foundselect = 1;
								}
							}
							else if ($profilefield['def'] AND $key == 1)
							{
								$selected = 'selected="selected"';
								$foundselect = 1;
							}

							$templater = vB_Template::create('userfield_select_option');
								$templater->register('key', $key);
								$templater->register('selected', $selected);
								$templater->register('val', $val);
							$selectbits .= $templater->render();
						}

						$show['noemptyoption'] = iif($profilefield['def'] != 2, true, false);

						if (!$foundselect AND $show['noemptyoption'])
						{
							$selected = 'selected="selected"';
						}
						else
						{
							$selected = '';
						}

						$templater = vB_Template::create('userfield_select');
							$templater->register('optionalfield', $optionalfield);
							$templater->register('profilefield', $profilefield);
							$templater->register('profilefieldname', $profilefieldname);
							$templater->register('selectbits', $selectbits);
							$templater->register('selected', $selected);
						$custom_field_holder = $templater->render();
					}
					else if ($profilefield['type'] == 'radio')
					{
						$data = unserialize($profilefield['data']);
						$radiobits = '';
						$foundfield = 0;
						
						if ($profilefield['optional'])
						{
							$optional = htmlspecialchars_uni($vbulletin->GPC['userfield']["$optionalname"]);
							if ($optional)
							{
								$foundfield = 1;
							}

							$templater = vB_Template::create('userfield_optional_input');
								$templater->register('optional', $optional);
								$templater->register('optionalname', $optionalname);
								$templater->register('profilefield', $profilefield);
								$templater->register('tabindex', $tabindex);
							$optionalfield = $templater->render();
						}

						foreach ($data AS $key => $val)
						{
							$key++;
							$checked = '';
							if (!$foundfield)
							{
								if (!$profilefield['currentvalue'] AND $key == 1 AND $profilefield['def'] == 1)
								{
									$checked = 'checked="checked"';
								}
								else if ($key == $profilefield['currentvalue'])
								{
									$checked = 'checked="checked"';
								}
							}
							
							$templater = vB_Template::create('userfield_radio_option');
								$templater->register('checked', $checked);
								$templater->register('key', $key);
								$templater->register('profilefieldname', $profilefieldname);
								$templater->register('val', $val);
							$radiobits .= $templater->render();
						}

						$templater = vB_Template::create('userfield_radio');
							$templater->register('optionalfield', $optionalfield);
							$templater->register('profilefield', $profilefield);
							$templater->register('profilefieldname', $profilefieldname);
							$templater->register('radiobits', $radiobits);
						$custom_field_holder = $templater->render();
					}
					else if ($profilefield['type'] == 'checkbox')
					{
						$data = unserialize($profilefield['data']);
						$radiobits = '';
						foreach ($data AS $key => $val)
						{
							$key++;
							if (is_array($profilefield['currentvalue']) AND in_array($key, $profilefield['currentvalue']))
							{
								$checked = 'checked="checked"';
							}
							else
							{
								$checked = '';
							}
							$templater = vB_Template::create('userfield_checkbox_option');
								$templater->register('checked', $checked);
								$templater->register('key', $key);
								$templater->register('profilefieldname', $profilefieldname);
								$templater->register('val', $val);
							$radiobits .= $templater->render();
						}
						$templater = vB_Template::create('userfield_radio');
							$templater->register('optionalfield', $optionalfield);
							$templater->register('profilefield', $profilefield);
							$templater->register('profilefieldname', $profilefieldname);
							$templater->register('radiobits', $radiobits);
						$custom_field_holder = $templater->render();
					}
					else if ($profilefield['type'] == 'select_multiple')
					{
						$data = unserialize($profilefield['data']);
						$selectbits = '';
						$selected = '';

						if ($profilefield['height'] == 0)
						{
							$profilefield['height'] = count($data);
						}

						foreach ($data AS $key => $val)
						{
							$key++;
							if (is_array($profilefield['currentvalue']) AND in_array($key, $profilefield['currentvalue']))
							{
								$selected = 'selected="selected"';
							}
							else
							{
								$selected = '';
							}
							$templater = vB_Template::create('userfield_select_option');
								$templater->register('key', $key);
								$templater->register('selected', $selected);
								$templater->register('val', $val);
							$selectbits .= $templater->render();
						}
						$templater = vB_Template::create('userfield_select_multiple');
							$templater->register('profilefield', $profilefield);
							$templater->register('profilefieldname', $profilefieldname);
							$templater->register('selectbits', $selectbits);
						$custom_field_holder = $templater->render();
					}

					$templater = vB_Template::create('userfield_wrapper');
						$templater->register('custom_field_holder', $custom_field_holder);
						$templater->register('profilefield', $profilefield);
					$fields[] = $templater->render();
			}
		}

		if (!count($fields))
		{
			// Skip this section
			continue;
		}

		$templater = vB_Template::create('dbtech_registration_custompage_section');
			$templater->register('section', REGISTRATION::$cache['section'][$sectionid]);
			$templater->register('fields',	implode("\n", $fields));
		$sections .= $templater->render();
	}

	// begin navbits
	$navbits = array('register.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['register']);

	// construct navbar
	$navbar = render_navbar_template(construct_navbits($navbits));
	
	// Add a vB hook to allow for third-party compatibility
	($hook = vBulletinHook::fetch_hook('register_form_complete')) ? eval($hook) : false;

	// set page title
	$pagetitle = (($show['coppa'] ? $vbphrase['coppa'] . ' ' : '') . construct_phrase($vbphrase['register_at_x'], $vbulletin->options['bbtitle']));

	$templater = vB_Template::create('dbtech_registration_register');
		$templater->register('sections',		$sections);
		$templater->register('errorlist',		$errorlist);
		$templater->register('instanceid',		REGISTRATION_REGISTER::$instanceid);
		$templater->register('url',				$url); # ???
		
		// birthday / coppa fields
		$templater->register('day',				$day);
		$templater->register('month',			$month);
		$templater->register('year',			$year);
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
?>