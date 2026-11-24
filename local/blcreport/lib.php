<?php


defined('MOODLE_INTERNAL') || die();

/**
 * Extend Navigation block and add options
 *
 * @param global_navigation $navigation {@link global_navigation}
 * @return void
 */
function local_blcreport_extend_navigation(global_navigation $navigation) {
	
	global $PAGE, $CFG;
	
	$previewnode = $PAGE->navigation->add("Report", new moodle_url('/_report'), navigation_node::TYPE_CUSTOM, null, null, new pix_icon('i/report', 'grades'));
	$previewnode->showinflatnavigation = false;
	
   
}

