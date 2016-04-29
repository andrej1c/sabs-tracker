<?php
/**
 * Points_Meta_Box
 *
 * Inspired by Snippet from GenerateWP.com
 * Generated on April 29, 2016 01:33:28
 * @link https://generatewp.com/snippet/68ka77b/
 */


class Points_Meta_Box {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'points',
			__( 'Points', 'text_domain' ),
			array( $this, 'render_metabox' ),
			'post',
			'advanced',
			'default'
		);

	}

	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'sabs_points_nonce_action', 'sabs_points_nonce' );

		// Retrieve an existing value from the database.
		$points = get_post_meta( $post->ID, 'sabs_points', true );

		// Set default values.
		if ( empty( $points ) ) {
			$points = '';
		}

		// Form fields.
		echo '<table class="form-table">';
		echo '	<tr>';
		echo '		<th><label for="points" class="points_label">' . __( 'Points', 'text_domain' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="number" id="sabs_points" name="sabs_points" class="points_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr__( $points ) . '">';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		$nonce_name   = filter_input( INPUT_POST, 'sabs_points_nonce' );
		$nonce_action = 'sabs_points_nonce_action';

		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) ) {
			return;
		}

		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if it's not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Sanitize user input.
		$points_new = filter_input( INPUT_POST, 'sabs_points' );

		// Update the meta field in the database.
		update_post_meta( $post_id, 'sabs_points', $points_new );

	}

}

new Points_Meta_Box;
