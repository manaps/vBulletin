<?php
switch ($content['contenttype'])
{
	case 'post':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				post.postid,
				post.title AS posttitle,
				post.pagetext AS pagetext,
				thread.title AS threadtitle,
				thread.title AS title,
				thread.threadid,
				thread.forumid
			FROM $post AS post
			LEFT JOIN $thread AS thread ON (thread.threadid = post.threadid)
			WHERE post.postid = ?
				AND post.visible = 1
		', [
			$content['contentid']
		]);
		break;

	/*DBTECH_PRO_START*/
	case 'blog':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				blog_text.blogtextid AS postid,
				blog_text.title AS posttitle,
				blog_text.pagetext AS pagetext,
				blog.title AS threadtitle,
				blog.title AS title,
				blog.firstblogtextid AS threadid,
				0 AS forumid
			FROM $blog_text AS blog_text
			LEFT JOIN $blog AS blog ON (blog.blogid = blog_text.blogid)
			WHERE blog_text.blogtextid = ?
				AND blog_text.state = \'visible\'
		', [
			$content['contentid']
		]);
		break;
	/*DBTECH_PRO_END*/

	case 'dbgallery_image':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				image.imageid AS postid,
				IF(image.title_clean, image.title_clean, image.title) AS posttitle,
				image.text AS pagetext,
				image.filename AS threadtitle,
				image.filename AS title,
				image_instance.shortname AS threadid,
				0 AS forumid
			FROM $dbtech_gallery_images AS image
			LEFT JOIN $dbtech_gallery_instances AS image_instance ON (image_instance.instanceid = image.instanceid)
			WHERE image.imageid = ?
		', [
			$content['contentid']
		]);
		break;

	case 'vbdownloads_download':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				download.downloadid AS postid,
				download.title AS posttitle,
				download.description AS pagetext,
				download.title AS threadtitle,
				download.title AS title,
				download.downloadid AS threadid,
				0 AS forumid
			FROM $dbtech_downloads_download AS download
			WHERE download.downloadid = ?
		', [
			$content['contentid']
		]);
		break;

	case 'dbreview_review':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				review.reviewid AS postid,
				review.title_clean AS posttitle,
				review.short_desc AS pagetext,
				review.title_clean AS threadtitle,
				review.title_clean AS title,
				review_instance.shortname AS threadid,
				0 AS forumid
			FROM $dbtech_review_reviews AS review
			LEFT JOIN $dbtech_review_instances AS review_instance ON (review_instance.instanceid = review.instanceid)
			WHERE review.reviewid = ?
		', [
			$content['contentid']
		]);
		break;

	case 'socialgroup':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				groupmessage.gmid AS postid,
				groupmessage.title AS posttitle,
				groupmessage.pagetext AS pagetext,
				firstgroupmessage.title AS threadtitle,
				firstgroupmessage.title AS title,
				groupmessage.discussionid AS threadid,
				0 AS forumid
			FROM $groupmessage AS groupmessage
			LEFT JOIN $discussion AS discussion ON (discussion.discussionid = groupmessage.discussionid)
			LEFT JOIN $groupmessage AS firstgroupmessage ON (firstgroupmessage.gmid = discussion.firstpostid)
			WHERE groupmessage.gmid = ?
				AND groupmessage.state = \'visible\'
		', [
			$content['contentid']
		]);
		break;

	case 'usernote':
		$sourceContent = THANKS::$db->fetchRow('
			SELECT
				usernote.usernoteid AS postid,
				usernote.title AS posttitle,
				usernote.message AS pagetext,
				IF(usernote.title, usernote.title, \'N/A\') AS threadtitle,
				IF(usernote.title, usernote.title, \'N/A\') AS title,
				usernote.userid AS threadid,
				0 AS forumid
			FROM $usernote AS usernote
			WHERE usernote.usernoteid = ?
		', [
			$content['contentid']
		]);
		break;

	case 'visitormessage':
			$sourceContent = THANKS::$db->fetchRow('
			SELECT
				visitormessage.vmid AS postid,
				visitormessage.title AS posttitle,
				visitormessage.pagetext AS pagetext,
				IF(visitormessage.title, visitormessage.title, \'N/A\') AS threadtitle,
				IF(visitormessage.title, visitormessage.title, \'N/A\') AS title,
				visitormessage.userid AS threadid,
				0 AS forumid
			FROM $visitormessage AS visitormessage
			WHERE visitormessage.vmid = ?
				AND visitormessage.state = \'visible\'
		', [
			$content['contentid']
		]);
		break;
}
?>