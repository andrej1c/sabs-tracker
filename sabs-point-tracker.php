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
	if ( is_category() && !isset( $_GET[ 'view_archive' ] ) ) {
		$tracker_pages = get_option( 'sabs_tracker_pages' );
		if ( isset( $tracker_pages[ 'student_report' ] ) ) {
			$report_page = $tracker_pages[ 'student_report' ];
			$link		 = get_permalink( $report_page );
		} else {
			$link = home_url();
		}
		if ( current_user_can( 'administrator' ) ) {
			$link .= '/?student=' . get_query_var( 'cat' );
		}
		wp_redirect( $link );
		exit();
	}
}

add_action( 'template_redirect', 'my_page_template_redirect' );

function sabs_tracker_scripts_enqueue( $hook ) {
	wp_enqueue_style(  'sabs_admin_styles', plugin_dir_url( __FILE__ ) . 'css/sabs_admin_styles.min.css' );
	wp_enqueue_style(  'sabs_chosen_css', plugin_dir_url( __FILE__ ) . 'css/chosen.min.css' );
	wp_enqueue_script(  'sabs_chosen_js', plugin_dir_url( __FILE__ ) . 'js/chosen.jquery.min.js' );
	// Register the script
	wp_register_script(  'sabs_tracker_js', plugin_dir_url( __FILE__ ) . 'js/sabs-tracker.min.js' );

	// Localize the script with new data
	$limits				 = get_option( 'sabs_tracker_limits' );
	$tracker_categories	 = get_option( 'sabs_tracker_categories' );
	wp_localize_script( 'sabs_tracker_js', 'limits', $limits );
	wp_localize_script( 'sabs_tracker_js', 'categories', $tracker_categories );

	// Enqueued script with localized data.
	wp_enqueue_script( 'sabs_tracker_js' );

}

function sabs_tracker_public_enqueue_scripts() {
    wp_enqueue_style(  'sabs_tracker_css', plugin_dir_url( __FILE__ ) . 'css/sabs-point-tracker.min.css' );
}

add_action( 'wp_enqueue_scripts', 'sabs_tracker_public_enqueue_scripts' );


add_action( 'admin_enqueue_scripts', 'sabs_tracker_scripts_enqueue' );

add_filter( 'wp_terms_checklist_args', 'wpse_98274_checklist_args' );

/**
 * Remove horrid feature that places checked categories on top.
 */
function wpse_98274_checklist_args( $args ) {

	$args[ 'checked_ontop' ] = false;
	return $args;
}

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function sabs_tracker_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} else {
			$tracker_pages = get_option( 'sabs_tracker_pages' );
			if ( isset( $tracker_pages[ 'student_report' ] ) ) {
				$report_page = $tracker_pages[ 'student_report' ];
				return get_permalink( $report_page );
			} else {
				return $redirect_to;
			}
		}
	} else {
		return $redirect_to;
	}
}

/**
 * Prevent non admins to display categories, which are not theirs
 * @param type $query
 * @return type
 */
function sabs_tracker_mess_with_query( $query ) {
	//is there a user to check?
	if ( is_category() && !current_user_can( 'administrator' ) ) {
		$options_categories	 = get_option( 'sabs_tracker_categories' );
		$youth_cat			 = $options_categories[ 'youth_category' ];
		$volunteers_cat		 = $options_categories[ 'volunteers_category' ];
		if ( cat_is_ancestor_of( $youth_cat, get_queried_object_id() ) || cat_is_ancestor_of( $volunteers_cat, get_queried_object_id() ) ) {
			$current_user_id = get_current_student_id();
			if ( !$current_user_id || !is_category( $current_user_id ) ) {
				sabs_tracker_unauthorized();
			}
		}
	}
	return $query;
}

add_filter( 'login_redirect', 'sabs_tracker_login_redirect', 10, 3 );

add_filter( 'pre_get_posts', 'sabs_tracker_mess_with_query' );

function sabs_tracker_unauthorized( ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	get_template_part( 404 ); exit();
}

function sabs_rest_get_students() {
	$tracker_categories	 = get_option( 'sabs_tracker_categories' );
	$cat				 = $tracker_categories['youth_category'];
	$categories			 = get_terms(
	'category', array(
		'hide_empty' => 0,
		'fields'	 => 'all',
		'child_of'	 => $cat
	)
	);
	if ( empty( $categories ) ) {
		return null;
	}

	return $categories;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/students/all', array(
		'methods'	 => 'GET',
		'callback'	 => 'sabs_rest_get_students',
	) );
} );

function sabs_rest_points_add() {
	$name	 = absint( filter_input( INPUT_POST, 'student_name' ) );
	$points	 = absint( filter_input( INPUT_POST, 'points' ) );
	$date	 = esc_attr( filter_input( INPUT_POST, 'date' ) );
	$comment = esc_html( filter_input( INPUT_POST, 'comment' ) );
	if ( empty( $name ) || empty( $points ) || empty( $date ) ) {
		return 'error';
	}
	//check if user is logged in
	$current_user = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		return 'error';
	}
	$student_name	 = get_category( $name );

	$post_params = array(
		'post_title'	 => ( $student_name->name . ' got ' . $points . (1 === $points ? ' point' : ' points') ),
		'post_content'	 => $comment,
		'post_status'	 => 'publish',
		'post_author'	 => $current_user->ID,
		'post_type'		 => 'post',
		'post_date'		 => $date,
	);
	
	$post_id	 = wp_insert_post( $post_params );
//	return $post_id;
	update_post_meta( $post_id, 'sabs_points', $points );

	$term_taxonomy_ids = wp_set_object_terms( $post_id, [$student_name->term_id], 'category' );
	return 'success';
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'sabs-tracker/v1', '/points/add', array(
    'methods' => 'POST',
    'callback' => 'sabs_rest_points_add',
  ) );
} );
function sabs_rest_points_subtract() {
	$name	 = absint( filter_input( INPUT_POST, 'student_name' ) );
	$points	 = absint( filter_input( INPUT_POST, 'points' ) );
	$date	 = esc_attr( filter_input( INPUT_POST, 'date' ) );
	$category = esc_html( filter_input( INPUT_POST, 'category' ) );
	if ( empty( $name ) || empty( $points ) || empty( $date ) ) {
		return 'error';
	}
	//check if user is logged in
	$current_user = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		return 'error';
	}
	$student_name	 = get_category( $name );

	$post_params = array(
		'post_title'	 => ( $student_name->name . ' spent ' . $points 
		. (1 === $points ? ' point' : ' points' . ( ! empty( $category ) ? ' on ' . $category : '')) ),
		'post_content'	 => '',
		'post_status'	 => 'publish',
		'post_author'	 => $current_user->ID,
		'post_type'		 => 'post',
		'post_date'		 => $date,
	);
	
	$post_id	 = wp_insert_post( $post_params );
//	return $post_id;
	update_post_meta( $post_id, 'sabs_points', $points );

	$term_taxonomy_ids = wp_set_object_terms( $post_id, [$student_name->term_id], 'category' );
	return 'success';
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'sabs-tracker/v1', '/points/subtract', array(
    'methods' => 'POST',
    'callback' => 'sabs_rest_points_subtract',
  ) );
} );

require_once 'points-metabox.php';
require_once 'limits-metabox.php';
require_once 'user-category-metabox.php';
require_once 'scheduling.php';
require_once 'skills.php';
require_once 'goals.php';
require_once 'youth-report.php';
require_once 'tracker-settings.php';
