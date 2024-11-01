<?php
/**
 * Continue course emails sending and formatting
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_CONTINUE_COURSE_EMAIL' ) ) {

	final class ATTEST_LMS_CONTINUE_COURSE_EMAIL {


		protected $title;
		protected $permission;
		protected $subject;
		protected $body;
		protected $time;
		protected $inactivity_time;


    public function __construct() {

			$this->time = intval(get_option('attest_email_continue_course_time'));
			$this->permission = get_option('attest_email_continue_course_permission');
			$this->subject = get_option('attest_email_continue_course_subject');
			$this->body = get_option('attest_email_continue_course_body');
			$this->inactivity_time = get_option('attest_email_continue_course_time');

			if (!empty($this->time) && $this->permission == '1') {
				add_action('add_option_attest_email_continue_course_time', array($this, 'continue_course_cron'), 10, 2);
			} else {
				wp_unschedule_event( current_time('timestamp'), 'attest_continue_course_cron' );
			}

			add_action( 'attest_continue_course_cron', array( $this, 'do_cron_job_function' ) );
    }


		public function continue_course_cron( $option_name, $option_value ) {

			if ( class_exists( 'ATTEST_LMS_CRON' ) ) {

				$cron = new ATTEST_LMS_CRON();
				$schedule = $cron->schedule_task(
							array(
							'timestamp' => current_time('timestamp'),
							'recurrence' => 'daily',
							'hook' => 'attest_continue_course_cron'
						) );
			}
		}


		public function do_cron_job_function() {

			//Get all students
			$users_args = array(
				//'role' => 'attest_student',
				'number' => -1
    	);
    	$users = get_users($users_args);

			//Get associated incomplete course through user_meta
			if ($users && count($users) > 0) {
				foreach ($users as $user) {

					$meta = get_user_meta($user->ID, 'attest_enrolled_courses', false);
					if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

						$courses = $meta[0];
						foreach($courses as $course) {
							if ($course['date'] != 0) {
								$timestamp = $course['date'];
								$course = $course['course'];

								$current_time = time();

								$a = new DateTime($timestamp);
								$b = new DateTime($current_time);
								$interval = $a->diff($b);

								$difference = $interval->format("%H");
								if ($difference >= ($inactivity_time * 24)) {

									$student = $user->ID;

									$this->title = get_the_title($course);

									$email = $this->get_email($student);

									$title = $this->alter_text($this->subject, $student);
									$description = $this->alter_text($this->body, $student);

									$sent = $this->send_email($email, $title, $description);
								}
							}
						}
					}
				}
			}
		}

		public function send_email($email, $title, $description) {

			$sent = wp_mail( $email, $title, $description);
      return $sent;
    }


    public function get_email($student_id) {

			$email = get_the_author_meta( 'user_email', $student_id);
      return $email;
    }


		public function alter_text($text, $user_id) {

			$user_data = get_userdata( $user_id );

			$text = str_replace( '{first_name}', $user_data->first_name, $text);
			$text = str_replace( '{last_name}', $user_data->last_name, $text);
			$text = str_replace( '{course_title}', $this->title, $text);

			return $text;
		}
  }
}
