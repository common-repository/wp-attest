<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Main plugin object to define the plugin
if ( ! class_exists( 'ATTEST_LMS_WOO_PRO_WOO_ADD_TO_CART' ) ) {

	final class ATTEST_LMS_WOO_PRO_WOO_ADD_TO_CART {


		public function __construct() {

			$this->course_temp_functions();
		}

		//Course template $functions
		public function course_temp_functions() {

			if ( isset($_POST['attest_enroll_course_logged_out']) ) {

				$course = (isset($_POST['attest_course_id']) ? sanitize_text_field($_POST['attest_course_id']) : false);
				$product_ID = get_post_meta( $course, 'attest_product_related_to_course', true );

				$students_data = get_post_meta( $course, 'attest_course_students', false );
				$student_to_enroll = ( isset($students_data[0]['to_enroll']) ? $students_data[0]['to_enroll'] : false );
				$student_to_enroll_number = ( isset($students_data[0]['to_enroll_number']) ? $students_data[0]['to_enroll_number'] : false );
				$student_to_excess_error = ( isset($students_data[0]['excess_error']) ? $students_data[0]['excess_error'] : false );

				if ($student_to_enroll == 'auto') {

					$this->procced_to_checkout($product_ID);

				} elseif ($student_to_enroll == 'define') {

					$meta = get_post_meta($course, 'attest_enrolled_students', false);
					if (false != $meta && is_array($meta[0]) && count($meta) > 0) {

						$existing = $meta[0];
						if ($student_to_enroll_number > count($existing)) {

							$this->procced_to_checkout($product_ID);
						}
					} else  {

						$this->procced_to_checkout($product_ID);
					}
				}
			}
		}



		public function procced_to_checkout($product_ID) {

			global $woocommerce;

			if (false != $product_ID && ! empty($product_ID)) {

				WC()->cart->add_to_cart( $product_ID, $quantity=1 );

				wp_safe_redirect( get_permalink( wc_get_page_id( 'cart' ) ) );
				exit;
			}
		}

	}
}
