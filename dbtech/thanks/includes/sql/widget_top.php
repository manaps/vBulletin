<?php
if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 64))
{
	$SQL[] = '
		SELECT
			post.postid,
			post.title AS posttitle,
			thread.title AS threadtitle,
			thread.title AS title,
			thread.threadid,
			thread.forumid,
			entry_post.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'post\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_post
		LEFT JOIN $post AS post ON (post.postid = entry_post.contentid)
		LEFT JOIN $thread AS thread ON (thread.threadid = post.threadid)
		WHERE post.visible = 1
	';
}

/*DBTECH_PRO_START*/
if ((bool)self::$vbulletin->products['vbblog'] AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 1))
{
	$SQL[] = '
		SELECT
			blog_text.blogtextid AS postid,
			blog_text.title AS posttitle,
			blog.title AS threadtitle,
			blog_text.userid,
			blog.firstblogtextid AS threadid,
			0 AS forumid,
			entry_blog.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'blog\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_blog
		LEFT JOIN $blog_text AS blog_text ON (blog_text.blogtextid = entry_blog.contentid)
		LEFT JOIN $blog AS blog ON (blog.blogid = blog_text.blogid)
		WHERE blog_text.state = \'visible\'
	';
}
/*DBTECH_PRO_END*/

if ((bool)self::$vbulletin->products['dbtech_gallery'] AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 2))
{
	$SQL[] = '
		SELECT
			image.imageid AS postid,
			IF(image.title_clean, image.title_clean, image.title) AS posttitle,
			image.filename AS threadtitle,		
			image.userid,
			image_instance.shortname AS threadid,
			0 AS forumid,
			entry_dbgallery_image.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'dbgallery_image\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_dbgallery_image
		LEFT JOIN $dbtech_gallery_images AS image ON (image.imageid = entry_dbgallery_image.contentid)
		LEFT JOIN $dbtech_gallery_instances AS image_instance ON (image_instance.instanceid = image.instanceid)		
	';
}

if ((bool)self::$vbulletin->products['dbtech_downloads'] AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 128))
{
	$SQL[] = '
		SELECT
			download.downloadid AS postid,
			download.title AS posttitle,
			download.title AS threadtitle,
			download.userid,
			download.downloadid AS threadid,
			0 AS forumid,
			entry_vbdownloads_download.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'vbdownloads_download\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_vbdownloads_download
		LEFT JOIN $dbtech_downloads_download AS download ON (download.downloadid = entry_vbdownloads_download.contentid)
	';
}

if ((bool)self::$vbulletin->products['dbtech_review'] AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 32))
{
	$SQL[] = '
		SELECT
			review.reviewid AS postid,
			review.title_clean AS posttitle,
			review.title_clean AS threadtitle,
			review.title_clean AS title,
			review_instance.shortname AS threadid,
			0 AS forumid,
			entry_dbreview_review.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'dbreview_review\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_dbreview_review
		LEFT JOIN $dbtech_review_reviews AS review ON (review.reviewid = entry_dbreview_review.contentid)
		LEFT JOIN $dbtech_review_instances AS review_instance ON (review_instance.instanceid = review.instanceid)		
	';
}

if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 4))
{
	$SQL[] = '
		SELECT
			groupmessage.gmid AS postid,
			groupmessage.title AS posttitle,
			firstgroupmessage.title AS threadtitle,		
			groupmessage.postuserid AS userid,
			groupmessage.discussionid AS threadid,
			0 AS forumid,
			entry_socialgroup.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'socialgroup\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_socialgroup
		LEFT JOIN $groupmessage AS groupmessage ON (groupmessage.gmid = entry_socialgroup.contentid)
		LEFT JOIN $discussion AS discussion ON (discussion.discussionid = groupmessage.discussionid)		
		LEFT JOIN $groupmessage AS firstgroupmessage ON (firstgroupmessage.gmid = discussion.firstpostid)		
		WHERE groupmessage.state = \'visible\'
	';
}

if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 8))
{
	$SQL[] = '
		SELECT
			usernote.usernoteid AS postid,
			usernote.title AS posttitle,
			\'N/A\' AS threadtitle,		
			usernote.posterid AS userid,
			usernote.userid AS threadid,
			0 AS forumid,
			entry_usernote.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'usernote\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_usernote
		LEFT JOIN $usernote AS usernote ON (usernote.usernoteid = entry_usernote.contentid)
	';
}

if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 16))
{
	$SQL[] = '
		SELECT
			visitormessage.vmid AS postid,
			visitormessage.title AS posttitle,
			\'N/A\' AS threadtitle,		
			visitormessage.postuserid AS userid,
			visitormessage.userid AS threadid,
			0 AS forumid,
			entry_visitormessage.*
		FROM (SELECT *, COUNT(*) AS numentries FROM $dbtech_thanks_entry WHERE contenttype = \'visitormessage\' AND varname :whereCond AND dateline >= :dateline GROUP BY contentid ORDER BY NULL) AS entry_visitormessage
		LEFT JOIN $visitormessage AS visitormessage ON (visitormessage.vmid = entry_visitormessage.contentid)
		WHERE visitormessage.state = \'visible\'
	';
}
?>