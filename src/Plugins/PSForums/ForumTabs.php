<?php

namespace vBulletin\Plugins\PSForums;

class ForumTabs
{
    /**
     * @hook forumhome_complete
     */
    public function loadForumTabs(&$vbulletin, &$template_hook)
    {
        if ($vbulletin->options['psforums_tabbed_forum_home_enabled']) {
            $tabs  = range(1, 12);
            $tabID = 1;

            if (empty($vbulletin->options['psforums_tabbed_forum_home_tab1_title'])) {
                $tabID = 2;
            }

            foreach ($tabs AS $tab) {
                if (! empty($vbulletin->options['psforums_tabbed_forum_home_tab' . $tab . '_title'])) {
                    $ftabs["$tab"]['title']      = $vbulletin->options['psforums_tabbed_forum_home_tab' . $tab . '_title'];
                    $ftabs["$tab"]['forumslist'] = $vbulletin->options['psforums_tabbed_forum_home_tab' . $tab . '_forumslist'];
                    $ftabs["$tab"]['tabid']      = $tabID;
                    $tabID++;
                }
            }

            $forumcache = $vbulletin->forumcache;

            foreach ($forumcache AS $forum) {
                if ($forum['parentid'] == -1) {
                    $fheaders[] = $forum['forumid'];
                }
            }

            $jsonarray = json_encode($fheaders);

            $count = 1;

            if (empty($vbulletin->options['psforums_tabbed_forum_home_tab1_title'])) {
                $count = 2;
            }

            $defaulttabid = $count;

            if (! empty($ftabs)) {
                foreach ($ftabs AS $ftab) {
                    $ftab_fincludes                  = explode(',', $ftab['forumslist']);
                    $jsarray[$count]['newjsonarray'] = json_encode($ftab_fincludes);
                    $jsarray[$count]['tabid']        = $count;
                    $count++;
                }

                $templater = \vB_Template::create('psforums_tabbed_forum_home_main');
                $templater->register('ftabs', $ftabs);
                $templater->register('defaulttabid', $defaulttabid);
                $templater->register('jsonarray', $jsonarray);
                $templater->register('jsarray', $jsarray);
                $template_hook['forumhome_above_forums'] .= $templater->render();
            }
        }
    }

    /**
     * @hook cache_templates
     */
    public function cacheTemplates($thisScript, &$cache)
    {
        if ($thisScript == 'index') {
            $cache = array_merge($cache, array(
                'psforums_tabbed_forum_home_main'
            ));
        }
    }
}