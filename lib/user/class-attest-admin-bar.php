<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main plugin object to define the plugin
 */
if ( ! class_exists( 'ATTEST_LMS_WOO_PRO_ADMIN_BAR' ) ) {

	final class ATTEST_LMS_WOO_PRO_ADMIN_BAR {


		public function __construct() {

			if (! is_user_logged_in()) {
				return;
			}

			$current_user = get_current_user_id();

			$user_data = get_userdata( $current_user );
			$roles = (array) $user_data->roles;

			if ( in_array( 'attest_student', $roles ) && ! current_user_can( 'manage_options' ) ) {
				add_filter('show_admin_bar', '__return_false');
			}
		}
	}
}
