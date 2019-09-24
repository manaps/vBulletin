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
 * CMS Config Content Controller
 * Default View Page Controller for vB CMS
 *
 * @TODO: Ajax detection
 * @TODO: We probably want to roll this up with vBCms_Controller_BaseWidget
 * and make the node segment optional.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Controller_Widget extends vBCms_Controller
{
	/*Properties====================================================================*/

	/**
	 * The package that the controller belongs to.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * The class string id that identifies the controller.
	 *
	 * @var string
	 */
	protected $class = 'ConfigWidget';

	/**
	 * The action definitions for the controller.
	 *
	 * @var array string => bool
	 */
	protected $actions = array('Config' => true);



	/*Response======================================================================*/

	/**
	 * Authorise the current user for the current action.
	 */
	protected function authorizeAction()
	{
		
		if (! isset(vB::$vbulletin->userinfo['permissions']['cms']))
		{
			vBCMS_Permissions::getUserPerms();
		}
		
		if (!(vB::$vbulletin->userinfo['permissions']['cms']['admin']))
		{
			throw (new vB_Exception_AccessDenied());
		}
	}


	/**
	 * Config Widget
	 *
	 * @return string							- The final page output
	 */
	public function actionConfig($widget = false)
	{
		if (!$widget)
		{
			throw (new vB_Exception_404(new vB_Phrase('error', 'page_not_found')));
		}

		// Setup the templater for xhtml
		vB_View::registerTemplater(vB_View::OT_XHTML, new vB_Templater_vB());

		// Get the content controller
		$this->content = vBCms_Content::create($this->node->getPackage(), $this->node->getClass(), $this->node->getContentId());

		// Add the node as content
		$this->content->castFrom($this->node);

		// Get Widget that we're configuring
		$widgets = vBCms_Widget::getWidgetCollection(array($widget), vBCms_Item_Widget::INFO_CONFIG, $this->node->getId());
		$widgets = vBCms_Widget::getWidgetControllers($widgets, true, $this->content);

		if (!isset($widgets[$widget]))
		{
			throw (new vB_Exception_404());
		}

		$widget = $widgets[$widget];

		// Render the content's config view and return
		return $widget->getConfigView()->render(true);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/