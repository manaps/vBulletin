<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * Default error controller.
 * Error controller with default implementations for providing error responses for
 * 403 (Access Denied), 404 (File not Found) and 500 (Internal Server Error)
 * responses.
 *
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Controller_Error extends vB_Controller
{
	/*Properties====================================================================*/

	/**
	 * The package that the controller belongs to.
	 *
	 * @var string
	 */
	protected $package = 'vB';

	/**
	 * The class string id that identifies the controller.
	 *
	 * @var string
	 */
	protected $class = 'Error';



	/*Initialization================================================================*/

	/**
	 * Main entry point for the controller.
	 *
	 * @return string							- The final page output
	 */
	public function getResponse($parameters)
	{
		// Resolve rerouted error
		$error = in_array($parameters[0], array('403', '404', '500')) ? $parameters[0] : '404';

		// Create the standard vB templater
		$templater = new vB_Templater_vB();

		// TODO: Check what happens to style when undefined.

		// Register the templater to be used for XHTML
		vB_View::registerTemplater(vB_View::OT_XHTML, new vB_Templater_vB());

		// Create the page view
		$page_view = new vB_View_Page('page');

		// Create the body view
		$error_view = new vB_View('error_' . $error);

		// Get original requested url so we can link to retry or redirect to it after login
		$error_view->initial_url = vB_Router::getInitialURL();

		// Add the body view to the page
		$page_view->setBodyView($error_view);

		// Add general page info
		// TODO: $view->setBreadcrumbInfo(); // May not be needed
		$page_view->setPageTitle(new vB_Phrase('error', 'error_' . $error));

		return $page_view->render();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/