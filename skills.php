<?php
/**
 * Register schedule post type
 */
function sabs_register_skill_post_type() {
	$args = array(
		'public'       => true,
		'taxonomies'   => array( 'category' ),
		'label'        => 'Skill',
		'supports'     => array( 'title', 'editor' ),
		'hierarchical' => true,
    );
    register_post_type( 'sabs_skill', $args );
}
add_action( 'init', 'sabs_register_skill_post_type' );

function sabs_add_categories_below_content( $content ) {
	$post = get_post();
	if ( 'sabs_skill' !== $post->post_type ) {
		return $content;
	}
	
	setup_postdata( $post );
	$skilled_youth_a = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
	$skilled_youth   = array();
	if ( ! empty( $skilled_youth_a ) ) {
		foreach ( $skilled_youth_a as $category_name ) {
			$skilled_youth[] = $category_name;
		}
		sort( $skilled_youth );
		$content .= sprintf( '<p>Skilled Youth:</p><ul><li>%s</li></ul>', implode( '</li><li>', $skilled_youth ) );
	} else {
		$content .=  '<p>Nobody skilled in this yet.</p>';
	}
	return $content;
}
add_filter( 'the_content', 'sabs_add_categories_below_content' );
