<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_STUDENT_SHORTCODE' ) ) {

	class ATTEST_LMS_STUDENT_SHORTCODE {


		public $redirect_url;
		public $register_url;
		public $login_url;


		public function __construct() {

			$courses_post_id = get_option('attest_template_courses');
			$courses_permalink = get_permalink($courses_post_id);
			$this->redirect_url = $courses_permalink;

			$register_post_id = get_option('attest_template_register');
			$register_permalink = get_permalink($register_post_id);
			$this->register_url = $register_permalink;

			$login_post_id = get_option('attest_template_login');
			$login_permalink = get_permalink($login_post_id);
			$this->login_url = $login_permalink;

			add_shortcode( 'wp_attest_student_sign', array( $this, 'sign_cb' ) );
			add_shortcode( 'wp_attest_student_register', array( $this, 'register_cb' ) );
		}


		public function sign_cb($atts) {

			$data = shortcode_atts( array(
										'email_text'       => __('Email', 'attest'),
										'password_text'    => __('Password', 'attest'),
										'remember_text'    => __('Remember Me', 'attest'),
										'submit_text'      => __('Login', 'attest'),
										'login_error_text' => __('Invalid credentials', 'attest'),
										'redirect_url'     => $this->redirect_url,
									), $atts );

			return $this->sign_html($data);
		}


		public function register_cb($atts) {

			$data = shortcode_atts( array(
										'first_name_text'       => __('First Name', 'attest'),
										'last_name_text'        => __('Last Name', 'attest'),
										'email_text'            => __('Email', 'attest'),
										'password_text'         => __('Password', 'attest'),
										'confirm_password_text' => __('Confirm Password', 'attest'),
										'password_match_text'   => __('Passwords doesn\'t match. Please try again.', 'attest'),
										'submit_text'           => __('Register', 'attest'),
										'register_error_text'   => sprintf( __('There is already an account using that email. Try another email or %s', 'attest'), '<a href="' . esc_url_raw( wp_lostpassword_url() ) . '" target="_blank">' . __('Recover your account', 'attest') . '</a>' ),
										'redirect_url'          => $this->redirect_url,
									), $atts );

			return $this->register_html($data);
		}


		/**
		 * Shortcode Display
		 */
		public function sign_html($data) {

			global $Attest_LMS_Login_Error;

			$body = '<div class="container">
				<div class="row justify-content-md-center">
					<div class="col-md-8">';

					if ($Attest_LMS_Login_Error) {
						$body .= '<div id="attest_login_error" class="alert alert-danger" role="alert">' . esc_attr($data['login_error_text']) . '</div>';
					}

						$body .=
						'<form method="POST" action="">
  						<div class="attest-form-group">
    						<input type="email" class="attest-text-input" id="attest_email1_sign" name="attest_email1_sign" placeholder="' . esc_attr($data['email_text']) . '" required>
  						</div>
  						<div class="attest-form-group">
    						<input type="password" class="attest-text-input" id="attest_password_sign" name="attest_password_sign" placeholder="' . esc_attr($data['password_text']) . '" required>
  						</div>
  						<div class="form-check attest-form-group">
    						<input type="checkbox" class="form-check-input attest-check" id="attest_remember_sign" name="attest_remember_sign" value="1">
    						<label class="form-check-label" for="attest_remember_sign">' . esc_attr($data['remember_text']) . '</label>
  						</div>
							<div class="attest-form-group">
  							<input type="submit" class="attest-button-block" name="attest_submit_sign" value="' . esc_attr($data['submit_text']) . '" />
							</div>
						</form>';

						$body .=
						'<div class="attest-form-group">
							<small>
							<a href="' . esc_url_raw( wp_lostpassword_url() ) . '" target="_blank">' . __( 'Forget Password?', 'attest' ) . '</a>
							<br />' .
							__('Don\'t have an account', 'attest') . ' &nbsp; <a href="' . esc_url_raw($this->register_url) . '">' . __('Register', 'attest') . '</a>
							</small>
						</div>';

						$body .=
					'</div>
				</div>
			</div>';

			return $this->redirect($body, $data['redirect_url']);
		}


		/**
		 * Shortcode Display
		 */
		public function register_html($data) {

			global $Attest_LMS_Register_Error;

			$body =
			'<div class="container">
				<div class="row justify-content-md-center">
					<div class="col-md-8">';

						if (!empty($Attest_LMS_Register_Error) && is_bool($Attest_LMS_Register_Error) === true) {
							$body .= '<div id="attest_login_error" class="alert alert-danger" role="alert">' . wp_kses_post($data['register_error_text']) . '</div>';
						} elseif (!empty($Attest_LMS_Register_Error) && is_bool($Attest_LMS_Register_Error) === false) {
							$body .= '<div id="attest_login_error" class="alert alert-danger" role="alert">' . wp_kses_post($Attest_LMS_Register_Error) . '</div>';
						}

						$body .=
						'<form method="POST" action="">
  						<div class="attest-form-group">
    						<input type="text" class="attest-text-input" id="attest_first_name_register" name="attest_first_name_register" placeholder="' . esc_attr($data['first_name_text']) . '" required>
  						</div>
  						<div class="attest-form-group">
    						<input type="text" class="attest-text-input" id="attest_last_name_register" name="attest_last_name_register" placeholder="' . esc_attr($data['last_name_text']) . '" required>
  						</div>
  						<div class="attest-form-group">
    						<input type="email" class="attest-text-input" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" title="Please enter a valid email address" id="attest_email1_register" name="attest_email1_register" placeholder="' . esc_attr($data['email_text']) . '" required>
  						</div>
  						<div class="attest-form-group">
    						<input type="password" class="attest-text-input" id="attest_password_register" name="attest_password_register" placeholder="' . esc_attr($data['password_text']) . '" required>
  						</div>
  						<div class="attest-form-group">
								<input type="password" class="attest-text-input" id="attest_password_confirm_register" name="attest_password_confirm_register" placeholder="' . esc_attr($data['confirm_password_text']) . '" required>
  						</div>
  						<div class="attest-form-group">
								<div id="attest_password_match_text_register" class="alert alert-warning" role="alert">' . esc_attr($data['password_match_text']) . '</div>
							</div>
							<div id="attest_g_recaptcha"></div>
  						<div class="attest-form-group">
								<input type="submit" class="attest-button-block" id="attest_submit_register" name="attest_submit_register" value="' . esc_attr($data['submit_text']) . '" />
							</div>
						</form>';
						$body .=
						'<div class="attest-form-group">
							<small>' .
							__('Already have an Account', 'attest') . ' &nbsp; <a href="' . esc_url_raw($this->login_url) . '">' . __('Login', 'attest') . '</a>
							</small>
						</div>
						<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>';
					$body .=
					'</div>
				</div>
			</div>';

			return $this->redirect($body, $data['redirect_url']);
		}


		private function redirect($body, $redirect_url) {

			$script =
			'<script type="text/javascript">
				function redirect() {
					window.location = \'' . esc_url_raw($redirect_url) . '\';
				}
				redirect();
			</script>';

			if (is_user_logged_in()) {
				return $script;
			} else {
				return $body;
			}
		}
	}
} ?>
