<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_STUDENT_REGISTER' ) ) {

	class ATTEST_LMS_STUDENT_REGISTER {


		public $courses_permalink;
		public $secret_key;
		public static $student_table = 'attest_students';


    public function __construct() {

			$post_id = get_option('attest_template_courses');
			$this->courses_permalink = get_permalink($post_id);

			$account_id = get_option('attest_template_my_account');
			$this->account_permalink = get_permalink($account_id);

			$this->secret_key = get_option('attest_secret_key_recaptcha');

			$this->register();
    }


		public function register() {

			global $Attest_LMS_Register_Error;
			$Attest_LMS_Register_Error = false;

			if (isset($_POST['attest_submit_register'])) {

				$first_name = (isset($_POST['attest_first_name_register']) ? sanitize_text_field($_POST['attest_first_name_register']) : false);
				$last_name = (isset($_POST['attest_last_name_register']) ? sanitize_text_field($_POST['attest_last_name_register']) : false);
				$email = (isset($_POST['attest_email1_register']) ? sanitize_email($_POST['attest_email1_register']) : false);
				$password = (isset($_POST['attest_password_register']) ? sanitize_text_field($_POST['attest_password_register']) : false);
				$confirm_password = (isset($_POST['attest_password_confirm_register']) ? sanitize_text_field($_POST['attest_password_confirm_register']) : false);

				$course_id = (isset($_GET['course_ref']) ? intval(sanitize_text_field($_GET['course_ref'])) : false);

				$captcha = (isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : false);

				if($this->secret_key && $captcha) {

					$ip = $_SERVER['REMOTE_ADDR'];
					$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->secret_key) .  '&response=' . urlencode($captcha);
					$response = wp_remote_get($url);

					if (!is_wp_error($response)) {
						$response_keys = json_decode($response['body'],true);
					}

					if (!isset($response_keys['success']) || $response_keys['success'] != true) {
						$Attest_LMS_Register_Error = __('You are a spammer!', 'attest');
						return;
					}
				} elseif ($this->secret_key && !$captcha) {

					$Attest_LMS_Register_Error = __('Invalid submission', 'attest');
					return;
				}

				if ($first_name && $last_name && $email && $password && $confirm_password) {
					if ( is_email($email) && ($password == $confirm_password)) {

						$username = $this->generate_username($email);

						$user_data = array(
							'first_name' => $first_name,
							'last_name'  => $last_name,
  						'user_login' => $username,
  						'user_pass'  => $password,
  						'user_email' => $email,
  						'role'       => 'attest_student'
						);
						$user_id = wp_insert_user( $user_data );
						if (! is_wp_error($user_id)) {

							$user_data = get_userdata( $user_id );
							$user = get_user_by('id', $user_id);
							wp_set_current_user( $user_id, $email );
							wp_set_auth_cookie( $user_id, false, is_ssl() );
							do_action( 'wp_login', $user_data->user_login, $user );

							if (!empty($course_id)) {

								$this->enroll_to_course($course_id, $user_id);

								wp_safe_redirect($this->account_permalink);
								exit;

							} else {
								wp_safe_redirect($this->courses_permalink);
								exit;
							}

          	} else {

							$Attest_LMS_Register_Error = true;
          	}
					}
				} else {

					$Attest_LMS_Register_Error = __('All fields are mandatory', 'attest');
				}
      }
		}


		public function generate_username($email) {

			$exploded = explode('@', $email);
			return $exploded[0];
		}


		public function enroll_to_course($course_id, $user_id) {

			$product_ID = get_post_meta( $course_id, 'attest_product_related_to_course', true );

			$message = $enrolled = false;

			$students_data = get_post_meta( $course_id, 'attest_course_students', false );
			$student_to_enroll = ( isset($students_data[0]['to_enroll']) ? $students_data[0]['to_enroll'] : false );
			$student_to_enroll_number = ( isset($students_data[0]['to_enroll_number']) ? $students_data[0]['to_enroll_number'] : false );
			$student_to_excess_error = ( isset($students_data[0]['excess_error']) ? $students_data[0]['excess_error'] : false );

      if ($student_to_enroll) {

          $meta = get_post_meta($course_id, 'attest_enrolled_students', false);
          if (false != $meta && is_array($meta[0]) && count($meta) > 0) {

						$existing = $meta[0];
						if ($student_to_enroll == 'auto') {

							array_push($existing, $user_id);
							$enrolled = true;

						} elseif ($student_to_enroll == 'define') {

							if ($student_to_enroll_number > count($existing)) {

								array_push($existing, $user_id);
								$enrolled = true;

							} else {

								$message = $student_to_excess_error;
								$enrolled = false;

							}
						}

						$student_list = array_values( array_unique( $existing ) );
          } else {

						$student_list = array(0 => $user_id);
						$enrolled = true;
          }

					if (empty($product_ID)) {

						update_post_meta( $course_id, 'attest_enrolled_students', $student_list );

						//Update dates for enrolled students
						$dates_array = get_post_meta( $course_id, 'attest_student_dates', false );
						if (false != $dates_array && is_array($dates_array[0]) && count($dates_array[0]) > 0) {

							$student_date_array = $dates_array[0];
							$student_date_array[$user_id] = current_time('mysql');
						} else {
							$student_date_array = array($user_id => current_time('mysql'));
						}

						update_post_meta( $course_id, 'attest_student_dates', $student_date_array );

						$this->update_students_table($course_id, $user_id);
					}
      }
    }


		public function update_students_table($course, $student) {

			global $wpdb;

			$table = $wpdb->prefix . self::$student_table;

			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT course_id FROM {$table} WHERE user_id = %d",
					$student
				));

			if (!empty($existing)) {

				$data = array(
					'course_id' => maybe_serialize(array_merge(maybe_unserialize($existing), $course)),
				);
				$where = array(
					'user_id' => $student,
				);

				$format = array('%s');
				$where_format = array('%d');

				$wpdb->update( $table, $data, $where, $format, $where_format );
			} else {

				$user_data = get_userdata($student);
				$name = $user_data->first_name . ' ' . $user_data->last_name;
				$email = $user_data->user_email;
				$city = '';
				$country = '';

				$data = array(
					'user_id'   => $student,
					'name'      => $name,
					'email'     => $email,
					'course_id' => maybe_serialize($course),
					'region'    => $country,
					'city'      => $city,
					'date'      => current_time('mysql'),
				);
				$format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');

				$wpdb->insert($table, $data, $format);
			}
		}
  }
}
