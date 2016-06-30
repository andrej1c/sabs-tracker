<?php

class SabsTrackerSettingsPage {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options_categories;
	private $options_limits;
	private $options_users;
	private $options_pages;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
		'Settings Admin', 'Sabs Tracker Settings', 'manage_options', 'sabs-tracker-settings', array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options_categories	 = get_option( 'sabs_tracker_categories' );
		$this->options_limits		 = get_option( 'sabs_tracker_limits' );
		$this->options_users		 = get_option( 'sabs_tracker_user_category' );
		$this->options_pages		 = get_option( 'sabs_tracker_pages' );
		?>
		<div class="wrap">
			<h2>Sabs Tracker Settings</h2>           
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'sabs_tracker_group' );
				do_settings_sections( 'sabs-tracker-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		$this->initialize_categories_section();
		$this->initialize_limits_section();
		$this->initialize_pages_section();
		$this->initialize_user_category_section();
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_categories_section( $input ) {
		$new_input						 = array();
		if ( isset( $input[ 'youth_category' ] ) ) {
			$new_input[ 'youth_category' ] = absint( $input[ 'youth_category' ] );
		}
		if ( isset( $input[ 'volunteers_category' ] ) ) {
			$new_input[ 'volunteers_category' ] = absint( $input[ 'volunteers_category' ] );
		}

		return $new_input;
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_limits_section( $input ) {
		$new_input					 = array();
		if ( isset( $input[ 'youth_limit' ] ) ) {
			$new_input[ 'youth_limit' ] = absint( $input[ 'youth_limit' ] );
		}
		if ( isset( $input[ 'volunteers_limit' ] ) ) {
			$new_input[ 'volunteers_limit' ] = absint( $input[ 'volunteers_limit' ] );
		}
		if ( isset( $input[ 'total_limit' ] ) ) {
			$new_input[ 'total_limit' ] = absint( $input[ 'total_limit' ] );
		}

		return $new_input;
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_user_category_section( $input ) {
		//todo
		return $input;
	}
	
	/**
	 * Sanitize each setting field as needed
	 * 
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize_pages_section( $input ) {
		$new_input					 = array();
		if ( isset( $input[ 'student_report' ] ) ) {
			$new_input[ 'student_report' ] = absint( $input[ 'student_report' ] );
		}
		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print 'Enter your settings below:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function youth_category_callback() {

		$categories = $this->get_parent_categories();
		echo '<select id="youth_category" name="sabs_tracker_categories[youth_category]">';
		foreach ( $categories as $category_id => $category_name ) {
			printf(
			'<option value="%s" %s/>%s</option>', esc_attr( $category_id ), $category_id === $this->options_categories[ 'youth_category' ] ? 'selected="selected"' : '', $category_name
			);
		}

		echo '</select>';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function volunteers_category_callback() {
		$categories = $this->get_parent_categories();

		echo '<select id="volunteers_category" name="sabs_tracker_categories[volunteers_category]">';
		foreach ( $categories as $category_id => $category_name ) {
			printf(
			'<option value="%s" %s/>%s</option>', esc_attr( $category_id ), $category_id === $this->options_categories[ 'volunteers_category' ] ? 'selected="selected"' : '', $category_name
			);
		}

		echo '</select>';
	}

	/**
	 * Print input for Youth limit
	 */
	public function youth_limits_callback() {
		printf(
		'<input type="text" id="youth_limit" name="sabs_tracker_limits[youth_limit]" value="%s" />', isset( $this->options_limits[ 'youth_limit' ] ) ? esc_attr( $this->options_limits[ 'youth_limit' ] ) : ''
		);
	}

	/**
	 * Print input for Volunteers limit
	 */
	public function volunteers_limits_callback() {
		printf(
		'<input type="text" id="volunteers_limit" name="sabs_tracker_limits[volunteers_limit]" value="%s" />', isset( $this->options_limits[ 'volunteers_limit' ] ) ? esc_attr( $this->options_limits[ 'volunteers_limit' ] ) : ''
		);
	}

	/**
	 * Print input for Total limit
	 */
	public function total_limits_callback() {
		printf(
		'<input type="text" id="total_limit" name="sabs_tracker_limits[total_limit]" value="%s" />', isset( $this->options_limits[ 'total_limit' ] ) ? esc_attr( $this->options_limits[ 'total_limit' ] ) : ''
		);
	}
	
	/**
	 * Print selectbox for Student-report page
	 */
	public function student_report_page_callback() {
		$pages = get_posts( array( 'post_type' => 'page' ) );
		echo '<select id="student_report" name="sabs_tracker_pages[student_report]">';
		foreach ( $pages as $page ) {
			printf(
			'<option value="%s" %s/>%s</option>', esc_attr( $page->ID ), $page->ID === $this->options_pages[ 'student_report' ] ? 'selected="selected"' : '', $page->post_title
			);
		}

		echo '</select>';
	}

	/**
	 * Print table for attaching users to category
	 */
	public function user_category_callback() {
		$related_users_categories	 = $this->options_users[ 'user_category' ];
		$available_users			 = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
		$categories					 = get_terms(
		'category', array(
			'hide_empty' => 0,
			'fields'	 => 'all'
		)
		);
		if ( !isset( $related_users_categories ) || count( $related_users_categories ) === 0 ) {
			$related_users_categories = array(
				'0' => array(
					'user_id'		 => -1,
					'category_id'	 => -1
				)
			);
		}

		echo '<table>';
		echo '	<thead>';
		echo '		<tr>';
		echo '			<th>User</th>';
		echo '			<th>Category</th>';
		echo '			<th>Remove</th>';
		echo '		</tr>';
		echo '	</thead>';
		echo '	<tbody>';
		foreach ( $related_users_categories as $user_cat_key => $user_cat ) {
			echo '		<tr id="' . $user_cat_key . '">';
			echo '			<td>';
			echo $this->user_selectbox( $available_users, $user_cat_key );
			echo '			</td>';
			echo '			<td>';
			echo $this->category_selectbox( $categories, $user_cat_key );
			echo '			</td>';
			echo '			<td><a href="#" class="sabs_remove_row" tilte="Remove row">Remove</a></td>';
			echo '		</tr>';
		}
		echo '		<tr id="-1">';
		echo '			<td></td><td></td><td><a href="#" class="sabs_add_row" title="Add row">Add row</a></td>';
		echo '		</tr>';
		echo '	</tbody>';
		echo '</table>';
	}

	public function user_selectbox( $available_users, $user_cat_key ) {
		$html = '';
		$html .= '<select class="sabs_user_id" name="sabs_tracker_user_category[user_category][' . $user_cat_key . '][user_id]">';
		$html .= '<option value="-1">Nothing Selected</option>';
		foreach ( $available_users as $user ) {
			$html .= sprintf(
			'<option value="%s" %s/>%s</option>', esc_attr( $user->ID ), $user->ID == $this->options_users[ 'user_category' ][ $user_cat_key ][ 'user_id' ] ? 'selected="selected"' : '', $user->display_name
			);
		}

		$html .= '</select>';
		return $html;
	}

	public function category_selectbox( $categories, $user_cat_key ) {
		$html = '';
		$html .= '<select class="sabs_category_id" name="sabs_tracker_user_category[user_category][' . $user_cat_key . '][category_id]">';
		$html .= '<option value="-1">Nothing Selected</option>';
		foreach ( $categories as $category ) {
			$parent = '';
			if ( $this->options_categories[ 'youth_category' ] == $category->parent ) {
				$parent = '--Student';
			} else if ( $this->options_categories[ 'volunteers_category' ] == $category->parent ) {
				$parent = '--Volunteers';
			}
			$html .= sprintf(
			'<option value="%s" %s/>%s</option>', esc_attr( $category->term_id ), $category->term_id == $this->options_users[ 'user_category' ][ $user_cat_key ][ 'category_id' ] ? 'selected="selected"' : '', $category->name . $parent
			);
		}

		$html .= '</select>';
		return $html;
	}

	/**
	 * Retrive parent categories
	 * @return array
	 */
	public function get_parent_categories() {
		$parent_categories = get_terms(
		'category', array(
			'parent'	 => 0,
			'hide_empty' => 0,
			'fields'	 => 'id=>name'
		)
		);
		return $parent_categories;
	}

	public function initialize_categories_section() {
		register_setting(
			'sabs_tracker_group', // Option group
			'sabs_tracker_categories', // Option name
			array( $this, 'sanitize_categories_section' ) // Sanitize
		);

		add_settings_section(
			'sabs_category_section_id', // ID
			'Attach categories for Sabs tracker', // Title
			array( $this, 'print_section_info' ), // Callback
			'sabs-tracker-settings' // Page
		);

		add_settings_field(
			'youth_category', // ID
			'Youth', // Title 
			array( $this, 'youth_category_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_category_section_id' // Section           
		);

		add_settings_field(
			'volunteers_category', // ID
			'Volunteers', // Title 
			array( $this, 'volunteers_category_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_category_section_id' // Section 
		);
	}

	public function initialize_limits_section() {
		register_setting(
			'sabs_tracker_group', // Option group
			'sabs_tracker_limits', // Option name
			array( $this, 'sanitize_limits_section' ) // Sanitize
		);

		add_settings_section(
			'sabs_limits_section_id', // ID
			'Limits for schedules', // Title
			array( $this, 'print_section_info' ), // Callback
			'sabs-tracker-settings' // Page
		);

		add_settings_field(
			'youth_limit', // ID
			'Youth Limit', // Title 
			array( $this, 'youth_limits_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_limits_section_id' // Section           
		);

		add_settings_field(
			'volunteers_limit', // ID
			'Volunteers Limit', // Title 
			array( $this, 'volunteers_limits_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_limits_section_id' // Section    
		);

		add_settings_field(
			'total_limit', // ID
			'Total Limit', // Title 
			array( $this, 'total_limits_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_limits_section_id' // Section           
		);
	}
	public function initialize_pages_section() {
		register_setting(
			'sabs_tracker_group', // Option group
			'sabs_tracker_pages', // Option name
			array( $this, 'sanitize_pages_section' ) // Sanitize
		);

		add_settings_section(
			'sabs_pages_section_id', // ID
			'Select related pages', // Title
			array( $this, 'print_section_info' ), // Callback
			'sabs-tracker-settings' // Page
		);

		add_settings_field(
			'student_report', // ID
			'Student report page', // Title 
			array( $this, 'student_report_page_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_pages_section_id' // Section           
		);

	}

	public function initialize_user_category_section() {
		register_setting(
			'sabs_tracker_group', // Option group
			'sabs_tracker_user_category', // Option name
			array( $this, 'sanitize_user_category_section' ) // Sanitize
		);

		add_settings_section(
			'sabs_user_category_section_id', // ID
			'Attach user to category', // Title
			array( $this, 'print_section_info' ), // Callback
			'sabs-tracker-settings' // Page
		);

		add_settings_field(
			'user_category', // ID
			'Map user to category', // Title 
			array( $this, 'user_category_callback' ), // Callback
			'sabs-tracker-settings', // Page
			'sabs_user_category_section_id' // Section           
		);
	}

}

if ( is_admin() ) {
	$my_settings_page = new SabsTrackerSettingsPage();
}