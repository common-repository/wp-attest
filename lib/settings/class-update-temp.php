<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create templates upon activation
 */
if ( ! class_exists( 'ATTEST_LMS_UPDATE_PAGES' ) ) {

	class ATTEST_LMS_UPDATE_PAGES {


    protected $author;
    protected $status;


    public function __construct() {

      $this->author = get_current_user_id();
      $this->status = 'publish';

      $this->handle_post();
    }


		public function handle_post() {

				if ( !isset( $_POST['attest_template_settings_nonce'] ) || ! wp_verify_nonce( $_POST['attest_template_settings_nonce'], ATTEST_LMS_FILE ) ) return;

				if (isset( $_POST['attest_template_submit'] )) {

					$courses = ( isset($_POST['attest_courses_template']) ? sanitize_text_field($_POST['attest_courses_template']) : false );
					$register = ( isset($_POST['attest_register_template']) ? sanitize_text_field($_POST['attest_register_template']) : false );
					$login = ( isset($_POST['attest_login_template']) ? sanitize_text_field($_POST['attest_login_template']) : false );
					$congrats = ( isset($_POST['attest_congrats_template']) ? sanitize_text_field($_POST['attest_congrats_template']) : false );
					$my_account = ( isset($_POST['attest_my_account_template']) ? sanitize_text_field($_POST['attest_my_account_template']) : false );

					if ($courses) {
						$this->create_page('[wp_attest_courses]', $courses, 'courses');
					}
					if ($register) {
						$this->create_page('[wp_attest_student_register]', $register, 'register');
					}
					if ($login) {
						$this->create_page('[wp_attest_student_sign]', $login, 'login');
					}
					if ($congrats) {
						$this->create_page('[wp_attest_congrats]', $congrats, 'congrats');
					}
					if ($my_account) {
						$this->create_page('[wp_attest_account]', $my_account, 'my_account');
					}

					$number = ( isset($_POST['attest_number_courses']) ? sanitize_text_field($_POST['attest_number_courses']) : 1 );
					update_option('attest_number_courses', $number);

					add_action('admin_notices', array($this, 'success_notice'));
				}
		}


		public function success_notice() { ?>

			<div class="notice notice-success is-dismissible">
				<p><strong><?php _e( 'Settings saved.', 'attest' ); ?></strong></p>
		 	</div>
		<?php
		}


    public function create_page($content, $key, $type) {

      $page_info = array(
				'ID'           => $key,
        'post_content' => $content,
        'post_type'    => 'page',
        'post_author'  => $this->author,
        'post_status'  => $this->status
      );

      $update_post_id = wp_update_post($page_info);
      if (!is_wp_error($update_post_id)) {

        $this->save_for_permalink($key, $type);
      }
    }


    private function save_for_permalink($post_id, $type) {

      update_option('attest_template_' . $type, $post_id);
    }
  }
}
