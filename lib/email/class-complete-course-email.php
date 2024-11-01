<?php
/**
 * Announcement emails sending and formatting
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_COMPLETE_COURSE_EMAIL' ) ) {

	final class ATTEST_LMS_COMPLETE_COURSE_EMAIL {


		public $student;
		public $course;
		protected $title;
		protected $permission;
		protected $subject;
		protected $body;


    public function __construct($student, $course) {

			$this->student = $student;
			$this->course = $course;
			$this->title = get_the_title($course);
			$this->permission = get_option('attest_email_completed_course_permission');
			$this->subject = get_option('attest_email_completed_course_subject');
			$this->body = get_option('attest_email_completed_course_body');

			if ($this->permission != '1') {
				return;
			}

			$this->execute($this->student, $this->course);
    }


    public function execute($student, $course) {

			$sent = false;

      if ($student) {
        $email = $this->get_email($student);

				$title = $this->alter_text($this->subject, $student);
				$description = $this->alter_text($this->body, $student);

        $sent = $this->send_email($email, $title, $description);
      }

			return $sent;
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
