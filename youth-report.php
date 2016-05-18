<?php

function sabs_student( $content ) {
	if ( isset( $_GET['student'] ) ) {
		$student_category_id = intval( $_GET['student'] );
	} else {
		ob_start();
		wp_list_categories( 'title_li=' );
		return ob_get_clean();
	}
	
	$student_category = get_category( $student_category_id );
	if ( empty( $student_category ) ) {
		return '<h3>Invalid Student ID</h3>';
	}
	
	/*
	 * Points
	 */
	global $wpdb;
	$points_query = sabs_get_points_query( $student_category_id );
	$points = $wpdb->get_results( $points_query );
	printf( '<h2>Student: %s</h2>', $points[0]->name );
	printf( '<h3>Current Points: %s</h3>', $points[0]->points );
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
add_shortcode( 'sabs_student', 'sabs_student' );