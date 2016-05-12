<?php
/**
 * Get post by post_name
 * @global class $wpdb
 * @param string $date
 * @return integer|null
 */
function sabs_get_day_post( $date = null ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_status IN ('publish', 'future')", $date ) );
}

/**
 * Create schedule post
 * @param string|null $date
 * @return integer|\WP_Error
 */
function sabs_create_post( $date = null ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	$current_user = wp_get_current_user();
	$post_params = array(
		'post_title'    => $date,
		'post_content'  => '',
		'post_status'   => 'future',
		'post_author'   => $current_user->ID,
		'post_type'     => 'sabs_schedule',
		'post_date'     => $date,
	);
	return wp_insert_post( $post_params );
}

/**
 * Check if string submitted is a valid date of Y-m-d
 * @param string $date
 * @return boolean
 */
function sabs_is_date_valid( $date ) {
	if ( ! DateTime::createFromFormat( 'Y-m-d', $date ) ) {
		return false;
	}
	return true;
}

/**
 * Get post, if not exist, create it
 * @param string $date
 * @return integer post_id
 */
function sabs_get_or_create_post( $date = null ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	$post_id = sabs_get_day_post( $date );
	if ( ! empty( $post_id ) && ! is_wp_error( $post_id ) ) {
		return $post_id;
	}
	return sabs_create_post( $date );
}

/**
 * Register schedule post type
 */
function sabs_register_schedule_post_type() {
	$args = array(
		'public'     => true,
		'taxonomies' => array( 'category' ),
		'label'      => 'Schedule',
		'supports'   => array( 'editor' ),
    );
    register_post_type( 'sabs_schedule', $args );
}
add_action( 'init', 'sabs_register_schedule_post_type' );

/**
 * If schedule post is created directly from WP admin, set its post title to selected date
 * @param integer $post_id
 * @return null
 */
function sabs_update_schedule_post_title( $post_id ) {
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	$post = get_post( $post_id );
	if ( 'sabs_schedule' !== $post->post_type ) {
		return;
	}

	$post_title = $post->post_title;
	$post_date  = get_the_time( 'Y-m-d', $post_id );
	if ( empty( $post_title ) || 'Auto Draft' === $post_title || $post_title !== $post_date ) {
		$params = array(
			'ID'         => $post_id,
			'post_title' => $post_date,
			'post_name'  => $post_date,
		);
		wp_update_post( $params );
	}
}
add_action( 'save_post', 'sabs_update_schedule_post_title' );

/**
 * Get monday's date of this week
 * @param string $date Y-m-d
 * @return string $date Y-m-d
 */
function sabs_monday_this_week( $date ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	return date( 'Y-m-d', strtotime( 'monday this week', strtotime( 'yesterday', strtotime( $date ) ) ) );
}

/**
 * Return passed date plus seven days
 * @param string $date Y-m-d
 * @return string $date Y-m-d
 */
function sabs_next_week( $date ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	return date( 'Y-m-d', strtotime( '+7 day', strtotime( $date ) ) );
}

/**
 * Return passed date minus seven days
 * @param string $date Y-m-d
 * @return string $date Y-m-d
 */
function sabs_previous_week( $date ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	return date( 'Y-m-d', strtotime( '-7 day', strtotime( $date ) ) );
}

/**
 * Return passed date plus 1 day
 * @param string $date Y-m-d
 * @return string $date Y-m-d
 */
function sabs_tomorrow( $date ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	return date( 'Y-m-d', strtotime( '+1 day', strtotime( $date ) ) );
}

/**
 * Implement short code to show the schedule
 * @return string
 */
function sabs_schedule_view() {
	ob_start();
	$monday = filter_input( INPUT_GET, 'schedule_day' );
	if ( empty( $monday ) || ! sabs_is_date_valid( $monday ) ) {
		$monday = sabs_monday_this_week( date( 'Y-m-d' ) );
	}
	$next_week     = sabs_next_week( $monday );
	$previous_week = sabs_previous_week( $monday );
	
	// Pagination
	printf(
		'<p><a href="?schedule_day=%s">Previous Week</a> <a href="?schedule_day=%s">Next Week</a></p>', 
		$previous_week,
		$next_week
	);
	
	// Schedule
	for ( $i = 1; $i <= 7; $i++ ) {
		if ( 1 === $i ) {
			$today = $monday;
		} else {
			$today = sabs_tomorrow( $today );
		}
		printf( '<p><strong>%s</strong></p>', sabs_pretty_date( $today ) );
		$today_post_id = sabs_get_day_post( $today );
		if ( ! empty( $today_post_id ) ) {
			edit_post_link( 'Edit', '', '', $today_post_id, '' );
			setup_postdata( $today_post_id );
			the_content();
			// get categories
			$today_categories_a = wp_get_post_categories( $today_post_id, array( 'fields' => 'names' ) );
			$today_categories = array();
			if ( ! empty( $today_categories_a ) ) {
				foreach ( $today_categories_a as $category_name ) {
					$today_categories[] = $category_name;
				}
				printf( '<p>Signed Up: %s</p>', implode( ', ', $today_categories ) );
			} else {
				print '<p>Schedule set but no people selected.</p>';
			}	
		}
	}
	
	// Pagination
	printf(
		'<p><a href="?schedule_day=%s">Previous Week</a> <a href="?schedule_day=%s">Next Week</a></p>', 
		$previous_week,
		$next_week
	);
	
	return ob_get_clean();
}
add_shortcode( 'sabs_schedule', 'sabs_schedule_view' );

/**
 * Show categories on a single schedule post
 * @param string $content
 * @return string modified content
 */
function sabs_schedule_single( $content ) {
	$post = get_post();
	if ( 'sabs_schedule' === $post->post_type ) {
		$today_categories_a = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
		$today_categories = array();
		if ( ! empty( $today_categories_a ) ) {
			foreach ( $today_categories_a as $category_name ) {
				$today_categories[] = $category_name;
			}
			$content .= sprintf( '<p>Signed Up: %s</p>', implode( ', ', $today_categories ) );
		}
	}
	return $content;
}
add_filter( 'the_content', 'sabs_schedule_single' );

/**
 * Change date format to something prettier than Y-m-d
 * @param string $date
 * @return string
 */
function sabs_pretty_date( $date ) {
	if ( empty( $date ) || ! sabs_is_date_valid( $date ) ) {
		return null;
	}
	return date( 'l, F jS, Y', strtotime( $date ) );
}