<?php
/**
 * Announcement emails sending and formatting
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_UPDATE_COURSE_EMAIL' ) ) {

	final class ATTEST_LMS_UPDATE_COURSE_EMAIL {


    protected $title;
		protected $subject;
		protected $body;
		protected $send_updated_course;


    public function __construct() {

			$this->send_updated_course = intval(get_option('attest_ok_updated_course'));
			$this->subject = get_option('attest_email_updated_course_subject');
			$this->body = get_option('attest_email_updated_course_body');

			if ($this->send_updated_course == 1) {
				add_action( 'save_post', array( $this, 'save_post_email' ), 10, 3 );
			}
    }


    public function save_post_email($post_ID, $post, $update){

			if ($post->post_type == 'attest_course' && $update == true && $post->post_status != 'trash') {
				$this->title = $post->post_title;
				$this->execute($post->ID);
			}
    }


    public function execute($course_id) {

			$sent = false;

      $students = $this->get_students($course_id);
      if ($students) {
        foreach($students as $student) {
          $email = $this->get_email($student);

					$title = $this->alter_text($this->subject, $student);
					$description = $this->alter_text($this->body, $student);

          $sent = $this->send_email($email, $title, $description);
        }
      }

			return $sent;
    }


    public function send_email($email, $title, $description) {

			$sent = wp_mail( $email, $title, $description);
      return $sent;
    }


    public function get_students($course_id) {

			$meta = get_post_meta($course_id, 'attest_enrolled_students', false);
			if (false != $meta && count($meta[0]) > 0) {

				return $meta[0];
			} else {

				return false;
			}
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
