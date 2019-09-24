<?php
$celldata .= '<tr><td class="smallfont">
		<input type="hidden" name="forum[' . $forumid . '][' . $buttonid . '][dbtech_thanks_firstpostonly]" value="0" />
		<label for="cb_forum_' . $forumid . '_' . $button['varname'] . '_dbtech_thanks_firstpostonly">
			<input type="checkbox" name="forum[' . $forumid . '][' . $buttonid . '][dbtech_thanks_firstpostonly]" id="cb_forum_' . $forumid . '_' . $button['varname'] . '_dbtech_thanks_firstpostonly" value="1"' . (((int)$forum['dbtech_thanks_firstpostonly'] & (int)$button['bitfield']) ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;forum[' . $forumid . '][' . $button['varname'] . '][dbtech_thanks_firstpostonly]&quot;"' : '') . '/>
			' . $vbphrase['dbtech_thanks_first_post_only'] . '
		</label>
	</td></tr>
';
?>