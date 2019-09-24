<?php
do
{
	if (!class_exists('DBSEO'))
	{
		// Set important constants
		define('DBSEO_CWD', 	DIR);
		define('DBSEO_TIMENOW', TIMENOW);
		define('IN_DBSEO', 		true);

		// Make sure we nab this class
		require_once(DBSEO_CWD . '/dbtech/dbseo/includes/class_core.php');

		// Init DBSEO
		DBSEO::init(true);
	}

	if (!DBSEO::$config['dbtech_dbseo_active'])
	{
		// Mod is disabled
		break;
	}

	if (DBSEO::$config['_preprocessed'] OR (DBSEO_URL_SCHEME == 'https' AND strpos($vbulletin->options['bburl'], 'https:') === false))
	{
		// Clean up base
		$headinclude = preg_replace('#<base href[^>]*?>(\s*?<!--\[if IE\]><\/base><!\[endif\]-->)?#is', '', $headinclude);
	}

	if ($_REQUEST['do'] != 'doenterpwd' AND THIS_SCRIPT != 'vbcms')
	{
		// Prepend canonical URL
		DBSEO_Url_Create::addCanonical($headinclude, preg_replace('#\?.+#', '', $_SERVER['DBSEO_URI']), false);
	}

	if (!class_exists('vB_Template'))
	{
		// Ensure we have this
		require_once(DIR . '/dbtech/dbseo/includes/class_template.php');
	}

	// Make sure we do replacement variables
	$vbulletin->options['dbtech_dbseo_sitelinks_customurl'] = str_replace(array('{bburl}', '{homeurl}'), array(DBSEO::$config['_bburl'], $vbulletin->options['homeurl']), $vbulletin->options['dbtech_dbseo_sitelinks_customurl']);

	// Set the sitelinks template
	$headinclude .= vB_Template::create('dbtech_dbseo_sitelink_search')->render();

	if (
		DBSEO_URL_QUERY_FILE == 'redirect-to/'
		AND
		(
			DBSEO::$config['dbtech_dbseo_externalurls_anonymise']
			OR
			(
				!DBSEO::$config['dbtech_dbseo_externalurls_anonymise']
				AND DBSEO::$config['dbtech_dbseo_externalurls_anonymise_redirect']
			)
		)
		AND DBSEO::$config['dbtech_dbseo_externalurls_anonymise_confirmation']
		AND !empty($_GET['redirect'])
	)
	{
		// create the path to jQuery depending on the version
		$jQueryVersion = '1.7.11';
		if (DBSEO::$config['customjquery_path'])
		{
			$jQueryPath = str_replace('{version}', $jQueryVersion, DBSEO::$config['customjquery_path']);
			if (!preg_match('#^https?://#si', DBSEO::$config['customjquery_path']))
			{
				$jQueryPath = REQ_PROTOCOL . '://' . $jQueryPath;
			}
		}
		else
		{
			switch (DBSEO::$config['remotejquery'])
			{
				case 1:
				default:
					// Google CDN
					$jQueryPath = REQ_PROTOCOL . '://ajax.googleapis.com/ajax/libs/jquery/' . $jQueryVersion . '/jquery.min.js';
					break;

				case 2:
					// jQuery CDN
					$jQueryPath = REQ_PROTOCOL . '://code.jquery.com/jquery-' . $jQueryVersion . '.min.js';
					break;

				case 3:
					// Microsoft CDN
					$jQueryPath = REQ_PROTOCOL . '://ajax.aspnetcdn.com/ajax/jquery/jquery-' . $jQueryVersion . '.min.js';
					break;
			}
		}

		$footer .= '<script type="text/javascript"> window.jQuery || document.write(\'<script src="' . $jQueryPath . '">\x3C/script>\'); </script>';
		$footer .= '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/dbseo/clientscript/3rdparty/jquery.popupoverlay.min.js"></script>';
		$footer .= '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/dbseo/clientscript/core.js?v=2042"></script>';
	}
}
while (false);
?>