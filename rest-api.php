<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
	$date			 = new DateTime( $date );
	$now			 = new DateTime( 'now' );
	$today			 = new DateTime( date( 'Y-m-d' ) );
	$time			 = $today->diff( $now );
	$date->add( $time );
	//check if user is logged in
	$current_user	 = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		return 'error';
	}
	$student_name = get_category( $name );

	$post_params = array(
		'post_title'	 => sprintf( '%s got %d %s', $student_name->name, $points, (1 === $points ? ' point' : ' points' ) ),
		'post_content'	 => $comment,
		'post_status'	 => 'publish',
		'post_author'	 => $current_user->ID,
		'post_type'		 => 'post',
		'post_date'		 => $date->format( 'Y-m-d H:i:s' ),
	);

	$post_id = wp_insert_post( $post_params );
	update_post_meta( $post_id, 'sabs_points', $points );

	$term_taxonomy_ids = wp_set_object_terms( $post_id, [$student_name->term_id], 'category' );
	return 'success';
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/points/add', array(
		'methods'	 => 'POST',
		'callback'	 => 'sabs_rest_points_add',
	) );
} );

function sabs_rest_points_subtract() {
	$name		 = absint( filter_input( INPUT_POST, 'student_name' ) );
	$points		 = absint( filter_input( INPUT_POST, 'points' ) );
	$date		 = esc_attr( filter_input( INPUT_POST, 'date' ) );
	$category	 = esc_attr( filter_input( INPUT_POST, 'category' ) );

	if ( empty( $name ) || empty( $points ) || empty( $date ) ) {
		return 'error';
	}
	$date			 = new DateTime( $date );
	$now			 = new DateTime( 'now' );
	$today			 = new DateTime( date( 'Y-m-d' ) );
	$time			 = $today->diff( $now );
	$date->add( $time );
	//check if user is logged in
	$current_user	 = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		return 'error';
	}
	$student_name = get_category( $name );

	$post_params = array(
		'post_title'	 => sprintf( '%s spent %d %s %s', $student_name->name, $points, ( ( 1 === $points ) ? ' point' : ' points' ), ( ! empty( $category ) ? ( ' on ' . $category ) : '' ) ),
		'post_content'	 => '',
		'post_status'	 => 'publish',
		'post_author'	 => $current_user->ID,
		'post_type'		 => 'post',
		'post_date'		 => $date->format( 'Y-m-d H:i:s' ),
	);

	$post_id = wp_insert_post( $post_params );
	update_post_meta( $post_id, 'sabs_points', $points );

	$term_taxonomy_ids = wp_set_object_terms( $post_id, [$student_name->term_id], 'category' );
	return 'success';
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/points/subtract', array(
		'methods'	 => 'POST',
		'callback'	 => 'sabs_rest_points_subtract',
	) );
} );

function sabs_rest_points_transfer() {
	$name_from	 = absint( filter_input( INPUT_POST, 'student_name_from' ) );
	$name_to	 = absint( filter_input( INPUT_POST, 'student_name_to' ) );
	$points		 = absint( filter_input( INPUT_POST, 'points' ) );

	if ( empty( $name_from ) || empty( $points ) || empty( $name_to ) ) {
		return 'error';
	}
	$date			 = new DateTime( date( 'Y-m-d' ) );
	//check if user is logged in
	$current_user	 = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		return 'error';
	}
	$student_name_from	 = get_category( $name_from );
	$student_name_to	 = get_category( $name_to );

	$post_params = array(
		'post_title'	 => sprintf( '%s gave %s %d %s', $student_name_from->name, $student_name_to->name, $points, ( 1 === $points ) ? ' point' : ' points' ),
		'post_content'	 => '',
		'post_status'	 => 'publish',
		'post_author'	 => $current_user->ID,
		'post_type'		 => 'post',
		'post_date'		 => $date->format( 'Y-m-d H:i:s' ),
	);

	$post_id = wp_insert_post( $post_params );
	update_post_meta( $post_id, 'sabs_points', $points );

	$term_taxonomy_ids = wp_set_object_terms( $post_id, [$student_name_to->term_id], 'category' );
	return 'success';
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/points/transfer', array(
		'methods'	 => 'POST',
		'callback'	 => 'sabs_rest_points_transfer',
	) );
} );

