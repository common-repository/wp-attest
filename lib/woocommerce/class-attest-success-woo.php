<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Object to update course upon woocommerce purchase
 */
if ( ! class_exists( 'ATTEST_LMS_WOO_PRO_WOO_SUCCESS' ) ) {

	final class ATTEST_LMS_WOO_PRO_WOO_SUCCESS {

		/**
		 * DB tabble used in plugin
		 *
		 * @var String
		 */
		protected static $plugin_table = 'attest_students';

		public $user_id;

		public function __construct() {

			add_action( 'woocommerce_order_status_completed', array($this, 'woo_complete' ), 10, 1 );
		}


		public function woo_complete( $order_id ) {

			$this->user_id = $this->get_user_id($order_id);

			$courses = $this->get_purchased_course_list( $order_id );
			if (!empty($courses) && is_array($courses) && count($courses) > 0) {

				foreach ($courses as $course) {

					$students_data = get_post_meta( $course, 'attest_course_students', false );
					$student_to_enroll = ( isset($students_data[0]['to_enroll']) ? $students_data[0]['to_enroll'] : false );
					$student_to_enroll_number = ( isset($students_data[0]['to_enroll_number']) ? (int) $students_data[0]['to_enroll_number'] : false );


					$meta = get_post_meta($course, 'attest_enrolled_students', false);
          			if (false != $meta && is_array($meta[0]) && count($meta) > 0) {

						$existing = $meta[0];
						if ($student_to_enroll == 'auto') {

							array_push($existing, $this->user_id);
						} elseif ($student_to_enroll == 'define') {

							if ($student_to_enroll_number > count($existing)) {

								array_push($existing, $this->user_id);
							}
						}

						$student_list = array_values( array_unique( $existing ) );
					} else {

						$student_list = array(0 => $this->user_id);
					}
          			update_post_meta( $course, 'attest_enrolled_students', $student_list );

					$student_date_array = array();
					foreach ($student_list as $student) {
						$student_date_array[$student] = current_time('mysql');
					}
					update_post_meta( $course, 'attest_student_dates', $student_date_array );
				}

				$this->update_students_table($courses, $order_id);
			}
		}


		public function update_students_table($courses, $order_id) {

			global $wpdb;

			$table = $wpdb->prefix . self::$plugin_table;

			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT course_id FROM {$table} WHERE user_id = %d",
					$this->user_id
				));

			if (!empty($existing)) {

				$data = array(
					'course_id' => maybe_serialize(array_merge(maybe_unserialize($existing), $courses)),
				);
				$where = array(
					'user_id' => $this->user_id,
				);

				$format = array('%s');
				$where_format = array('%d');

				$wpdb->update( $table, $data, $where, $format, $where_format );
			} else {

				$order = wc_get_order( $order_id );
				$order_data = $order->get_data();
				$name = $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'];
				$email = $order_data['billing']['email'];
				$city = $order_data['billing']['city'];
				$country = $order_data['billing']['country'];

				$data = array(
					'user_id'   => $this->user_id,
					'name'      => $name,
					'email'     => $email,
					'course_id' => maybe_serialize($courses),
					'region'    => $country,
					'city'      => $city,
					'date'      => current_time('mysql'),
				);
				$format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');

				$wpdb->insert($table, $data, $format);
			}
		}


		public function get_purchased_course_list( $order_id ) {

			$order = wc_get_order( $order_id );
			$items = $order->get_items();

			$course_products_purchased = array();
			foreach ( $items as $item ) {

			    $product = wc_get_product( $item['product_id'] );
				$product_ID = $product->get_id();

				$meta = get_post_meta($product_ID, 'attest_course_related_to_product', true);
				if (!empty($meta)) {
					$course_products_purchased[] = $meta;
				}
			}

			return $course_products_purchased;
		}


		public function get_user_id($order_id) {

			$order = wc_get_order( $order_id );
			$user_id = $order->get_user_id();

			return $user_id;
		}
	}
}
