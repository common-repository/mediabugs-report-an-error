<?php
/*
 Plugin Name: MediaBugs
 Plugin URI: http://mediabugs.org
 Description: Automatically insert the MediaBugs widget into your WordPress posts.
 Version: 1.0
 Author: XOXCO, Ben Brown <ben@xoxco.com>
 Author URI: http://xoxco.com
 */



function install_MediaBugs() {
	update_option('mb_insert','checked');
	update_option('mb_icon','checked');
	update_option('mb_defaulttext','Report an error');
}

function mb_prep($str) {
	$str = htmlspecialchars($str);
	$str = preg_replace("/\'/","&#146;",$str);
	return $str;
}

function mediabugs_widget() { 
	global $post;


	$mb_icon = get_option('mb_icon');
	$mb_text = get_option('mb_defaulttext');
	if ($mb_text =='') {
		$mb_text = 'Report an error';
	}

	$blog_name = get_bloginfo('name');
	$str = "<a href=\"#\" onclick=\"return reportMediaBug('" . 
			mb_prep(get_the_title()) . 
			"','" .
			mb_prep($blog_name) . 
			"','" .
			mb_prep(get_the_author()) . 
			"','" .
			the_date('Y-m-d','','',false) . 
			"','" .
			get_permalink($post->ID) .
			"');\">";
	if ($mb_icon=='checked') { 
		$str .= '<img src="'.WP_PLUGIN_URL.'/mediabugs-report-an-error/img/reporterror.png" alt="Report an Error" border="0" align="absmiddle">&nbsp;';
	}
	
	$str .= $mb_text .'</a>';
	
	return $str;
}

function mb_head() {
	echo '<script type="text/javascript" src="http://mediabugs.org/widget/widget.js"></script>';
}

function mb_add_widget($content) {

	if (!is_page() && !is_feed()) {
			return $content.'<p>'.mediabugs_widget() .'</p>';
	} else {
		return $content;
	}

}

function mb_menu_items() {
		add_options_page(
		__('MediaBugs Options', 'mediabugs')
		, __('MediaBugs', 'mediabugs')
		, 8
		, basename(__FILE__)
		, 'mb_options'
		);
}

function mb_options() {
	$mb_insert = get_option('mb_insert');
	$mb_icon = get_option('mb_icon');
	$mb_text = get_option('mb_defaulttext');
	if ($mb_text =='') {
		$mb_text = 'Report an error';
	}
	echo '<div class="wrap">
			<form action="'.get_bloginfo('wpurl').'/wp-admin/index.php" method="post">
			<h2>MediaBugs Widget</h2>
			<p>This plugin adds a link to the end of each post that allows users to file error reports via <a href="http://mediabugs.org">MediaBugs.org.</a> (We have <a href="http://mediabugs.org/pages/wordpress">more information on how to use MediaBugs and this plugin</a> at the MediaBugs site.)</p>
			<p><input type="checkbox" value="checked" name="mb_insert" '.$mb_insert.'> Automatically append the "Report an error" link to all posts?</p>
			<p>
				Text for link:
				<input type="text" value="' . htmlspecialchars($mb_text) .'" name="mb_defaulttext">
			</p>
			<p><input type="checkbox" value="checked" name="mb_icon" '.$mb_icon.'> Use the "Report an error" icon next to the text?</p>			
			<p>If you choose not to automatically insert the link, you may insert it manually in your theme using the following code in your post template:</p>
			<p><code>&lt;?= php echo mediabugs_widget(); ?&gt;</code></p>
			<p class="submit">
				<input type="submit" name="submit_button" value="Update" /> 			
			</p>
			<input type="hidden" name="mb_action" value="mb_update" />
			</form>
		</div>';

}


function mb_options_update() {
	if (!empty($_REQUEST['mb_action'])) {
		update_option('mb_insert',$_REQUEST['mb_insert']);
		update_option('mb_defaulttext',$_REQUEST['mb_defaulttext']);
		update_option('mb_icon',$_REQUEST['mb_icon']);
		header('Location: '.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=mediabugs.php&updated=true');
		die();
	}		
}



$mb_insert = get_option('mb_insert');

if ($mb_insert=='checked') { 
	add_filter('the_content','mb_add_widget');
}
add_action('wp_head','mb_head');
add_action('admin_menu', 'mb_menu_items');
add_action('init', 'mb_options_update', 9999);
