<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Main plugin object to define the plugin
if ( ! class_exists( 'ATTEST_LMS_BUILD' ) ) {

	final class ATTEST_LMS_BUILD {


		protected $data;


		public function installation() {

			if (class_exists('ATTEST_LMS_INSTALL')) {

				$install = new ATTEST_LMS_INSTALL();
				$install->textDomin = 'attest';
				$install->phpVerAllowed = '5.4';
				$install->pluginPageLinks = array(
																array(
																	'slug' => admin_url('edit.php?post_type=attest_course&page=settings'),
																	'label' => __('Settings', 'attest'),
																),
															);
				$install->execute();
			}
		}


		public function install_data() {

			if ( class_exists( 'ATTEST_LMS_ROLES' ) ) new ATTEST_LMS_ROLES();

			if ( class_exists( 'ATTEST_LMS_CPT_COURSE' ) ) new ATTEST_LMS_CPT_COURSE();
			if ( class_exists( 'ATTEST_LMS_CPT_LESSON' ) ) new ATTEST_LMS_CPT_LESSON();
			flush_rewrite_rules();
		}


		public function db_install() {

			if ( class_exists( 'ATTEST_LMS_DB' ) ) {
				$db = new ATTEST_LMS_DB();
				$db->table = 'attest_announcements';
				$db->sql = "ID mediumint(9) NOT NULL AUTO_INCREMENT,
							title varchar(1028) NOT NULL,
							description text NOT NULL,
							related_course mediumint(9) NOT NULL,
							active smallint(1) NOT NULL,
							trigger_email smallint(1) NOT NULL,
							date datetime NOT NULL,
							UNIQUE KEY ID (ID)";
				$db->build();

				$db = new ATTEST_LMS_DB();
				$db->table = 'attest_students';
				$db->sql = "ID mediumint(9) NOT NULL AUTO_INCREMENT,
							user_id mediumint(9) NOT NULL,
							name varchar(256) NOT NULL,
							email varchar(256) NOT NULL,
							course_id varchar(1028) NOT NULL,
							region varchar(256) NOT NULL,
							city varchar(256) NOT NULL,
							date date NOT NULL,
							UNIQUE KEY ID (ID)";
				$db->build();
			}

			if (get_option('_attest_db_exist') == '0') {
				add_action( 'admin_notices', array( $this, 'db_error_msg' ) );
			}

			$options = array(
										array( 'attest_lms_lns', $this->data->lns() ),
										array( 'attest_number_courses', 10 ),
										array( 'attest_email_announcement_permission', '1' ),
										array( 'attest_email_new_course_permission', '1' ),
										array( 'attest_email_completed_course_permission', '1' ),
										array( 'attest_ok_updated_course', '1' ),
										array( 'attest_email_continue_course_permission', '1' ),
									);
						foreach ($options as $value) {
							update_option( $value[0], $value[1] );
						}
		}

		//Notice of DB
		public function db_error_msg() { ?>

			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'Database table Not installed correctly.', 'attest' ); ?></p>
		 	</div>
		<?php
		}


		//Redirect to getting started upon activation
		public function activation_redirect( $plugin ) {

			if( $plugin == ATTEST_LMS_FILE ) {
				wp_redirect( admin_url( 'edit.php?post_type=attest_course&page=attest' ) );
			}
		}


		//Tracking code
		public function attest_lms_freemium() {

			global $attest_lms_freemium;

      if ( ! isset( $attest_lms_freemium ) ) {
        // Include Freemius SDK.
        require_once ATTEST_LMS_PATH . 'vendor/freemius/start.php';

        $attest_lms_freemium = fs_dynamic_init(
					array(
            'id'             => '6177',
            'slug'           => 'wp-attest',
            'type'           => 'plugin',
            'public_key'     => 'pk_918699df233a3fc665f96deb5ea82',
            'is_premium'     => false,
            'has_addons'     => false,
            'has_paid_plans' => false,
            'menu'           => array(
                									'slug'    => 'edit.php?post_type=attest_course&page=attest',
                									'account' => false,
                									'support' => false,
            										),
        	) );
      }

      return $attest_lms_freemium;
  	}


		//Welcome text
		public function attest_lms_freemium_custom_connect_message_on_update($message, $user_first_name, $plugin_title,$user_login, $site_link,$freemius_link) {

			return sprintf(
				__( 'Hey %1$s' ) . ',<br>' .
				__( 'Never miss an important update! Opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking with %2$s.', 'wp-attest' ),
				$user_first_name,
				$freemius_link
		  );
		}


		//List of data
		protected function data() {

			$data = new ATTEST_LMS_DATA();
			return $data;
		}


		//CRON deactivation
		public function deactivate() {

			wp_clear_scheduled_hook( 'attest_continue_course_cron' );
			flush_rewrite_rules();
		}


		//Import and export
		public function import_export() {

			if ( class_exists( 'ATTEST_LMS_EXPORT' ) ) new ATTEST_LMS_EXPORT();
			if ( class_exists( 'ATTEST_LMS_IMPORT' ) ) new ATTEST_LMS_IMPORT();
		}


		//Send emails
		public function email_cb() {

			if ( class_exists( 'ATTEST_LMS_NEW_COURSE_EMAIL' ) ) new ATTEST_LMS_NEW_COURSE_EMAIL();
			if ( class_exists( 'ATTEST_LMS_UPDATE_COURSE_EMAIL' ) ) new ATTEST_LMS_UPDATE_COURSE_EMAIL();
			if ( class_exists( 'ATTEST_LMS_CONTINUE_COURSE_EMAIL' ) ) $email = new ATTEST_LMS_CONTINUE_COURSE_EMAIL();
		}


		//Add settings callback
		public function settings_cb() {

			if ( class_exists( 'ATTEST_LMS_UPDATE_PAGES' ) ) new ATTEST_LMS_UPDATE_PAGES();
			if ( class_exists( 'ATTEST_RE_CAPTCHA_SCRIPT' ) ) new ATTEST_RE_CAPTCHA_SCRIPT();
		}


		//Add templates to the flow
		public function templates() {

			$single_temp_course  = ATTEST_LMS_PATH . 'lib/templates/single-course.php';
			$archive_temp_course = ATTEST_LMS_PATH . 'lib/templates/archive-course.php';
			$single_temp_lesson  = ATTEST_LMS_PATH . 'lib/templates/single-lesson.php';
			if ( class_exists( 'ATTEST_LMS_TEMPLATES' ) ) new ATTEST_LMS_TEMPLATES($single_temp_course, $archive_temp_course, $single_temp_lesson);
		}


		//Lesson restriction
		public function lesson_Restriction() {

			if ( class_exists( 'ATTEST_LMS_LESSON_RESTRICTION' ) ) new ATTEST_LMS_LESSON_RESTRICTION();
		}


		//Add a sidebar
		public function archive_sidebar() {

			if ( class_exists( 'ATTEST_LMS_COURSES_SIDEBAR' ) ) new ATTEST_LMS_COURSES_SIDEBAR();
		}


		//Add student module
		public function auth() {

			if ( class_exists( 'ATTEST_LMS_STUDENT_REGISTER' ) ) new ATTEST_LMS_STUDENT_REGISTER();
			if ( class_exists( 'ATTEST_LMS_STUDENT_SIGN' ) ) new ATTEST_LMS_STUDENT_SIGN();
		}


		//Add custom post types
		public function cpt() {

			if ( class_exists( 'ATTEST_LMS_CPT_COURSE' ) ) new ATTEST_LMS_CPT_COURSE();
			if ( class_exists( 'ATTEST_LMS_CPT_LESSON' ) ) new ATTEST_LMS_CPT_LESSON();
		}


		//Add taxonomy
		public function taxonomy() {

			if ( class_exists( 'ATTEST_LMS_TAX_TOPIC' ) ) new ATTEST_LMS_TAX_TOPIC();
			if ( class_exists( 'ATTEST_LMS_TAX_DIFFICULTY' ) ) new ATTEST_LMS_TAX_DIFFICULTY();
			if ( class_exists( 'ATTEST_LMS_INSERT_DIFFICULTY' ) ) new ATTEST_LMS_INSERT_DIFFICULTY();
		}


		//Include scripts
		public function scripts() {

			if ( class_exists( 'ATTEST_LMS_SCRIPT' ) ) new ATTEST_LMS_SCRIPT();
		}


		//Include settings pages
		public function settings() {

			if ( class_exists( 'ATTEST_LMS_SETTINGS' ) ) new ATTEST_LMS_SETTINGS();
		}


		//Include metabox classes
		public function metabox() {

			if ( class_exists( 'ATTEST_LMS_INTRO_VIDEO_METABOX' ) ) new ATTEST_LMS_INTRO_VIDEO_METABOX();
			if ( class_exists( 'ATTEST_LMS_CURRICULUM_METABOX' ) ) new ATTEST_LMS_CURRICULUM_METABOX();
			if ( class_exists( 'ATTEST_LMS_DETAILS_METABOX' ) ) new ATTEST_LMS_DETAILS_METABOX();
			if ( class_exists( 'ATTEST_LMS_SETTINGS_METABOX' ) ) new ATTEST_LMS_SETTINGS_METABOX();
		}


		//Include shortcode classes
		public function shortcode() {

			if ( class_exists( 'ATTEST_LMS_STUDENT_SHORTCODE' ) ) new ATTEST_LMS_STUDENT_SHORTCODE();
			if ( class_exists( 'ATTEST_LMS_COURSES_SHORTCODE' ) ) new ATTEST_LMS_COURSES_SHORTCODE();
			if ( class_exists( 'ATTEST_LMS_CONGRATS_SHORTCODE' ) ) new ATTEST_LMS_CONGRATS_SHORTCODE();
		}


		public function ajax_cb() {

			if ( class_exists( 'ATTEST_LMS_NEW_LESSON_AJAX' ) ) new ATTEST_LMS_NEW_LESSON_AJAX();
			if ( class_exists( 'ATTEST_LMS_SAVE_LESSON_AJAX' ) ) new ATTEST_LMS_SAVE_LESSON_AJAX();
		}

		/**
		 * Hide admin bar
		 *
		 * @return Void
		 */
		 public function hide_Admin_bar() {

			 if ( class_exists( 'ATTEST_LMS_WOO_PRO_ADMIN_BAR' ) ) new ATTEST_LMS_WOO_PRO_ADMIN_BAR();
		 }


		/**
		 * Add to cart
		 *
		 * @return Void
		 */
		 public function add_to_cart() {

			 if ( class_exists( 'ATTEST_LMS_WOO_PRO_WOO_ADD_TO_CART' ) ) new ATTEST_LMS_WOO_PRO_WOO_ADD_TO_CART();
		 }


		/**
		 * Woocoomerce upon successful order
		 *
		 * @return Void
		 */
		public function woo_success() {

			if ( class_exists( 'ATTEST_LMS_WOO_PRO_WOO_SUCCESS' ) ) new ATTEST_LMS_WOO_PRO_WOO_SUCCESS();
			if ( class_exists( 'ATTEST_LMS_WOO_PRO_WOO_ACCOUNT' ) ) new ATTEST_LMS_WOO_PRO_WOO_ACCOUNT();
		}


		/**
		 * Woocoomerce add new product
		 *
		 * @return Void
		 */
		public function woo_new_product() {

			if ( class_exists( 'ATTEST_LMS_WOO_PRO_WOO_NEW_PRODUCT' ) ) new ATTEST_LMS_WOO_PRO_WOO_NEW_PRODUCT();
		}


		//Add functionality files
		public function functionality() {

			require_once ('src/class-attest-db.php');
			require_once ('src/class-attest-install.php');
			require_once ('src/class-attest-settings.php');

			require_once ('src/cpt/class-course-cpt.php');
			require_once ('src/cpt/class-lesson-cpt.php');

			require_once ('src/taxonomy/class-topic-taxonomy.php');
			require_once ('src/taxonomy/class-difficulty-taxonomy.php');
			require_once ('src/taxonomy/class-insert-difficulty.php');

			require_once ('src/metabox/class-video-metabox.php');
			require_once ('src/metabox/class-curriculum-metabox.php');
			require_once ('src/metabox/class-details-metabox.php');
			require_once ('src/metabox/class-settings-metabox.php');

			require_once ('src/shortcode/class-students-shortcode.php');
			require_once ('src/shortcode/class-courses-shortcode.php');
			require_once ('src/shortcode/class-congrats-shortcode.php');
		}


		//Call the dependency files
		public function helpers() {

			require_once ('lib/class-attest-script.php');
			require_once ('lib/class-attest-cron.php');

			require_once ('lib/user/class-attest-role.php');
			require_once ('lib/user/class-attest-tutor-table.php');
			require_once ('lib/user/class-attest-student-table.php');

			require_once ('lib/announcement/class-announcement-form.php');
			require_once ('lib/announcement/class-announcement-table.php');

			require_once ('lib/email/class-announcement-email.php');
			require_once ('lib/email/class-new-course-email.php');
			require_once ('lib/email/class-update-course-email.php');
			require_once ('lib/email/class-continue-course-email.php');
			require_once ('lib/email/class-complete-course-email.php');

			require_once ('lib/auth/class-register-user.php');
			require_once ('lib/auth/class-sign-user.php');

			require_once ('lib/ajax/class-create-lesson-ajax.php');
			require_once ('lib/ajax/class-save-lesson-ajax.php');

			require_once ('lib/templates/class-hook.php');
			require_once ('lib/templates/archive-sidebar.php');
			require_once ('lib/templates/class-course-archive-functions.php');
			require_once ('lib/templates/class-course-temp-functions.php');

			require_once ('lib/settings/class-update-temp.php');
			require_once ('lib/settings/class-recaptcha-script.php');

			require_once ('lib/data/class-attest-data.php');
			require_once ('lib/data/class-attest-export.php');
			require_once ('lib/data/class-attest-import.php');

			require_once ( 'lib/woocommerce/class-attest-new-product-woo.php' );
			require_once ( 'lib/woocommerce/class-attest-success-woo.php' );
			require_once ( 'lib/woocommerce/class-attest-account-woo.php' );
			require_once ( 'lib/woocommerce/class-attest-addtocart-woo.php' );

			require_once ( 'lib/user/class-attest-admin-bar.php' );
			require_once ( 'lib/user/class-attest-lesson-resitriction.php' );
		}


		public function __construct() {

			$this->helpers();
			$this->functionality();

			$this->data = $this->data();

			register_activation_hook( ATTEST_LMS_FILE, array( $this, 'db_install' ) );
			register_activation_hook( ATTEST_LMS_FILE, array( $this, 'install_data' ) );

			register_deactivation_hook( ATTEST_LMS_FILE, array( 'ATTEST_LMS_BUILD', 'deactivate' ) );
			register_uninstall_hook( ATTEST_LMS_FILE, array( 'ATTEST_LMS_BUILD', 'deactivate' ) );

			add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );

			$this->attest_lms_freemium();
			$this->attest_lms_freemium()->add_filter('connect_message_on_update', array($this, 'attest_lms_freemium_custom_connect_message_on_update'), 10, 6);
			do_action( 'attest_lms_freemium_loaded' );

			add_action( 'init', array( $this, 'installation' ) );
			add_action( 'init', array( $this, 'taxonomy' ) );
			add_action( 'init', array( $this, 'cpt' ) );
			add_action( 'init', array( $this, 'auth' ) );
			add_action( 'init', array( $this, 'templates' ) );
			add_action( 'init', array( $this, 'settings_cb' ) );
			add_action( 'init', array( $this, 'email_cb' ) );
			add_action( 'init', array( $this, 'import_export' ) );

			add_action( 'template_redirect', array( $this, 'lesson_Restriction' ) );

			add_action( 'wp', array( $this, 'shortcode' ) );

			add_action( 'widgets_init', array( $this, 'archive_sidebar' ));

			add_action( 'admin_init', array( $this, 'ajax_cb' ) );

			add_action( 'init', array( $this, 'woo_success' ) );
			add_action( 'wp', array( $this, 'hide_Admin_bar' ) );
			add_action( 'wp_loaded', array( $this, 'add_to_cart' ) );

			$this->woo_new_product();

			$this->scripts();
			$this->metabox();
			$this->settings();
		}
	}
} ?>
