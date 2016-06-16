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

function sabs_get_points_query( $student_category_id = 0, $term_id = 0 ) {
	if ( empty( $student_category_id ) ) {
		// Refactor to not have a hard coded category ID
		return sprintf( "
SELECT t.name, SUM(pm.meta_value) as points
FROM wp_terms t
INNER JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
LEFT JOIN wp_posts p ON p.ID = tr.object_id
LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
WHERE tt.taxonomy = 'category'
AND tt.parent = %d
AND pm.meta_key = 'sabs_points'
GROUP BY t.term_id", $term_id );
	} else {
		return sprintf( "
SELECT t.name, SUM(pm.meta_value) as points
FROM wp_terms t
INNER JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
LEFT JOIN wp_posts p ON p.ID = tr.object_id
LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
WHERE tt.taxonomy = 'category'
AND tt.term_id = %d
AND pm.meta_key = 'sabs_points'
GROUP BY t.term_id", $student_category_id );
	}
}

function sabs_get_posts_with_points_query( $student_category_id = 0 ) {
		// Refactor to not have a hard coded category ID
		return sprintf( "
SELECT p.post_date as date, pm.meta_value as points
FROM wp_terms t
INNER JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
LEFT JOIN wp_posts p ON p.ID = tr.object_id
LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
WHERE tt.taxonomy = 'category'
AND tt.term_id = %d
AND pm.meta_key = 'sabs_points'
", $student_category_id );
}

/**
 * Show report
 * @global class $wpdb
 * @return string HTML report
 */
function sabs_report() {
	global $wpdb;
	ob_start();
	$tracker_categories	 = get_option( 'sabs_tracker_categories' );
	$cat				 = $tracker_categories[ 'youth_category' ];
	$query_guts			 = sabs_get_points_query( 0, $cat );
	$report_query_alpha  = $query_guts . " ORDER BY t.name ASC";
	$report_query_points = $query_guts . " ORDER BY points DESC";
	?>

	<?php
	if ( false === ( $report_alpha		 = get_transient( 'report_alpha_transient_' . $cat ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$report_alpha = $wpdb->get_results( $report_query_alpha );
		set_transient( 'report_alpha_transient_' . $cat, $report_alpha, 1 * HOUR_IN_SECONDS );
	}

	if ( false === ( $report_points = get_transient( 'report_points_transient_' . $cat ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$report_points = $wpdb->get_results( $report_query_points );
		set_transient( 'report_points_transient_' . $cat, $report_points, 1 * HOUR_IN_SECONDS );
	}
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

function my_page_template_redirect() {
	if ( is_category() && ! isset( $_GET[ 'view_archive' ] ) ) {
		wp_redirect( home_url( '/student-report/?student=' . get_query_var('cat') ) );
        exit();
	}
}
add_action( 'template_redirect', 'my_page_template_redirect' );

/**
 * Show report for student
 * @global class $wpdb
 * @return string HTML report
 */
function sabs_student_report() {
	global $wpdb;
	ob_start();
	$bye_text				 = '<h2>You have no record of points yet!</h2>';
	$tracker_user_category	 = get_option( 'sabs_tracker_user_category' );
	if ( !$tracker_user_category ) {
		return ob_get_clean();
	}
	$user_categories_r	 = $tracker_user_category[ 'user_category' ];
	$student_category	 = false;
	$current_user_id	 = get_current_user_id();

	foreach ( $user_categories_r as $user_category ) {
		if ( $user_category[ 'user_id' ] == $current_user_id ) {
			$student_category = $user_category[ 'category_id' ];
			break;
		}
	}

	if ( !$student_category ) {
		echo $bye_text;
		return ob_get_clean();
	}

	$query_guts	 = sabs_get_posts_with_points_query( $student_category );
	if ( false === ( $report_student		 = get_transient( 'student_points_per_post_transient_' . $student_category ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$report_student = $wpdb->get_results( $query_guts );
		set_transient( 'student_points_per_post_transient_' . $student_category, $report_student, 1 * HOUR_IN_SECONDS );
	}
	?>
		<h3>Your Points</h3>

		<table>
			<tr><th>Date</th><th>Points</th></tr>
			<?php
			$count			 = 0;
			foreach ( $report_student as $record ) {
				$count += $record->points;
				?>
					<tr><td><?php echo $record->date; ?></td><td><?php echo $record->points; ?></td></tr>
			<?php } ?>
			<tr><td><strong>Total Points</strong></td><td><strong><?php echo $count; ?></strong></td></tr>
		</table>

		<hr />

	<?php
	return ob_get_clean();
}

add_shortcode( 'sabs_student_report', 'sabs_student_report' );

function sabs_tracker_scripts_enqueue( $hook ) {
	wp_enqueue_script( 'sabs_tracker_js', plugin_dir_url( __FILE__ ) . 'js/sabs-tracker.js' );
}

add_action( 'admin_enqueue_scripts', 'sabs_tracker_scripts_enqueue' );

require_once 'points-metabox.php';
require_once 'scheduling.php';
require_once 'skills.php';
require_once 'goals.php';
require_once 'youth-report.php';
require_once 'tracker-settings.php';
