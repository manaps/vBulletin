<?php
if (class_exists('vboptimise_cdn'))
{
	vboptimise_cdn::apply_cdn_finalise($this->doc);
}
?>