<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_CONGRATS_SHORTCODE' ) ) {

	class ATTEST_LMS_CONGRATS_SHORTCODE {

		protected $query;
		protected $number_courses;
		protected $courses_permalink;

		public function __construct() {

			$account_post_id = get_option('attest_template_my_account');
			$this->account_permalink = ($account_post_id ? get_permalink($account_post_id) : false);

			add_shortcode( 'wp_attest_congrats', array( $this, 'courses_cb' ) );
		}


		public function courses_cb($atts) {

			$data = shortcode_atts( array(
								'text' => __('Congratulations for finishing', 'attest'),
							), $atts );

			return $this->congrats_html($data);
		}


		/**
		 * Shortcode Display
		 */
		public function congrats_html($data) {

			$course = (isset($_GET['course']) ? $_GET['course'] : false);
			$student = get_current_user_id();
			$checked = $this->course_check($course, $student);

			$body = '<div class="container text-center">
				<div class="row">
					<div class="col-md-12">
						<h2 class="mb-3">🎉 ' . $data['text'] . '</h2>
						<a href="' . esc_url_raw(get_permalink($course)) . '">' . esc_attr(get_the_title($course)) . '</a>';

						if($this->account_permalink) {
							$body .= '<p class="mt-5"><a class="attest-button" href="' . esc_url_raw($this->account_permalink) . '">' . __('GO TO MY COURSES', 'attest') . '</a></p>';
						}

        	$body .= '</div>
				</div>';

				if ($checked) {

					$this->complete_course($student, $course);
					$email = new ATTEST_LMS_COMPLETE_COURSE_EMAIL($student, $course);
					return $body;
				} else {
					return false;
				}
		}


		public function complete_course($student, $course) {

			//Update student data (used for email)
			$meta = get_user_meta($student, 'attest_enrolled_courses', false);
			if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

				$courses = $meta[0];
				$search = array_search($course, array_column($courses, 'course'));
				if ($search !== false) {

					unset($courses[$search]);
					array_push($courses, array(
						'course' => $course,
						'date' => current_time('mysql'),
					));
				}
			}
			$courses = array_map("unserialize", array_unique(array_map("serialize", $courses)));
			update_user_meta($student, 'attest_enrolled_courses', $courses);

			//Update completed data(use at archives)
			$completed_students = get_post_meta($course, 'attest_completed_students', false);
		  if (false != $completed_students && is_array($completed_students[0]) && count($completed_students[0]) > 0) {

				$student_list = $completed_students[0];
				array_push($student_list, $student);

				$student_list = array_values( array_unique( $student_list ) );
		  } else {
				$student_list = array(0 => $student);
			}
			update_post_meta( $course, 'attest_completed_students', $student_list );

			//Update dates for enrolled students
			$dates_array = get_post_meta( $course, 'attest_student_dates', false );
			if (false != $dates_array && is_array($dates_array[0]) && count($dates_array[0]) > 0) {

				$dates_list = $dates_array[0];
				$dates_list[$student] = current_time('mysql');
				update_post_meta( $course, 'attest_student_dates', $dates_list );
			}
		}


		public function course_check($course, $student) {

			$output = false;

      $meta = get_post_meta($course, 'attest_enrolled_students', false);
      if (false != $meta && count($meta[0]) > 0) {

				$existing = $meta[0];

				if (in_array($student, $existing)) {

					$output = true;
				}
      }

			return $output;
    }
  }
}
