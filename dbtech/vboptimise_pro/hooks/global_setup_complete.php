<?php
if (class_exists('vb_optimise'))
{
	vb_optimise::start_guestcache();
	vb_optimise::start_cdn(true);
}
?>