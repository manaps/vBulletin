<?php
$row['title'] = fetch_censored_text($row['posttitle'] ? $row['posttitle'] : $row['threadtitle']);
$row['url'] = self::$vbulletin->options['bburl'] . '/blog.php?' . self::$vbulletin->session->vars['sessionurl'] . 'bt=' . $row['postid'] . ($row['threadid'] == $row['postid'] ? '' : '#comment' . $row['postid']);
?>