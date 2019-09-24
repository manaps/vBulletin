<?php
if (class_exists('vboptimise_cdn'))
{
	vboptimise_cdn::apply_cdn_styles();
}

if (defined('vb_cdn_wsyiwyg'))
{
	$vbulletin->templatecache['editor_clientscript'] = $vbulletin->templatecache['editor_clientscript'] . "\n" . str_replace('$final_rendered =', '$final_rendered .=', $vbulletin->templatecache['wysiwyg_cdn_css']);
}
?>