<?php

namespace vBulletin\Plugins\PSForums;

class LoadStyleAssets
{
    public function run(&$vbulletin, &$template_hook)
    {
        $manifest = json_decode(file_get_contents(CWD . '/clientscript/assets/mix-manifest.json'), true);

        $template_hook['headinclude_css'] .= '<link rel="stylesheet" href="clientscript/assets' . $manifest['/css/app.css'] . '">';
        $template_hook['footer_javascript'] .= '<script type="text/javascript" src="clientscript/assets' . $manifest['/js/app.js'] . '"></script>';
    }
}