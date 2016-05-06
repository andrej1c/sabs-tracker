<?php
/* 
 * Plugin Name: SABS Tracker
 * Description: WordPress plugin for tracking how students earn and spend points in a program.
 * Author: Andrej Ciho, South Atlanta Bike Shop
 * Plugin URI:  https://github.com/andrej1c/sabs-tracker
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tested up to: 4.5.1
 * Author URI: http://southatlanta.bike
 */

/**
 * Show report
 * @global class $wpdb
 * @return string HTML report
 */
function sabs_report() {
	global $wpdb;
	ob_start();
	$query_guts = "
SELECT t.name, SUM(pm.meta_value) as points
FROM wp_terms t
INNER JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
LEFT JOIN wp_posts p ON p.ID = tr.object_id
LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
WHERE tt.taxonomy = 'category'
AND tt.parent = 3
AND pm.meta_key = 'sabs_points'
GROUP BY t.term_id";
	$report_query_alpha  = $query_guts . " ORDER BY t.name ASC";
	$report_query_points = $query_guts . " ORDER BY points DESC";
	?>

	<?php
	$report_alpha  = $wpdb->get_results( $report_query_alpha );
	$report_points = $wpdb->get_results( $report_query_points );
	?>

	<h3>Points (ordered by first name)</h3>

	<table>
		<tr><th>Name</th><th>Points</th></tr>
		<?php foreach($report_alpha as $record) { ?>
			<tr><td><?php echo $record->name; ?></td><td><?php echo $record->points; ?></td></tr>
		<?php } ?>
	</table>

	<hr />

	<h3>Points (ordered by points)</h3>
	<table>
		<tr><th>Name</th><th>Points</th></tr>
		<?php foreach($report_points as $record) { ?>
			<tr><td><?php echo $record->name; ?></td><td><?php echo $record->points; ?></td></tr>
		<?php } ?>
	</table>
	<?php
	return ob_get_clean();
}
add_shortcode( 'sabs_report', 'sabs_report' );

/**
 * Only allow access to logged in users
 */
function sabs_logged_in_only() {
	if ( ! is_user_logged_in() ) {
		die( 'Please Log In' );
	}
}
add_action( 'wp_head', 'sabs_logged_in_only' );

/**
 * Append points to post content if such post meta exists
 * @global type $post
 * @param string $content
 * @return string
 */
function sabs_show_points( $content ) {
	global $post;
	$points = '';
	if ( is_a( $post, 'WP_Post' ) ) {
		$points = get_post_meta( $post->ID, 'sabs_points', true );
	}
	if ( ! empty( $points ) ) {
		$content = sprintf( '<h3>Points: %d</h3>', $points ) . $content;
	}
	return $content;
}
add_filter( 'the_content', 'sabs_show_points' );

require_once 'points-metabox.php';
require_once 'scheduling.php';

require_once 'page-debug.php';
