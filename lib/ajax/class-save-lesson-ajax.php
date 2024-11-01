<?php
/**
 * Doing AJAX the WordPress way.
 * Use this class in admin or user side
 */
if ( ! defined( 'ABSPATH' ) ) exit;

//AJAX helper class
if ( ! class_exists( 'ATTEST_LMS_SAVE_LESSON_AJAX' ) ) {

	final class ATTEST_LMS_SAVE_LESSON_AJAX {


		// Add basic actions
		public function __construct() {

			add_action( 'wp_ajax_attest_save_lesson', array( $this, 'attest_save_lesson' ) );
			add_action( 'wp_ajax_nopriv_attest_save_lesson', array( $this, 'attest_save_lesson' ) );
		}


		//The data processor
		public function attest_save_lesson() {

			$course_id = sanitize_text_field($_POST['course_id']);
			$data = $this->sanitize($_POST['data']);
      $nonce = sanitize_text_field($_POST['nonce']);

			if ( !isset( $nonce ) || !wp_verify_nonce( $nonce, ATTEST_LMS_FILE ) ) {

        $response = array( 'alert' => __('Are you a hacker?', 'attest' ));
      } else {

				update_post_meta( $course_id, 'attest_curriculum', $data );
				$response = array( 'alert' => 1 );
      }

      echo json_encode( $response );
      wp_die();
		}


		private function sanitize($input) {

			$data = array();
			$k = 0;
			if (is_array($input)) {

				foreach($input as $section) {
					$data[$k]['title'] = (isset($section[0]['title']) ? sanitize_text_field($section[0]['title']) : false);
					$lesson_sanitized = array();
					$i = 0;

					if (isset($section[1]['item'])) {
						foreach ($section[1]['item'] as $lesson) {
							if(isset($lesson[0]['lesson_id'])) {
								$lesson_sanitized[$i]['lesson_id'] = sanitize_text_field($lesson[0]['lesson_id']);
							}
							if(isset($lesson[1]['lesson_teaser']) && $lesson[1]['lesson_teaser'] != '') {
								$lesson_sanitized[$i]['lesson_teaser'] = (isset($lesson[1]['lesson_teaser']) && $lesson[1]['lesson_teaser'] == '1' ? '1' : '0');
							}
							$i++;
						}
					}
					array_push($data[$k], array_values($lesson_sanitized));
					$k++;
				}
			}

			return $data;
		}
	}
} ?>
