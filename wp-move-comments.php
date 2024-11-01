<?php
/*
Plugin Name: WP Move Comments
Plugin URI: http://hontap.info/2008/04/wp-move-comments-plugin.html
Description: Easy move comments from one post or page to another.
Version: 2.0
Author: learn2hack
Author URI: http://rilwis.tk

Copyright (C) 2008 Rilwis (email : rilwis@yahoo.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_action('admin_head', 'rwmc_process');

function rwmc_process() {
	global $wpdb;
	
	if (basename($_SERVER['PHP_SELF']) != 'edit-comments.php' || !isset($_REQUEST['action']) || ($_REQUEST['action'] != 'rwmc-move-comment')) {
		return;
	}
	$comment_id = (int) $_REQUEST['c'];
	$post_id = (int) $_REQUEST['p'];
	if (empty($comment_id) || empty($post_id)) {
		return;
	}
	$wpdb->query("UPDATE $wpdb->comments SET comment_post_ID=$post_id WHERE comment_ID=$comment_id");
	$wpdb->query("UPDATE $wpdb->posts SET comment_count=comment_count+1 WHERE ID=$post_id");
	$comment = get_comment($comment_id);
	$wpdb->query("UPDATE $wpdb->posts SET comment_count=comment_count-1 WHERE ID=$comment->comment_post_ID");
}

add_action('admin_head', 'rwmc_add_script');

function rwmc_add_script() {
	if (basename($_SERVER['PHP_SELF']) != 'edit-comments.php') {
		return;
	}
?>
	<script type="text/javascript">
		function rwmc_get_post_id(link) {
			postID = prompt('Please enter ID of destination post');
			if (!postID) {
				return false;
			}
			if (isNaN(postID)) {
				alert('Error!\nInvalid post ID. Please try again.');
				return false;
			}
			link.href += postID;
			link.target = '';
			return true;
		}
	</script>
<?php
}

add_filter('comment_text', 'rwmc_add_action');

function rwmc_add_action($content) {
	global $comment;
	
	if (!is_admin() || basename($_SERVER['PHP_SELF']) != 'edit-comments.php') {
		return $content;
	}
	$form = '<p><a href="?action=rwmc-move-comment&c='.$comment->comment_ID.'&p=" title="Move comment" onclick="return rwmc_get_post_id(this);">Move</a></p>';
	return $content.$form;
}
?>
