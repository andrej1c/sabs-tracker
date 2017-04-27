<?php
/**
 * User_Category_Meta_Box
 *
 * Inspired by Snippet from GenerateWP.com
 * Generated on April 29, 2016 01:33:28
 * @link https://generatewp.com/snippet/68ka77b/
 */


class User_Category_Meta_Box {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'show_user_profile', array( $this, 'render_metabox' ) );
			add_action( 'edit_user_profile', array( $this, 'render_metabox' ) );
			add_action( 'profile_update', array( $this, 'save_fields_from_user_edit' ) );
		}

	}
	
	public function save_fields_from_user_edit( $user_id ) {
		
		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		if ( ! empty( $_POST['sabs_user_category'] ) ) {
			$custom_department_id = intval( $_POST['sabs_user_category'] );
			update_user_meta( $user_id, 'sabs_user_category', $custom_department_id );
		}
	}

public function category_selectbox( $categories, $value ) {
		$options_categories	 = get_option( 'sabs_tracker_categories' );
		$html		 = '';
		$html .= '<select class="sabs_user_category chosen-select" name="sabs_user_category" data-placeholder="Nothing Selected">';
		$html .= '<option value="-1"></option>';
		$categories_r	 = array(
			'Students'	 => array(),
			'Volunteers' => array()
		);
		foreach ( $categories as $category ) {
			$parent = '';
			if ( $options_categories[ 'youth_category' ] == $category->parent ) {
				$categories_r[ 'Students' ][] = $category;
			} else if ( $options_categories[ 'volunteers_category' ] == $category->parent ) {
				$categories_r[ 'Volunteers' ][] = $category;
			} else {
				continue;
			}
		}
		foreach ( $categories_r as $key => $parents ) {
			$html.= '<optgroup label="' . $key . '">';
			foreach ( $parents as $category ) {
				$html .= sprintf(
				'<option value="%d" %s/>%s</option>', esc_attr( $category->term_id ), $category->term_id == $value ? 'selected="selected"' : '', $category->name
				);
			}
			$html.= '</optgroup>';
		}


		$html .= '</select>';
		return $html;
	}

	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'sabs_user_category_nonce_action', 'sabs_user_category_nonce' );
		
		$categories					 = get_terms(
		'category', array(
			'hide_empty' => 0,
			'fields'	 => 'all'
		)
		);

		// Retrieve an existing value from the database.
		$user_category = get_user_meta( $post->ID, 'sabs_user_category', true );

		// Set default values.
		if ( empty( $user_category ) ) {
			$user_category = '';
		}

		// Form fields.
		echo '<table class="form-table">';
		echo '	<tr>';
		echo '		<th><label for="user_category" class="user_category_label">' . __( 'User Category', 'text_domain' ) . '</label></th>';
		echo '		<td>';
		echo $this->category_selectbox($categories, $user_category);
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		$nonce_name   = filter_input( INPUT_POST, 'sabs_user_category_nonce' );
		$nonce_action = 'sabs_user_category_nonce_action';

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
		$user_category_new = filter_input( INPUT_POST, 'sabs_user_category' );

		// Update the meta field in the database.
		update_post_meta( $post_id, 'sabs_user_category', $user_category_new );

	}

}

new User_Category_Meta_Box;
