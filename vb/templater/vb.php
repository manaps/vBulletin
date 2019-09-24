<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * vB Templater
 * Wrapper for the legacy vB style based templater and the new vB_Template object.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Templater_vB extends vB_Templater
{
	/*Properties====================================================================*/

	/**
	 * The style id of the style to use.
	 *
	 * @var int
	 */
	protected $styleid = 0;

	/**
	 * A reference to the legacy bootstrap.
	 *
	 * @var vB_Bootstrap
	 */
	protected $bootstrap;

	/**
	 * The content type of the content.
	 *
	 * @var string
	 */
	protected $content_type = 'text/html';



	/*Initialization================================================================*/

	/**
	 * Constructor.
	 * Allows a charset to be set.
	 *
	 * @param string $charset
	 */
	public function __construct($charset = false)
	{
		global $bootstrap;

		$this->bootstrap = $bootstrap;

		if ($charset)
		{
			$this->charset = $charset;
		}
	}



	/*Rendering=====================================================================*/

	/**
	 * Performs the actual rendering of the view.
	 *
	 * @param vB_View $view						- The view to render
	 * @return string							- The rendering result
	 */
	protected function render(vB_View $view)
	{
		// Set up the style info
		$this->bootstrap->force_styleid($this->styleid);
		$this->bootstrap->load_style();

		// Create a template
		$template = vB_Template::create($view->getResult());

		// Register the view data
		$template->quickRegister($view->getViewData());

		// Return the output
		return $template->render();
	}



	/*Results=======================================================================*/

	/**
	 * Notifies the templater of a result that will be rendered later.
	 *
	 * @param string $resultid
	 */
	public function notifyResult($resultid)
	{
		global $bootstrap;

		if (!in_array($resultid, $bootstrap->cache_templates))
		{
			$this->notified_results[$resultid] = true;
		}
	}


	/**
	 * Performs caching based on the prenotified results.
	 *
	 * This should be overridden by the child classes, and the notifed_results
	 * should be emptied.
	 */
	public function prefetchResources()
	{
		global $bootstrap;

		$bootstrap->cache_templates = array_merge($bootstrap->cache_templates, array_keys($this->notified_results));

		parent::prefetchResources();
	}



	/*Styles========================================================================*/

	/**
	 * Sets the style to use for successive templates.
	 *
	 * @param int $styleid						- The id of the style to use
	 */
	public function setStyle($styleid)
	{
		if (!IS_MOBILE_STYLE)
		{
			$this->styleid = $styleid;
			$this->bootstrap->force_styleid($this->styleid);
		}
	}

	/** Return the style
	* Basically for debugging
	****/
	public function getStyle()
	{
		return $this->styleid;
}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/