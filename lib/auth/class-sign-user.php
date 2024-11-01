<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_STUDENT_SIGN' ) ) {

	class ATTEST_LMS_STUDENT_SIGN {


		public $courses_permalink;
		public $login_permalink;

    public function __construct() {

			$courses_post_id = get_option('attest_template_courses');
			$this->courses_permalink = get_permalink($courses_post_id);

			$login_post_id = get_option('attest_template_login');
			$this->login_permalink = get_permalink($login_post_id);

      //$this->hide_admin_bar_students();
			$this->sign_in();
			add_action( 'wp_logout', array($this, 'auto_redirect_after_logout'));
    }


		public function auto_redirect_after_logout(){

			wp_safe_redirect($this->login_permalink);
			exit();
		}


    public function hide_admin_bar_students() {
      if (is_user_logged_in()) {

        $user = wp_get_current_user();
        $role = $user->role;

        if ($role = 'attest_student') {
          show_admin_bar(false);
        }
      }
    }


		public function sign_in() {

			if (isset($_POST['attest_submit_sign'])) {

				$email = (isset($_POST['attest_email1_sign']) ? sanitize_email($_POST['attest_email1_sign']) : false);
				$password = (isset($_POST['attest_password_sign']) ? sanitize_text_field($_POST['attest_password_sign']) : false);
        $remember = (isset($_POST['attest_remember_sign']) ? intval(sanitize_text_field($_POST['attest_remember_sign'])) : false);

				if ($email && is_email($email) && $password) {

          $auth_remember = (($remember == 1) ? true : false);
          $creds = array(
            'user_login' => $email,
            'user_password' => $password,
            'remember' => $auth_remember,
          );

          $user = wp_signon( $creds, false );

          if (! is_wp_error($user)) {

            wp_set_current_user($user->ID, $email);
            wp_set_auth_cookie( $user->ID, $auth_remember, is_ssl() );
            do_action( 'wp_login', $user->user_login, $user );

            wp_safe_redirect($this->courses_permalink);
            exit;

          } else {

            global $Attest_LMS_Login_Error;
            $Attest_LMS_Login_Error = true;
          }
				}
      }
		}
  }
}
