<?php
if (class_exists('vboptimise_cdn'))
{
	vboptimise_cdn::apply_cdn_finalise($output);
}

if (class_exists('vb_optimise'))
{
	vb_optimise::finish_guestcache($output);
}
?>