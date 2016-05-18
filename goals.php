<?php
/**
 * Register schedule post type
 */
function sabs_register_goal_post_type() {
	$args = array(
		'public'       => true,
		'taxonomies'   => array( 'category' ),
		'label'        => 'Goal',
		'supports'     => array( 'title', 'editor' ),
		'hierarchical' => true,
    );
    register_post_type( 'sabs_goal', $args );
}
add_action( 'init', 'sabs_register_goal_post_type' );

function sabs_add_categories_below_content_of_goal( $content ) {
	$post = get_post();
	if ( 'sabs_goal' !== $post->post_type ) {
		return $content;
	}

	setup_postdata( $post );
	$youth_a = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
	$youth   = array();
	if ( ! empty( $youth_a ) ) {
		foreach ( $youth_a as $category_name ) {
			$youth[] = $category_name;
		}
		sort( $youth );
		$content .= sprintf( '<p>Goal For Youth:</p><ul><li>%s</li></ul>', implode( '</li><li>', $youth ) );
	} else {
		$content .=  '<p>No student selected for this goal yet.</p>';
	}
	return $content;
}
add_filter( 'the_content', 'sabs_add_categories_below_content_of_goal' );


