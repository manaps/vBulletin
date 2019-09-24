<?php if(!defined('IN_DBSEO')) die('Access denied.');

// #############################################################################
// DBSEO "AlbumPicture URL" class

/**
* Lets you construct & lookup custom URLs
*/
class DBSEO_Rewrite_AlbumPicture
{
	public static $format = 'Album_AlbumPicture';
	public static $structure = 'album.php?albumid=%d&%s=%d';

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
		if (empty($urlInfo['user_id']) AND isset($urlInfo['user_name']))
		{
			// Grab the user ID
			$urlInfo['user_id'] = DBSEO_Filter::reverseUsername($urlInfo['user_name']);
		}

		if (empty($urlInfo['album_id']) AND isset($urlInfo['album_title']))
		{
			// Grab the album ID
			$urlInfo['album_id'] = DBSEO_Filter::reverseObject('album', $urlInfo['album_title'], $urlInfo['user_id']);
		}

		// Return the constructed URL
		return sprintf((is_null($structure) ? self::$structure : $structure), $urlInfo['album_id'], DBSEO::$config['_pictureid'], $urlInfo['picture_id'], $urlInfo['page']);
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

		if ($data[DBSEO::$config['_pictureid']])
		{
			// Grab album picture info
			$albumPictureInfo = DBSEO::getObjectInfo(DBSEO::$config['_picturestorage'], $data[DBSEO::$config['_pictureid']]);
		}

		if (!$albumPictureInfo[DBSEO::$config['_pictureid']])
		{
			// Album picture didn't exist
			return '';
		}

		// Handle picture id and picture title
		$replace['%picture_id%'] 	= $albumPictureInfo[DBSEO::$config['_pictureid']];
		$replace['%picture_title%'] = DBSEO_Filter::filterText($albumPictureInfo['caption'], NULL, !(strpos($rawFormat, 'picture_id') === false), (strpos($rawFormat, 'picture_id') === false));

		if ($data['thumb'])
		{
			// We're requesting a thumbnail
			$replace['%picture_id%'] .= 't';
		}

		$data['albumid'] = ($data['albumid'] ? $data['albumid'] : DBSEO::getContentId($albumPictureInfo));
		if ($data['albumid'])
		{
			// Grab album info
			$albumInfo = DBSEO::getObjectInfo('album', $data['albumid']);
		}
		
		if (!$albumInfo['albumid'])
		{
			// Album didn't exist
			return '';
		}

		// Store object ID
		DBSEO::$cache['_objectIds']['album'][] = $albumInfo['albumid'];

		// Handle album id and album title
		$replace['%album_id%'] 		= $albumInfo['albumid'];
		$replace['%album_title%'] 	= DBSEO_Filter::filterText($albumInfo['title'], NULL, !(strpos($rawFormat, 'album_id') === false), (strpos($rawFormat, 'album_id') === false), true);

		$data['userid'] = $albumInfo['userid'];
		if ($data['userid'])
		{
			// Grab our user info
			DBSEO::getUserInfo($data['userid']);
			$userInfo = DBSEO::$cache['userinfo'][$data['userid']];
		}
		
		if (!$userInfo['userid'])
		{
			// User didn't exist
			return '';
		}

		// Handle userid and username
		$replace['%user_id%'] 	= $userInfo['userid'];
		$replace['%user_name%'] = DBSEO_Filter::filterText($userInfo['username'], NULL, false, true, true, false);

		if ($data['page'])
		{
			// We had a paged blog
			$replace['%page%'] = $data['page'];
		}

		// Handle the replacements
		$newUrl = str_replace(array_keys($replace), $replace, $rawFormat);

		//if (strpos($newUrl, '%') !== false)
		//{
			// We should not return true if any single URL remains
			//return '';
		//}

		// Return the new URL
		return $newUrl;
	}
}