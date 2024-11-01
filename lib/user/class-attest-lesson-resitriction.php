<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_LESSON_RESTRICTION' ) ) {

	class ATTEST_LMS_LESSON_RESTRICTION {


    public function __construct() {

			$this->lesson_restriction_for_users();
    }


		public function lesson_restriction_for_users() {

			$existing_students = array();

			$post_id = get_the_ID();
			$post_type = get_post_type($post_id);

			$login_post_id = get_option('attest_template_login');
			$login_permalink = get_permalink($login_post_id);

			if ($post_type == 'attest_lesson') {

				$course_id = intval(get_post_meta($post_id, 'attest_course_related_to_lesson', true));
				$meta = get_post_meta($course_id, 'attest_enrolled_students', false);
				if (false != $meta && is_array($meta[0]) && count($meta) > 0) {

					$existing_students = $meta[0];
				}

				$teasers = $this->teasers($course_id);

				if (!in_array($post_id, $teasers)) {

					if (!is_user_logged_in()) {

						wp_safe_redirect($login_permalink, 302);
						exit();

					} elseif(is_user_logged_in()) {

						$user_id = get_current_user_id();
						$roles = get_userdata($user_id)->roles;

						if(!current_user_can('administrator') && !in_array('attest_tutor', $roles) && !in_array('attest_student', $roles)) {

							wp_safe_redirect($login_permalink, 302);
							exit();

						} elseif (in_array('attest_student', $roles) && !in_array($user_id, $existing_students)) {

							wp_safe_redirect($login_permalink, 302);
							exit();

						}

					}

				}
			}
		}


		public function teasers($current_post_ID) {

			$teaser = array();
			$functions = new ATTEST_LMS_COURSE_FUNCTIONS();
			$curriculum = $functions->get_course_curriculum($current_post_ID);
			$sections = $curriculum['data'];
			foreach ($sections as $key => $section) {
				unset($section['title']);
				foreach($section[0] as $count => $lesson) {
					if (isset($lesson['lesson_teaser']) && $lesson['lesson_teaser'] == '1') {
						$teaser[] = $lesson['lesson_id'];
					}
				}
			}

			return $teaser;
		}
  }
}
