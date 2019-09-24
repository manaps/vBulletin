<?php
if (class_exists('vb_optimise'))
{
	$show['vbo_cutoff'] = $cutoff;
	
	vb_optimise::cache('whovisited', $show, $vbulletin->db);
}
?>