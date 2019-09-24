<?php if(!defined('IN_DBSEO')) die('Access denied.');

// #############################################################################
// DBSEO "CMSSection URL" class

/**
* Lets you construct & lookup custom URLs
*/
class DBSEO_Rewrite_CMSSection
{
	public static $format = 'CMS_CMSSection';
	public static $structure = 'content.php?%s=%d';

	/**
	 * Creates a SEO'd URL based on the URL fed
	 *
	 * @param string $url
	 * @param array $data
	 * 
	 * @return string
	 */
	public static function resolveUrl($urlInfo = array(), $structure = NULL)
	{
		if (!$urlInfo['section_id'] AND $urlInfo['section_title'])
		{
			// Lookup section title
			$urlInfo['section_id'] = DBSEO_Filter::reverseObject('cmsnode', $urlInfo['section_title']);
		}

		return sprintf((is_null($structure) ? self::$structure : $structure), DBSEO::$config['route_requestvar'], $urlInfo['section_id'], $urlInfo['page']);
	}

	/**
	 * Creates a SEO'd URL based on the URL fed
	 *
	 * @param string $url
	 * @param array $data
	 * 
	 * @return string
	 */
	public static function createUrl($data = array(), $format = NULL)
	{
		if (!count(DBSEO::$cache['rawurls']))
		{
			// Ensure we got this kickstarted
			DBSEO::initUrlCache();
		}

		// Prepare the regexp format
		$format 		= explode('_', (is_null($format) ? self::$format : $format), 2);
		$rawFormat 		= DBSEO::$cache['rawurls'][strtolower($format[0])][$format[1]];

		// Init this
		$replace = array();

		$data['sectionid'] = intval($data['sectionid']);
		if ($data['sectionid'])
		{
			// Grab our section info
			$sectionInfo = DBSEO::getObjectInfo('cmscont', $data['sectionid']);
		}

		if (!$sectionInfo['idfield'])
		{
			// User didn't exist
			return '';
		}

		// Set SEO title
		$sectionInfo['seotitle'] = DBSEO_Filter::filterText($sectionInfo['title'], NULL, !(strpos($rawFormat, 'section_id') === false), (strpos($rawFormat, 'section_id') === false), true);
		$sectionInfo['seotitle'] = $sectionInfo['seotitle'] ? $sectionInfo['seotitle'] : ($sectionInfo['url'] ? strtolower($sectionInfo['url']) : 'a');

		// Handle userid and username
		$replace['%section_id%'] 	= $sectionInfo['idfield'];
		$replace['%section_title%'] = $sectionInfo['seotitle'];

		if ($data['page'])
		{
			// We had a paged cms
			$replace['%page%'] = $data['page'];
		}

		// Handle the replacements
		$newUrl = str_replace(array_keys($replace), $replace, $rawFormat);
		
		/*DBTECH_PRO_START*/
		if (DBSEO::$config['dbtech_dbseo_custom_cms'] AND strpos($newUrl, '://') === false)
		{
			// Use a custom cms domain
			$newUrl = DBSEO::$config['dbtech_dbseo_custom_cms'] . $newUrl;
		}
		/*DBTECH_PRO_END*/

		//if (strpos($newUrl, '%') !== false)
		//{
			// We should not return true if any single URL remains
			//return '';
		//}

		// Return the new URL
		return $newUrl;
	}
}