function sabs_rest_student_add() {
	$name = esc_attr( filter_input( INPUT_POST, 'student_name' ) );

	if ( empty( $name ) ) {
		return 'error';
	}
	//check if user is logged in
	$current_user = wp_get_current_user();
	if ( 0 == $current_user->ID ) {
		return 'error';
	}
	$tracker_categories	 = get_option( 'sabs_tracker_categories' );
	$cat				 = $tracker_categories['youth_category'];
	if ( term_exists( $name, 'category', $cat ) ) {
		return 'exists';
	}
	wp_insert_term( $name, 'category', ['parent' => $cat] );
	return 'success';
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/student/add', array(
		'methods'	 => 'POST',
		'callback'	 => 'sabs_rest_student_add',
	) );
} );

function sabs_rest_student_get() {
	$name = esc_attr( filter_input( INPUT_GET, 'student_slug' ) );

	if ( empty( $name ) ) {
		return 'error';
	}
	//check if user is logged in
//	$current_user = wp_get_current_user();
//	if ( 0 == $current_user->ID ) {
//		return 'error';
//	}
	$student = get_term_by( 'slug', $name, 'category' );
	if ( ! $student ) {
		return 'none';
	}
	global $wpdb;
	$points_query	 = sabs_get_points_query( $student->term_id );
	if ( false === ( $points			 = get_transient( 'student_points_transient_' . $student->term_id ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$points = $wpdb->get_results( $points_query );
		set_transient( 'student_points_transient_' . $student->term_id, $points, 1 * HOUR_IN_SECONDS );
	}
	$points_m = 'No points for this student yet. ';
	if ( $points ) {
		$points_m = $points[0]->points;
	}
	$history_posts	 = get_posts( [
		'category'		 => $student->term_id,
		'posts_per_page' => 100
	] );
	$history		 = [];
	foreach ( $history_posts as $post ) {
		setup_postdata( $post );
		$history[] = [
			'title'	 => $post->post_title,
			'slug'	 => $post->post_name,
			'author' => get_the_author_meta( 'user_nicename', $post->post_author ),
			'points' => get_post_meta( $post->ID, 'sabs_points', true ),
			'date'	 => $post->post_date
		];
	}
	$data = [
		'student'	 => $student,
		'points'	 => $points_m,
		'history'	 => $history
	];
	return $data;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/student/get', array(
		'methods'	 => 'GET',
		'callback'	 => 'sabs_rest_student_get',
	) );
} );

function sabs_rest_students_report() {
	//check if user is logged in
//	$current_user = wp_get_current_user();
//	if ( 0 == $current_user->ID ) {
//		return 'error';
//	}
	global $wpdb;
	$tracker_categories	 = get_option( 'sabs_tracker_categories' );
	$cat				 = $tracker_categories['youth_category'];
	$query_guts			 = sabs_get_points_query( 0, $cat );
	$report_query_alpha	 = $query_guts . " ORDER BY t.name ASC";

	if ( false === ( $report_alpha = get_transient( 'report_alpha_transient_' . $cat ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$report_alpha = $wpdb->get_results( $report_query_alpha );
		set_transient( 'report_alpha_transient_' . $cat, $report_alpha, 1 * HOUR_IN_SECONDS );
	}
	return $report_alpha;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'sabs-tracker/v1', '/students/report', array(
		'methods'	 => 'GET',
		'callback'	 => 'sabs_rest_students_report',
	) );
} );


register_rest_field( 'post', 'meta_points', array(
	'get_callback' => function ( $data ) {
		return get_post_meta( $data['id'], 'sabs_points', true );
	},) );
