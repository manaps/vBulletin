<?php
if (class_exists('vb_optimise'))
{
	$show['vbo_threadid'] = $threadid;
	$show['vbo_contenttypeid'] = $contenttypeid;
	$show['vbo_cutoff'] = $cutoff;

	vb_optimise::cache('whoread', $show, $vbulletin->db);
}
?>