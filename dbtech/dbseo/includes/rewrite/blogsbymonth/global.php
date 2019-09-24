<?php if(!defined('IN_DBSEO')) die('Access denied.');

// #############################################################################
// DBSEO "BlogsByMonth_Global URL" class

/**
* Lets you construct & lookup custom URLs
*/
class DBSEO_Rewrite_BlogsByMonth_Global
{
	public static $format = 'Blog_BlogsByMonth_Global';
	public static $structure = 'blog.php?do=list&y=%d&m=%d';

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
		return sprintf((is_null($structure) ? self::$structure : $structure), $urlInfo['year'], $urlInfo['month'], $urlInfo['page']);
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

		if ($data['page'])
		{
			// We had a paged blog
			$replace['%page%'] = $data['page'];
		}

		if ($data['y'])
		{
			$replace['%year%'] 	= $data['y'];
			$replace['%month%'] = $data['m'];
			$replace['%day%'] 	= $data['d'];
		}

		// Handle the replacements
		$newUrl = str_replace(array_keys($replace), $replace, $rawFormat);
		
		/*DBTECH_PRO_START*/
		if (DBSEO::$config['dbtech_dbseo_custom_blog'] AND strpos($newUrl,'://') === false)
		{
			// Use a custom blog domain
			$newUrl = DBSEO::$config['dbtech_dbseo_custom_blog'] . $newUrl;
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