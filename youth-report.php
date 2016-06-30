<?php

function sabs_student( $content ) {
	if ( !current_user_can( 'administrator' ) ) {
		$bye_text			 = '<h2>You have no record of points yet!</h2>';
		$student_category_id = get_current_student_id();
		if ( !$student_category_id ) {
			return $bye_text;
		}
	} else {
		if ( isset( $_GET[ 'student' ] ) ) {
			$student_category_id = intval( $_GET[ 'student' ] );
		} else {
			ob_start();
			wp_list_categories( 'title_li=' );
			return ob_get_clean();
		}

		$student_category = get_category( $student_category_id );
		if ( empty( $student_category ) ) {
			return '<h3>Invalid Student ID</h3>';
		}
	}

	/*
	 * Points
	 */
	global $wpdb;
	$points_query	 = sabs_get_points_query( $student_category_id );
	if ( false === ( $points			 = get_transient( 'student_points_transient_' . $student_category_id ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$points = $wpdb->get_results( $points_query );
		set_transient( 'student_points_transient_' . $student_category_id, $points, 1 * HOUR_IN_SECONDS );
	}
	
	if ( $points ) {
		printf( '<h2>Student: %s</h2>', $points[ 0 ]->name );
		printf( '<h3>Current Points: %s</h3>', $points[ 0 ]->points );
	} else {
		print( '<h2>No points for this student yet.</h2>' );
	}
	printf( '<p><a href="%s?view_archive=yes">View Points History</a></p>', get_category_link( $student_category_id ) );
	/*
	 * Upcoming Schedule
	 */
	$schedule_query_args = array(
		'post_type'      => 'sabs_schedule',
		'post_status'    => 'future',
		'posts_per_page' => 10,
		'cat'            => $student_category_id,
	);
	$schedule_query = new WP_Query( $schedule_query_args );
	if ( $schedule_query->have_posts() ) {
		print( '<h2>Scheduled for</h2><ul>' );
		while ( $schedule_query->have_posts() ) {
			$schedule_query->the_post();
			$title_date = sabs_pretty_date( get_the_title() );
			printf( '<li><a href="%s">%s</a></li>', get_permalink( $schedule_query->post->ID ), $title_date );
		}
		print( '</ul>' );
	}
	
	/*
	 * Skills
	 */
	$skill_query_args = array(
		'post_type'      => 'sabs_skill',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'cat'            => $student_category_id,
	);
	$skill_query = new WP_Query( $skill_query_args );
	if ( $skill_query->have_posts() ) {
		print( '<h2>Current Skills</h2><ul>' );
		while ( $skill_query->have_posts() ) {
			$skill_query->the_post();
			printf( '<li><a href="%s">%s</a></li>', get_permalink( $skill_query->post->ID ), get_the_title() );
		}
		print( '</ul>' );
	} else {
		print( '<h3>No Skills on File</h3>' );
	}
	
	/*
	 * Goals
	 */
	$goal_query_args = array(
		'post_type'      => 'sabs_goal',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'cat'            => $student_category_id,
	);
	$goal_query = new WP_Query( $goal_query_args );
	if ( $goal_query->have_posts() ) {
		print( '<h2>Goals For Student:</h2><ul>' );
		while ( $goal_query->have_posts() ) {
			$goal_query->the_post();
			printf( '<li><a href="%s">%s</a></li>', get_permalink( $goal_query->post->ID ), get_the_title() );
		}
		print( '</ul>' );
	} else {
		print( '<h3>No Goals on File</h3>' );
	}
	
	return ob_get_clean();
}

function student_report_page_template_redirect() {
	if ( is_page() ) {
		$tracker_pages = get_option( 'sabs_tracker_pages' );
		if ( isset( $tracker_pages[ 'student_report' ] ) ) {
			$report_page = $tracker_pages[ 'student_report' ];
			if ( is_page( $report_page ) ) {
				add_filter( 'the_content', 'sabs_student' ) ;
			}
		}
	}
}

add_action( 'template_redirect', 'student_report_page_template_redirect' );

//add_shortcode( 'sabs_student', 'sabs_student' );

/**
 * Get category associated with current logged student
 * 
 * @return boolean | ID
 */
function get_current_student_id() {
	$tracker_user_category = get_option( 'sabs_tracker_user_category' );
	if ( !$tracker_user_category ) {
		return false;
	}
	$user_categories_r	 = $tracker_user_category[ 'user_category' ];
	$student_category_id = false;
	$current_user_id	 = get_current_user_id();

	foreach ( $user_categories_r as $user_category ) {
		if ( $user_category[ 'user_id' ] == $current_user_id ) {
			$student_category_id = $user_category[ 'category_id' ];
			break;
		}
	}
	if ( !$student_category_id || -1 == $student_category_id ) {
		return false;
	} else {
		return $student_category_id;
	}
}