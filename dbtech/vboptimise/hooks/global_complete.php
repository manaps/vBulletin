<?php
$output = str_replace('<!--VBO_STATS-->', '<br />Page generated in <b>' . vb_number_format(microtime(true) - TIMESTART, 5) . '</b> seconds with <b>' . $vbulletin->db->querycount . "</b> queries. Memory Usage: <b>" . vb_number_format(memory_get_usage(), 2, true) . "</b><br />", $output);
?>