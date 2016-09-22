<?php
/**
 * Limits_Meta_Box
 *
 * Inspired by Snippet from GenerateWP.com
 * Generated on April 29, 2016 01:33:28
 * @link https://generatewp.com/snippet/68ka77b/
 */


class Limits_Meta_Box {

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
			'limits',
			__( 'Limits', 'text_domain' ),
			array( $this, 'render_metabox' ),
			'sabs_schedule',
			'advanced',
			'default'
		);

	}

	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'sabs_limits_nonce_action', 'sabs_limits_nonce' );

		// Retrieve an existing value from the database.
		$limits_students = get_post_meta( $post->ID, 'sabs_limits_students', true );
		$limits_volunteers = get_post_meta( $post->ID, 'sabs_limits_volunteers', true );

		// Set default values.
		if ( empty( $limits_students ) ) {
			$limits_students = 0;
		}
		if ( empty( $limits_volunteers ) ) {
			$limits_volunteers = 0;
		}

		// Form fields.
		echo '<table class="form-table">';
		echo '	<tr>';
		echo '		<th><label for="limits" class="limits_label">' . __( 'Limit for Students', 'text_domain' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="number" id="sabs_limits_students" name="sabs_limits_students" class="limits_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr__( $limits_students ) . '">';
		echo '		</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<th><label for="limits" class="limits_label">' . __( 'Limit for Volunteers', 'text_domain' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="number" id="sabs_limits_volunteers" name="sabs_limits_volunteers" class="limits_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr__( $limits_volunteers ) . '">';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		$nonce_name   = filter_input( INPUT_POST, 'sabs_limits_nonce' );
		$nonce_action = 'sabs_limits_nonce_action';

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
		$limits_students_new	 = filter_input( INPUT_POST, 'sabs_limits_students' );
		$limits_volunteers_new	 = filter_input( INPUT_POST, 'sabs_limits_volunteers' );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'sabs_limits_students', $limits_students_new );
		update_post_meta( $post_id, 'sabs_limits_volunteers', $limits_volunteers_new );
	}

}

new Limits_Meta_Box;
