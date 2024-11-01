<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Object to update course upon woocommerce purchase
 */
if ( ! class_exists( 'ATTEST_LMS_WOO_PRO_WOO_ACCOUNT' ) ) {

	final class ATTEST_LMS_WOO_PRO_WOO_ACCOUNT {

		public $account;

		public function __construct() {

			$account_id = get_option('attest_template_my_account');

			if (! empty($account_id)) {
				$this->account = get_permalink($account_id);

				add_filter ( 'woocommerce_account_menu_items', array($this, 'account_link' ) );
				add_filter( 'woocommerce_get_endpoint_url', array($this, 'account_link_endpoint' ), 10, 4 );

				add_action( 'woocommerce_thankyou', array( $this, 'view_courses' ), 10, 4 );
			}
		}


		public function view_courses( $order_id ) {

		 	echo '<a class="button" href="' . $this->account . '">' . __( 'My courses', 'attest-woo-pro' ) . '</a>';
		}


		public function account_link( $menu_links ){

			$new = array( 'attest_account' => __( 'My Courses', 'attest-pro' ) );

			$menu_links = array_slice( $menu_links, 0, 1, true )
			+ $new
			+ array_slice( $menu_links, 1, NULL, true );

			return $menu_links;
		}


		public function account_link_endpoint( $url, $endpoint, $value, $permalink ){

			if( $endpoint === 'attest_account' ) {

				$url = $this->account;
			}
			return $url;
		}
	}
}
