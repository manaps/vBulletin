<?php
$row['title'] = htmlspecialchars_uni(fetch_censored_text($row['posttitle']));
$row['url'] = self::$vbulletin->options['bburl'] . '/' . self::$vbulletin->options['dbtech_downloads_link'] . '.php?' . self::$vbulletin->session->vars['sessionurl'] . 'do=download&amp;downloadid=' . $row['postid'];
?>