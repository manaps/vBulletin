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
 * CMS Widget Route
 * Routing for vB Widget management.  Allows configuration and editing for widget
 * instances.
 *
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Route_Widget extends vB_Route
{
	/*Properties====================================================================*/

	/**
	 * The segment scheme
	 *
	 * @see vB_Route::$_segment_scheme
	 *
	 * @var array mixed
	 */
	protected $_segment_scheme = array(
		'action'		=> array (
			'optional' 	=> false,
			'values'	=> array(
							'edit',
							'config',
							'configeditor'
							),
			'default'	=> 'config'
		),
		'widget'		=> array (
			'optional'	=> false,
			'default'	=> 0
		),
		'node'			=> array (
			'optional'  => false,
			'default'	=> 0
		)
	);

	/**
	 * The class to use for error rerouting.
	 *
	 * @var string
	 */
	protected $_error_route_class = 'vB_Route_HttpError';

	/**
	 * Default path.
	 *
	 * @var string
	 */
	protected $_default_path = 'config/0/0';



	/*URL===========================================================================*/

	/**
	 * Returns a representative URL of a route.
	 * Optional segments and parameters may be passed to set the route state.
	 *
	 * @param array mixed $segments				- Assoc array of segment => value
	 * @param array mixed $parameters			- Array of parameter values, in order
	 * @return string							- The URL representing the route
	 */
	public static function getURL(array $segments = null, array $parameters = null, $absolute_path = false)
	{
		$route = vb_Route::create('vBCms_Route_Widget');

		if ($absolute_path)
		{
			$route->setAbsolutePath(true);
		}
		return $route->getCurrentURL($segments, $parameters);
	}



	/*Response======================================================================*/

	/**
	 * Returns the response for the route.
	 *
	 * @return string							- The response
	 */
	public function getResponse()
	{
		if (!$this->_is_valid)
		{
			throw (new vB_Exception_404());
		}

		$controller = new vBCms_Controller_BaseWidget($this->_parameters);
		return $controller->getResponse();
	}


	public function assertSubdirectoryUrl()
	{
		//logic is shared with the core app
		verify_subdirectory_url(vB::$vbulletin->options['vbcms_url']);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/