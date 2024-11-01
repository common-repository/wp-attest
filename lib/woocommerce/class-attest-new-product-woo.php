<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Object to create woocommerce product
 */
if ( ! class_exists( 'ATTEST_LMS_WOO_PRO_WOO_NEW_PRODUCT' ) ) {

	final class ATTEST_LMS_WOO_PRO_WOO_NEW_PRODUCT {


		public function __construct() {

			add_action( 'attest_do_woo_process', array( $this, 'save_post_woo' ), 100, 2 );
		}


		public function save_post_woo($post_ID, $post) {

			$post_type = get_post_type( $post_ID );
			if ($post_type == 'attest_course') {

				$price = $this->get_price($post_ID);
				$old_product_ID = get_post_meta( $post_ID, 'attest_product_related_to_course', true );

				$thumb_product = $old_product_ID;
				if (false != $price['price']) {

					if (false != $old_product_ID && ! empty($old_product_ID)) {

						$this->update_post_meta($post_ID, $old_product_ID, $price);
					} else {

						$new_product_id = $this->add_new($post->post_title);
						$this->update_post_meta($post_ID, $new_product_id, $price);

						$thumb_product = $new_product_id;
					}
				} else {

					$this->update_old_post($post_ID, $old_product_ID);
				}

				$thumb = get_post_thumbnail_id($post_ID);
				set_post_thumbnail($thumb_product, $thumb);
			}
		}


		public function get_price($post_id) {

			$price_actual = $price_sale = false;

			$price = get_post_meta( $post_id, 'attest_course_price', true );
			if ($price == 'paid') {
				$amount_data = get_post_meta( $post_id, 'attest_course_price_amount', false );
				if ( ! empty($amount_data) ) {
					$price_sale = (isset($amount_data[0]['sale']) ? esc_attr($amount_data[0]['sale']) : false);
					$price_actual = (isset($amount_data[0]['actual']) ? esc_attr($amount_data[0]['actual']) : false);
				}
			} else {

				$price_actual = false;
				$price_sale = false;
			}

			return array(
				'price'      => $price_actual,
				'sale_price' => $price_sale,
			);
		}


		public function add_new($title) {

			$my_post = array(
				'post_title'    => $title,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type'     => 'product'
			);

			// Insert the post into the database
			$product_ID = wp_insert_post( $my_post );

			if ( ! is_wp_error( $product_ID ) ){
				return $product_ID;
			} else {
				return false;
			}
		}


		public function update_post_meta($course_ID, $product_ID, $price) {

			if (!$product_ID) {
				return;
			}

			$current_post = array();
			$current_post['post_status'] = 'publish';
    		wp_update_post($product_ID);

			update_post_meta($product_ID, '_regular_price', $price['price'] );
			if (false != $price['sale_price']) {
				update_post_meta($product_ID, '_price', $price['sale_price'] );
			} else {
				update_post_meta($product_ID, '_price', $price['price'] );
			}
			update_post_meta($product_ID, '_sale_price', $price['sale_price'] );
			update_post_meta($product_ID, '_virtual', 1 );
			update_post_meta($product_ID, '_stock_status', 'instock' );

			update_post_meta($course_ID, 'attest_product_related_to_course', $product_ID);
			update_post_meta($product_ID, 'attest_course_related_to_product', $course_ID);
		}


		public function update_old_post($course_ID, $old_product_ID) {

			$current_post = array();
			$current_post['post_status'] = 'draft';
    		wp_update_post($old_product_ID);

			delete_post_meta($course_ID, 'attest_product_related_to_course');
			delete_post_meta($old_product_ID, 'attest_course_related_to_product');
		}
	}
}
