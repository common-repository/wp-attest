<?php
/**
 * New course emails sending and formatting
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_NEW_COURSE_EMAIL' ) ) {

	final class ATTEST_LMS_NEW_COURSE_EMAIL {


    protected $title;
		protected $permission;
		protected $subject;
		protected $body;
		protected $target_course;


    public function __construct() {

			$this->permission = get_option('attest_email_new_course_permission');
			$this->target_course = get_option('attest_target_new_course');
			$this->subject = get_option('attest_email_new_course_subject');
			$this->body = get_option('attest_email_new_course_body');
			$this->title = ($this->target_course == 'all' ? '' : get_the_title($this->target_course) );

			if ($this->target_course != '' && $this->permission == '1') {
				add_action( 'save_post', array( $this, 'save_post_email' ), 10, 3 );
			}
    }


    public function save_post_email($post_ID, $post, $update){

			if ($post->post_type == 'attest_course' && $update == false) {
				$this->execute();
			}
    }

    public function execute() {

      $students = $this->get_students($this->target_course);
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

			if ($course_id == 'all') {

				return $this->get_all_students();
			} else {

				$meta = get_post_meta($course_id, 'attest_enrolled_students', false);
				if (false != $meta && count($meta[0]) > 0) {

					return $meta[0];
				} else {

					return false;
				}
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


		public function get_all_students() {

			$output = array();

			$args = array(
				'role__in' => 'attest_teacher',
				'exclude'  => array(get_current_user_id()),
				'number'	=> -1,
			);

			$user_query = new WP_User_Query( $args );
			$users = $user_query->get_results();

			foreach ($users as $user) {
				$output[] = $user->ID;
			}

			return $output;
		}
  }
}
