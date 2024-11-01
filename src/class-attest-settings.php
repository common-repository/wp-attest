<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Backend settings page class, can have settings fields or data table
 */
if ( ! class_exists( 'ATTEST_LMS_SETTINGS' ) ) {

	final class ATTEST_LMS_SETTINGS {


		public $excess_fields;
		protected $page_template;
		protected $courses_list;
		public $capability;
		public $subMenuPage;
		public $help;
		public $announcement_table;
		public $tutor_table;
		protected $pro_installed;


		// Add basic actions for menu and settings
		public function __construct() {

			$this->excess_fields = $this->excess_fields();
			$this->page_template = $this->list_pages();
			$this->courses_list = $this->courses_list();

			$plugins = $this->get_activated_plugin();
			if( in_array('WP Attest PRO - Elementor', $plugins) || in_array('WP Attest PRO - Quize', $plugins) ) {
				$this->pro_installed = true;
			} else {
				$this->pro_installed = false;
			}

			$this->capability = 'manage_options';
			$this->subMenuPage = apply_filters( 'attest_settings_pages', array(
									array(
										'name' => __('Get Started', 'attest'),
										'heading' => __('Get Started', 'attest'),
										'slug' => 'attest',
										'parent_slug' => 'edit.php?post_type=attest_course',
										'callback' => 'start_page_cb',
										'help' => false,
										'screen' => false,
										'screen_option_cb' => false
									),
									array(
										'name' => __('Announcements', 'attest'),
										'heading' => __('Announcements', 'attest'),
										'slug' => 'announcement',
										'parent_slug' => 'edit.php?post_type=attest_course',
										'callback' => 'announcement_page_cb',
										'help' => false,
										'screen' => true,
										'screen_option_cb' => 'announcement_screen_cb'
									),
									array(
										'name' => __('Tutors', 'attest'),
										'heading' => __('Tutors', 'attest'),
										'slug' => 'tutors',
										'parent_slug' => 'edit.php?post_type=attest_course',
										'callback' => 'tutor_page_cb',
										'help' => false,
										'screen' => true,
										'screen_option_cb' => 'tutor_screen_cb'
									),
									array(
										'name' => __('Students', 'attest'),
										'heading' => __('Students', 'attest'),
										'slug' => 'students',
										'parent_slug' => 'edit.php?post_type=attest_course',
										'callback' => 'students_page_cb',
										'help' => false,
										'screen' => true,
										'screen_option_cb' => 'student_screen_cb'
									),
									array(
										'name' => __('Settings', 'attest'),
										'heading' => __('Settings', 'attest'),
										'slug' => 'settings',
										'parent_slug' => 'edit.php?post_type=attest_course',
										'callback' => 'settings_page_cb',
										'help' => false,
										'screen' => false,
										'screen_option_cb' => false
									),
								));

			add_action( 'admin_init', array( $this, 'remove_submenu_page' ) );
			add_action( 'admin_head', array( $this, 'pro_links_in_new_tab') );
			add_action( 'admin_head', array( $this, 'delighted_survey') );

			add_action( 'admin_menu', array( $this, 'sub_menu_page' ) );
			add_action( 'admin_menu' , array($this, 'elementor_pro_add_on_link'), 30);
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

			add_action( 'admin_init', array($this, 'add_template_settings') );
			add_action( 'admin_init', array($this, 'add_recaptcha_settings') );
			add_action( 'admin_init', array($this, 'add_email_settings') );
			add_action( 'admin_init', array($this, 'add_data_settings') );

			add_action( 'show_user_profile', array( $this, 'tutors_profile_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'tutors_profile_fields' ) );

			add_action( 'personal_options_update', array( $this, 'save_tutors_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_tutors_profile_fields' ) );
		}


		//Template settings
		public function add_template_settings() {

			add_settings_section( 'attest_template_settings_id', __( 'Templates', 'attest' ), array( $this,'template_settings_section_cb' ), 'attest_template_settings_section' );

      register_setting( 'attest_template_settings_id', 'attest_courses_template' );
      add_settings_field( 'attest_courses_template', __( 'Courses archive', 'attest' ), array( $this, 'courses_template_settings_selection_cb' ), 'attest_template_settings_section', 'attest_template_settings_id' );
			register_setting( 'attest_template_settings_id', 'attest_register_template' );
      add_settings_field( 'attest_register_template', __( 'Register', 'attest' ), array( $this, 'register_template_settings_selection_cb' ), 'attest_template_settings_section', 'attest_template_settings_id' );
			register_setting( 'attest_template_settings_id', 'attest_login_template' );
      add_settings_field( 'attest_login_template', __( 'Login', 'attest' ), array( $this, 'login_template_settings_selection_cb' ), 'attest_template_settings_section', 'attest_template_settings_id' );
			register_setting( 'attest_template_settings_id', 'attest_congrats_template' );
      add_settings_field( 'attest_congrats_template', __( 'Congratulations', 'attest' ), array( $this, 'congrats_template_settings_selection_cb' ), 'attest_template_settings_section', 'attest_template_settings_id' );
			register_setting( 'attest_template_settings_id', 'attest_my_account_template' );
      add_settings_field( 'attest_my_account_template', __( 'My Account', 'attest' ), array( $this, 'account_template_settings_selection_cb' ), 'attest_template_settings_section', 'attest_template_settings_id' );
			register_setting( 'attest_template_settings_id', 'attest_number_courses' );
      add_settings_field( 'attest_number_courses', __( 'Number of courses per page', 'attest' ), array( $this, 'list_courses_number_settings_cb' ), 'attest_template_settings_section', 'attest_template_settings_id' );
		}


		//reCaptcha settings
		public function add_recaptcha_settings() {

			add_settings_section( 'attest_recaptcha_settings_id', __( 'reCaptcha', 'attest' ), array( $this,'recaptcha_settings_section_cb' ), 'attest_recaptcha_settings_section' );

			register_setting( 'attest_recaptcha_settings_id', 'attest_site_key_recaptcha' );
			add_settings_field( 'attest_site_key_recaptcha', __( 'Site key', 'attest' ), array( $this, 'recaptcha_settings_site_key_cb' ), 'attest_recaptcha_settings_section', 'attest_recaptcha_settings_id' );
			register_setting( 'attest_recaptcha_settings_id', 'attest_secret_key_recaptcha' );
			add_settings_field( 'attest_secret_key_recaptcha', __( 'Secret key', 'attest' ), array( $this, 'recaptcha_settings_secret_key_cb' ), 'attest_recaptcha_settings_section', 'attest_recaptcha_settings_id' );
		}


		//Email settings
		public function add_email_settings() {

			add_settings_section( 'attest_announcement_email_settings_id', __( 'Announcement email', 'attest' ), array( $this,'announcement_email_settings_section_cb' ), 'attest_announcement_email_settings_section' );

			register_setting( 'attest_announcement_email_settings_id', 'attest_email_announcement_permission' );
			add_settings_field( 'attest_email_announcement_permission', __( 'Enable/Disable', 'attest' ), array( $this, 'announcement_email_settings_permission_cb' ), 'attest_announcement_email_settings_section', 'attest_announcement_email_settings_id' );
			register_setting( 'attest_announcement_email_settings_id', 'attest_email_announcement_subject' );
			add_settings_field( 'attest_email_announcement_subject', __( 'Subject', 'attest' ), array( $this, 'announcement_email_settings_subject_cb' ), 'attest_announcement_email_settings_section', 'attest_announcement_email_settings_id' );
			register_setting( 'attest_announcement_email_settings_id', 'attest_email_announcement_body' );
			add_settings_field( 'attest_email_announcement_body', __( 'Body', 'attest' ), array( $this, 'announcement_email_settings_body_cb' ), 'attest_announcement_email_settings_section', 'attest_announcement_email_settings_id' );

			add_settings_section( 'attest_new_course_email_settings_id', __( 'New course email', 'attest' ), array( $this,'new_course_email_settings_section_cb' ), 'attest_new_course_email_settings_section' );

			register_setting( 'attest_new_course_email_settings_id', 'attest_email_new_course_permission' );
			add_settings_field( 'attest_email_new_course_permission', __( 'Enable/Disable', 'attest' ), array( $this, 'new_course_email_settings_permission_cb' ), 'attest_new_course_email_settings_section', 'attest_new_course_email_settings_id' );
			register_setting( 'attest_new_course_email_settings_id', 'attest_target_new_course' );
			add_settings_field( 'attest_target_new_course', __( 'Target audience of', 'attest' ), array( $this, 'new_course_target_settings_cb' ), 'attest_new_course_email_settings_section', 'attest_new_course_email_settings_id' );
			register_setting( 'attest_new_course_email_settings_id', 'attest_email_new_course_subject' );
			add_settings_field( 'attest_email_new_course_subject', __( 'Subject', 'attest' ), array( $this, 'new_course_email_settings_subject_cb' ), 'attest_new_course_email_settings_section', 'attest_new_course_email_settings_id' );
			register_setting( 'attest_new_course_email_settings_id', 'attest_email_new_course_body' );
			add_settings_field( 'attest_email_new_course_body', __( 'Body', 'attest' ), array( $this, 'new_course_email_settings_body_cb' ), 'attest_new_course_email_settings_section', 'attest_new_course_email_settings_id' );

			add_settings_section( 'attest_completed_course_email_settings_id', __( 'Completed course email', 'attest' ), array( $this,'completed_course_email_settings_section_cb' ), 'attest_completed_course_email_settings_section' );

			register_setting( 'attest_completed_course_email_settings_id', 'attest_email_completed_course_permission' );
			add_settings_field( 'attest_email_completed_course_permission', __( 'Enable/Disable', 'attest' ), array( $this, 'completed_course_email_settings_permission_cb' ), 'attest_completed_course_email_settings_section', 'attest_completed_course_email_settings_id' );
			register_setting( 'attest_completed_course_email_settings_id', 'attest_email_completed_course_subject' );
			add_settings_field( 'attest_email_completed_course_subject', __( 'Subject', 'attest' ), array( $this, 'completed_course_email_settings_subject_cb' ), 'attest_completed_course_email_settings_section', 'attest_completed_course_email_settings_id' );
			register_setting( 'attest_completed_course_email_settings_id', 'attest_email_completed_course_body' );
			add_settings_field( 'attest_email_completed_course_body', __( 'Body', 'attest' ), array( $this, 'completed_course_email_settings_body_cb' ), 'attest_completed_course_email_settings_section', 'attest_completed_course_email_settings_id' );

			add_settings_section( 'attest_updated_course_email_settings_id', __( 'Updated course email', 'attest' ), array( $this,'updated_course_email_settings_section_cb' ), 'attest_updated_course_email_settings_section' );

			register_setting( 'attest_updated_course_email_settings_id', 'attest_ok_updated_course' );
			add_settings_field( 'attest_ok_updated_course', __( 'Enable/Disable', 'attest' ), array( $this, 'updated_course_ok_settings_cb' ), 'attest_updated_course_email_settings_section', 'attest_updated_course_email_settings_id' );
			register_setting( 'attest_updated_course_email_settings_id', 'attest_email_updated_course_subject' );
			add_settings_field( 'attest_email_updated_course_subject', __( 'Subject', 'attest' ), array( $this, 'updated_course_email_settings_subject_cb' ), 'attest_updated_course_email_settings_section', 'attest_updated_course_email_settings_id' );
			register_setting( 'attest_updated_course_email_settings_id', 'attest_email_updated_course_body' );
			add_settings_field( 'attest_email_updated_course_body', __( 'Body', 'attest' ), array( $this, 'updated_course_email_settings_body_cb' ), 'attest_updated_course_email_settings_section', 'attest_updated_course_email_settings_id' );

			add_settings_section( 'attest_continue_course_email_settings_id', __( 'Continue course email', 'attest' ), array( $this,'continue_course_email_settings_section_cb' ), 'attest_continue_course_email_settings_section' );

			register_setting( 'attest_continue_course_email_settings_id', 'attest_email_continue_course_permssion' );
			add_settings_field( 'attest_email_continue_course_permssion', __( 'Enable/Disable', 'attest' ), array( $this, 'continue_course_email_settings_permssion_cb' ), 'attest_continue_course_email_settings_section', 'attest_continue_course_email_settings_id' );
			register_setting( 'attest_continue_course_email_settings_id', 'attest_email_continue_course_time' );
			add_settings_field( 'attest_email_continue_course_time', __( 'Wait time', 'attest' ), array( $this, 'continue_course_email_settings_time_cb' ), 'attest_continue_course_email_settings_section', 'attest_continue_course_email_settings_id' );
			register_setting( 'attest_continue_course_email_settings_id', 'attest_email_continue_course_subject' );
			add_settings_field( 'attest_email_continue_course_subject', __( 'Subject', 'attest' ), array( $this, 'continue_course_email_settings_subject_cb' ), 'attest_continue_course_email_settings_section', 'attest_continue_course_email_settings_id' );
			register_setting( 'attest_continue_course_email_settings_id', 'attest_email_continue_course_body' );
			add_settings_field( 'attest_email_continue_course_body', __( 'Body', 'attest' ), array( $this, 'continue_course_email_settings_body_cb' ), 'attest_continue_course_email_settings_section', 'attest_continue_course_email_settings_id' );
		}


		public function add_data_settings() {

			add_settings_section( 'attest_import_export_settings_id', __( 'Manage data', 'attest' ), array( $this,'attest_import_export_settings_section_cb' ), 'attest_import_export_settings_section' );

			register_setting( 'attest_import_export_settings_id', 'attest_manage_import' );
			add_settings_field( 'attest_manage_import', __( 'Import', 'attest' ), array( $this, 'import_settings_cb' ), 'attest_import_export_settings_section', 'attest_import_export_settings_id' );
			register_setting( 'attest_import_export_settings_id', 'attest_manage_export' );
			add_settings_field( 'attest_manage_export', __( 'Export', 'attest' ), array( $this, 'export_settings_cb' ), 'attest_import_export_settings_section', 'attest_import_export_settings_id' );
		}


		//Remove tags submenu page
		public function remove_submenu_page() {

			global $submenu;

			unset( $submenu['edit.php?post_type=course'][15] );
		}


		//Add a sample Submenu page callback
		public function sub_menu_page() {

			if ($this->subMenuPage) {
				foreach ($this->subMenuPage as $page) {
					$hook = add_submenu_page(
								$page['parent_slug'],
								$page['name'],
								$page['heading'],
								$this->capability,
								$page['slug'],
								array( $this, $page['callback'] )
							);
						if ($page['screen']) {
							add_action( 'load-' . $hook, array( $this, $page['screen_option_cb'] ) );
						}
				}
			}
		}


		//Set screen option
		public function set_screen_option($status, $option, $value) {

			$user = get_current_user_id();

			switch ($option) {
				case 'announcements_per_page':
					update_user_meta($user, 'announcements_per_page', $value);
					$output = $value;
					break;

				case 'tutors_per_page':
					update_user_meta($user, 'tutors_per_page', $value);
					$output = $value;
					break;

				case 'students_per_page':
					update_user_meta($user, 'students_per_page', $value);
					$output = $value;
					break;
			}

			if($output) return $output;
		}


		//Set screen option for announcement table
		public function announcement_screen_cb() {

			$option = 'per_page';
			$args   = array(
								'label'   => __( 'Announcements per page', 'attest' ),
								'default' => 10,
								'option'  => 'announcements_per_page'
			);

			add_screen_option( $option, $args );
			$this->announcement_table = new ATTEST_LMS_ANNOUNCEMENT_TABLE();
		}


		//Set screen option for tutor table
		public function tutor_screen_cb() {

			$option = 'per_page';
			$args   = array(
								'label'   => __( 'Tutors per page', 'attest' ),
								'default' => 10,
								'option'  => 'tutors_per_page'
			);
			add_screen_option( $option, $args );
			$this->tutor_table = new ATTEST_LMS_TUTOR_TABLE();
		}

		//Set screen option for student table
		public function student_screen_cb() {

			$option = 'per_page';
			$args   = array(
								'label'   => __( 'Students per page', 'attest' ),
								'default' => 10,
								'option'  => 'students_per_page'
			);

			add_screen_option( $option, $args );
			$this->student_table = new ATTEST_LMS_PRO_STUDENT_TABLE();
		}


		//Template section
		public function template_settings_section_cb() { ?>

			<p class="description"><?php _e( 'In order for the system to work, you need to assign templates to the pages you already created.', 'attest' ); ?></p>
			<?php
		}


		//Courses template cb
		public function courses_template_settings_selection_cb() {

			$attest_courses_template = get_option('attest_template_courses');
			?>

			<select name="attest_courses_template" class="regular-text" autocomplete="off">
				<option value=""><?php _e('Select', 'attest'); ?></option>
      <?php foreach ($this->page_template as $key => $val) : ?>
        <option value="<?php echo esc_attr($key); ?>" <?php selected( $attest_courses_template, $key, true ); ?>><?php echo esc_attr($val); ?></option>
      <?php endforeach; ?>
      </select>
			<?php
		}


		//Register template cb
		public function register_template_settings_selection_cb() {

			$attest_register_template = get_option('attest_template_register');
			?>

			<select name="attest_register_template" class="regular-text" autocomplete="off">
				<option value=""><?php _e('Select', 'attest'); ?></option>
      <?php foreach ($this->page_template as $key => $val) : ?>
        <option value="<?php echo esc_attr($key); ?>" <?php selected( $attest_register_template, $key, true ); ?>><?php echo esc_attr($val); ?></option>
      <?php endforeach; ?>
      </select>
			<?php
		}


		//Login template cb
		public function login_template_settings_selection_cb() {

			$attest_login_template = get_option('attest_template_login');
			?>

			<select name="attest_login_template" class="regular-text" autocomplete="off">
				<option value=""><?php _e('Select', 'attest'); ?></option>
      <?php foreach ($this->page_template as $key => $val) : ?>
        <option value="<?php echo esc_attr($key); ?>" <?php selected( $attest_login_template, $key, true ); ?>><?php echo esc_attr($val); ?></option>
      <?php endforeach; ?>
      </select>
			<?php
		}


		//Congratulation template cb
		public function congrats_template_settings_selection_cb() {

			$attest_congrats_template = get_option('attest_template_congrats');
			?>

			<select name="attest_congrats_template" class="regular-text" autocomplete="off">
				<option value=""><?php _e('Select', 'attest'); ?></option>
      <?php foreach ($this->page_template as $key => $val) : ?>
        <option value="<?php echo esc_attr($key); ?>" <?php selected( $attest_congrats_template, $key, true ); ?>><?php echo esc_attr($val); ?></option>
      <?php endforeach; ?>
      </select>
			<?php
		}


		//My account template cb
		public function account_template_settings_selection_cb() {

			$attest_account_template = get_option('attest_template_my_account');
			?>

			<select name="attest_my_account_template" class="regular-text" autocomplete="off">
				<option value=""><?php _e('Select', 'attest'); ?></option>
			<?php foreach ($this->page_template as $key => $val) : ?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected( $attest_account_template, $key, true ); ?>><?php echo esc_attr($val); ?></option>
			<?php endforeach; ?>
			</select>
			<?php
		}


		//Number of courses per page
		public function list_courses_number_settings_cb() {

			$attest_number_courses = get_option('attest_number_courses'); ?>

			<input type="number" min="1" step="1" max="999" class="small-text" id="attest_number_courses" name="attest_number_courses" value="<?php echo intval(esc_attr($attest_number_courses)); ?>" />
			<?php
		}


		//Template section
		public function recaptcha_settings_section_cb() { ?>

			<p class="description"><?php _e( 'Use reCaptcha v2: I\'m not a robot details', 'attest' ); ?> <a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php _e('here', 'attest') ?></a>.</p>
			<?php
		}


		//Site key cb
		public function recaptcha_settings_site_key_cb() {

			$attest_site_key_recaptcha = get_option('attest_site_key_recaptcha'); ?>

			<input type="text" class="regular-text" id="attest_site_key_recaptcha" name="attest_site_key_recaptcha" value="<?php echo esc_attr($attest_site_key_recaptcha); ?>" />
			<?php
		}


		//Site key cb
		public function recaptcha_settings_secret_key_cb() {

			$attest_secret_key_recaptcha = get_option('attest_secret_key_recaptcha'); ?>

			<input type="text" class="regular-text" id="attest_secret_key_recaptcha" name="attest_secret_key_recaptcha" value="<?php echo esc_attr($attest_secret_key_recaptcha); ?>" />
			<?php
		}


		//Announcement email section cb
		public function announcement_email_settings_section_cb() { ?>

			<p class="description"><?php _e( 'Announcement email description. Available tags', 'attest' ); ?>: {first_name}, {last_name}, {announcement_title}, {announcement_description}</p>
			<?php
		}


		public function announcement_email_settings_permission_cb() {

			$attest_email_announcement_permission = get_option('attest_email_announcement_permission'); ?>

			<label for="attest_email_announcement_permission">
			<input type="checkbox" id="attest_email_announcement_permission" name="attest_email_announcement_permission" value="1" <?php checked($attest_email_announcement_permission, '1', true); ?> />
			<?php _e('Check this to Enable', 'attest'); ?>
			</label>
			<?php
		}


		public function announcement_email_settings_subject_cb() {

			$attest_email_announcement_subject = get_option('attest_email_announcement_subject'); ?>

			<input type="text" class="large-text" id="attest_email_announcement_subject" name="attest_email_announcement_subject" value="<?php echo esc_attr($attest_email_announcement_subject); ?>" />
			<?php
		}


		public function announcement_email_settings_body_cb() {

			$attest_email_announcement_body = get_option('attest_email_announcement_body'); ?>

			<textarea rows="5" class="large-text" id="attest_email_announcement_body" name="attest_email_announcement_body"><?php echo esc_attr($attest_email_announcement_body); ?></textarea>
			<?php
		}


		//New course email section cb
		public function new_course_email_settings_section_cb() { ?>

			<p class="description"><?php _e( 'New course email description. Available tags', 'attest' ); ?>: {first_name}, {last_name}, {course_title}</p>
			<?php
		}


		public function new_course_target_settings_cb() {

			$attest_target_new_course = get_option('attest_target_new_course'); ?>

			<select class="medium-text" id="attest_target_new_course" name="attest_target_new_course" autocomplete="off">
				<option value=""><?php _e('None', 'attest'); ?></option>
				<option value="all"><?php _e('All courses', 'attest'); ?></option>
				<?php foreach ($this->courses_list as $key => $value) : ?>
				<option value="<?php echo intval(esc_attr($key)); ?>" <?php selected($attest_target_new_course, $key, true); ?>><?php echo esc_attr($value); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
		}


		public function new_course_email_settings_permission_cb() {

			$attest_email_new_course_permission = get_option('attest_email_new_course_permission'); ?>

			<label for="attest_email_new_course_permission">
			<input type="checkbox" id="attest_email_new_course_permission" name="attest_email_new_course_permission" value="1" <?php checked($attest_email_new_course_permission, '1', true); ?> />
			<?php _e('Check this to Enable', 'attest'); ?>
			</label>
			<?php
		}


		public function new_course_email_settings_subject_cb() {

			$attest_email_new_course_subject = get_option('attest_email_new_course_subject'); ?>

			<input type="text" class="large-text" id="attest_email_new_course_subject" name="attest_email_new_course_subject" value="<?php echo esc_attr($attest_email_new_course_subject); ?>" />
			<?php
		}


		public function new_course_email_settings_body_cb() {

			$attest_email_new_course_body = get_option('attest_email_new_course_body'); ?>

			<textarea rows="5" class="large-text" id="attest_email_new_course_body" name="attest_email_new_course_body"><?php echo esc_attr($attest_email_new_course_body); ?></textarea>
			<?php
		}


		//Completed course email section cb
		public function completed_course_email_settings_section_cb() { ?>

			<p class="description"><?php _e( 'Completed course email description. Allowed tags', 'attest' ); ?>: {first_name}, {last_name}, {course_title}</p>
			<?php
		}


		public function completed_course_email_settings_permission_cb() {

			$attest_email_completed_course_permission = get_option('attest_email_completed_course_permission'); ?>

			<label for="attest_email_completed_course_permission">
			<input type="checkbox" id="attest_email_completed_course_permission" name="attest_email_completed_course_permission" value="1" <?php checked($attest_email_completed_course_permission, '1', true); ?> />
			<?php _e('Check this to Enable', 'attest'); ?>
			</label>
			<?php
		}


		public function completed_course_email_settings_subject_cb() {

			$attest_email_completed_course_subject = get_option('attest_email_completed_course_subject'); ?>

			<input type="text" class="large-text" id="attest_email_completed_course_subject" name="attest_email_completed_course_subject" value="<?php echo esc_attr($attest_email_completed_course_subject); ?>" />
			<?php
		}


		public function completed_course_email_settings_body_cb() {

			$attest_email_completed_course_body = get_option('attest_email_completed_course_body'); ?>

			<textarea rows="5" class="large-text" id="attest_email_completed_course_body" name="attest_email_completed_course_body"><?php echo esc_attr($attest_email_completed_course_body); ?></textarea>
			<?php
		}


		//Updated course email section cb
		public function updated_course_email_settings_section_cb() { ?>

			<p class="description"><?php _e( 'Updated course email description. Available tags', 'attest' ); ?>: {first_name}, {last_name}, {course_title}</p>
			<?php
		}


		public function updated_course_ok_settings_cb() {

			$attest_ok_updated_course = get_option('attest_ok_updated_course'); ?>

			<label>
			<input type="checkbox" id="attest_ok_updated_course" name="attest_ok_updated_course" value="1" <?php checked($attest_ok_updated_course, 1, true); ?> />
			<?php _e('Check to allow sending emails upon course update.', 'attest'); ?>
			</label>
			<?php
		}


		public function updated_course_email_settings_subject_cb() {

			$attest_email_updated_course_subject = get_option('attest_email_updated_course_subject'); ?>

			<input type="text" class="large-text" id="attest_email_updated_course_subject" name="attest_email_updated_course_subject" value="<?php echo esc_attr($attest_email_updated_course_subject); ?>" />
			<?php
		}


		public function updated_course_email_settings_body_cb() {

			$attest_email_updated_course_body = get_option('attest_email_updated_course_body'); ?>

			<textarea rows="5" class="large-text" id="attest_email_updated_course_body" name="attest_email_updated_course_body"><?php echo esc_attr($attest_email_updated_course_body); ?></textarea>
			<?php
		}


		//Continue course email section cb
		public function continue_course_email_settings_section_cb() { ?>

			<p class="description"><?php _e( 'Continue course email description. Available tags', 'attest' ); ?>: {first_name}, {last_name}, {course_title}</p>
			<?php
		}


		public function continue_course_email_settings_time_cb() {

			$attest_email_continue_course_time = get_option('attest_email_continue_course_time'); ?>

			<input type="number" class="small-text" id="attest_email_continue_course_time" name="attest_email_continue_course_time" value="<?php echo esc_attr($attest_email_continue_course_time); ?>" />
			<?php _e('days of no activity', 'attest') ?>
			<?php
		}


		public function continue_course_email_settings_permssion_cb() {

			$attest_email_continue_course_permission = get_option('attest_email_continue_course_permission'); ?>

			<label for="attest_email_continue_course_permission">
			<input type="checkbox" id="attest_email_continue_course_permission" name="attest_email_continue_course_permission" value="1" <?php checked($attest_email_continue_course_permission, '1', true); ?> />
			<?php _e('Check this to Enable', 'attest'); ?>
			</label>
			<?php
		}


		public function continue_course_email_settings_subject_cb() {

			$attest_email_continue_course_subject = get_option('attest_email_continue_course_subject'); ?>

			<input type="text" class="large-text" id="attest_email_continue_course_subject" name="attest_email_continue_course_subject" value="<?php echo esc_attr($attest_email_continue_course_subject); ?>" />
			<?php
		}


		public function continue_course_email_settings_body_cb() {

			$attest_email_continue_course_body = get_option('attest_email_continue_course_body'); ?>

			<textarea rows="5" class="large-text" id="attest_email_continue_course_body" name="attest_email_continue_course_body"><?php echo esc_attr($attest_email_continue_course_body); ?></textarea>
			<?php
		}


		public function attest_import_export_settings_section_cb() { ?>

		 	<p class="description"><?php _e( 'Import Export data', 'attest' ); ?></p>
		 	<?php
		}


		public function import_settings_cb() { ?>

			<form method="post" action="" enctype="multipart/form-data">
				<input type="file" multiple="false" name="attest_import_data_upload" />
				<input type="submit" name="attest_import_data_execute" class="button button-primary" value="<?php _e('Import', 'attest'); ?>" />
			</form>
			<p><?php echo sprintf( __('Select %s file to upload', 'attest'), '.json'); ?></p>
			<?php
		}


		public function export_settings_cb() { ?>

			<a class="button button-primary" href="<?php echo admin_url('edit.php?post_type=attest_course&page=settings&tab=data&export=true'); ?>" target="_blank"><?php _e('Export', 'attest'); ?></a>
		<?php
		}


		// SubMenu page callback
		public function announcement_page_cb() {

			$modal = new ATTEST_LMS_ANNOUNCEMENT_FORM(); ?>

			<div class="wrap">
				<h1><?php echo get_admin_page_title(); ?>
				<?php echo $modal->trigger(); ?>
				</h1>
				<br class="clear">
				<?php $modal->create_announcement(); ?>
				<?php settings_errors(); ?>

				<form method="post" action="">
					<?php
						$this->announcement_table->prepare_items();
						$this->announcement_table->display(); ?>
					</form>
				<br class="clear">
			</div>

			<?php echo $modal->body(); ?>
			<?php
		}


		public function tutor_page_cb() { ?>

			<div class="wrap">
				<h1><?php echo get_admin_page_title(); ?>
				<a href="<?php echo admin_url( 'user-new.php' ); ?>" class="page-title-action"><?php _e('Add New', 'attest') ?></a>
				</h1>
				<br class="clear">
				<?php settings_errors(); ?>

				<form method="post" action="">
					<?php
						$this->tutor_table->prepare_items();
						$this->tutor_table->display(); ?>
					</form>
				<br class="clear">
			</div>
		<?php
		}


		public function students_page_cb() { ?>

			<div class="wrap">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<br class="clear">
				<?php settings_errors(); ?>

				<form method="post" action="" onSubmit="return confirm('<?php _e( 'Are you sure you want to delete? The action is irreversible.', 'attest' ); ?>')">
					<?php
						$this->student_table->prepare_items();
						$this->student_table->display(); ?>
					</form>
				<br class="clear">
			</div>
		<?php
		}


		//Settings page save
		public function settings_page_cb() { ?>

			<div class="wrap">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<br class="clear">
				<?php settings_errors();

				$tabs = apply_filters( 'attest_lms_settings_tabs', array(
					'template' 		=> __('Templates', 'attest'),
					're-captcha'	=> __('reCaptcha', 'attest'),
					'email'				=> __('Email', 'attest'),
					'data'				=> __('Backup', 'attest'),
				) );
				$current = (isset($_GET['tab'])) ? $_GET['tab'] : 'template';

				echo '<h2 class="nav-tab-wrapper">';
				foreach( $tabs as $tab => $name ){
					$class = ( $tab == $current ) ? ' nav-tab-active' : false;
					echo '<a class="nav-tab' . esc_attr($class) . '" href="?post_type=attest_course&page=settings&tab=' . esc_attr($tab) . '">' . esc_attr($name) . '</a>';
				}
				echo '</h2>';

				switch ( $current ) {

					case 'template' :

						echo '<form method="post" action="">';
						wp_nonce_field( ATTEST_LMS_FILE, 'attest_template_settings_nonce' );
						settings_fields('attest_template_settings_id');
						do_settings_sections('attest_template_settings_section');
						submit_button( __( 'Save', 'attest' ), 'primary', 'attest_template_submit' );
						echo '</form>';
						break;

					case 're-captcha' :

						echo '<form method="post" action="options.php">';
						settings_fields('attest_recaptcha_settings_id');
						do_settings_sections('attest_recaptcha_settings_section');
						submit_button( __( 'Save', 'attest' ), 'primary', 'attest_recaptcha_submit' );
						echo '</form>';
						break;

					case 'email' :

						$tab = (isset($_GET['tab']) ? $_GET['tab'] : 'email');
						$sub_tabs = array(
							'announcement' 		=> __('Announcement', 'attest'),
							'new-course'			=> __('New Course', 'attest'),
							'updated-course'	=> __('Updated Course', 'attest'),
							'continue-course'	=> __('Continue Course', 'attest'),
							'completed-course'=> __('Completed Course', 'attest'),
						);
						$current_sub_tab = (isset($_GET['subtab'])) ? $_GET['subtab'] : 'announcement';

						echo '<ul class="subsubsub">';
						$i = 0;
						foreach( $sub_tabs as $sub_tab => $sub_name ){
							$sub_class = ( $sub_tab == $current_sub_tab ) ? 'current' : false;
							echo '<li>' . (($sub_class == 'current') ? esc_attr($sub_name) . ($i < ( count($sub_tabs) - 1 ) ? '&nbsp;|&nbsp;' : false) : '<a class="' . esc_attr($sub_class) . '" href="?post_type=attest_course&page=settings&tab=' . esc_attr($tab) . '&subtab=' . esc_attr($sub_tab) . '">' . esc_attr($sub_name) . '</a>' . ($i < ( count($sub_tabs) - 1 ) ? '&nbsp;|&nbsp;' : false) ) . '</li>';
							$i++;
						}
						echo '</ul>';
						echo '<form method="post" action="options.php"><br /><br />';

						switch ( $current_sub_tab ){

							case 'announcement' :
								settings_fields('attest_announcement_email_settings_id');
								do_settings_sections('attest_announcement_email_settings_section');
								break;

							case 'new-course' :
								settings_fields('attest_new_course_email_settings_id');
								do_settings_sections('attest_new_course_email_settings_section');
								break;

							case 'updated-course' :
								settings_fields('attest_updated_course_email_settings_id');
								do_settings_sections('attest_updated_course_email_settings_section');
								break;

							case 'continue-course' :
								settings_fields('attest_continue_course_email_settings_id');
								do_settings_sections('attest_continue_course_email_settings_section');
								break;

							case 'completed-course' :
								settings_fields('attest_completed_course_email_settings_id');
								do_settings_sections('attest_completed_course_email_settings_section');
								break;
						}

						submit_button( __( 'Save', 'attest' ), 'primary', 'attest_email_submit' );
						echo '</form>';
						break;

					case 'data' :

						settings_fields('attest_import_export_settings_id');
						do_settings_sections('attest_import_export_settings_section');
						break;
				}

				do_action( 'attest_lms_tab_content', $tabs, $current );
				?>
				<br class="clear">
			</div>
			<?php
		}


		// SubMenu page callback
		public function start_page_cb() { ?>

			<style type="text/css">
				.has-subtle-background-color{background-color: #f2edd3;}
				#wpcontent, .about__container{background-color: #ffffff;}
			</style>
			<div class="wrap about__container">
				<?php settings_errors(); ?>

				<div class="about__section has-2-columns has-subtle-background-color has-accent-background-color is-wider-right">
					<div class="column">
						<h2><?php _e('Create your first Online Course with WP Attest', 'attest'); ?></h2>
						<p><?php _e('Build your Curriculum with an intuitive editor.', 'attest');?></p>
					</div>
					<div class="column about__image is-vertically-aligned-center">
						<iframe width="560" height="315" src="https://www.youtube.com/embed/DzL33VnNqJo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>

				<br class="clear" />

				<div class="about__section">
					<div class="column">
						<p><?php echo sprintf( __('Enjoying WP Attest? Drop us a %2s (5 star) rating. Your support will move us forward to make it better!', 'attest'), '<a href="https://wordpress.org/support/plugin/wp-attest/reviews/#new-post" target="_blank">★★★★★</a>'); ?></p>
					</div>
				</div>

				<div class="about__section has-1-column">
					<div class="column">
						<h2><?php _e('Get paid through WooCommerce', 'attest'); ?></h2>
						<p><?php _e('Harness the power of WooCommerce by selling your online courses, offer flexible secure payments, manage orders, and automate the entire financial cycle.', 'attest'); ?></p>
						<p><?php _e(' With WooCommerce you can integrate any of the major payment gateways that fit your interests. Be it PayPal, Stripe, 2Checkout, AmazonPay, Paysafe, PayU or others, you can either use multiple or choose only one.', 'attest'); ?></p>
						<br class="clear" />
						<h2><?php _e('Quick Links', 'attest'); ?></h2>
						<div class="about__section has-2-columns">
							<div class="column" style="padding: 0px;">
								<ul>
									<li><a href="<?php echo admin_url('post-new.php?post_type=attest_course'); ?>"><?php _e('Create a new Course', 'attest') ?></a></li>
									<li><a href="<?php echo admin_url('post-new.php?post_type=attest_lesson'); ?>"><?php _e('Create a new Lesson', 'attest') ?></a></li>
									<li><a href="<?php echo admin_url('edit.php?post_type=elementor_library&tabs_group=theme'); ?>"><?php _e('Theme Builder', 'attest') ?></a></li>
									<li><a href="<?php echo admin_url('edit.php?post_type=attest_course&page=settings'); ?>"><?php _e('Templates', 'attest') ?></a></li>
									<li><a href="<?php echo admin_url('edit.php?post_type=attest_course&page=settings&tab=email'); ?>"><?php _e('Customize emails', 'attest') ?></a></li>
								</ul>
							</div>
							<div class="column" style="padding: 0px;">
								<ul>
									<li><a href="https://www.wpattest.com/documentation" target="_blank"><?php _e('Documentation', 'attest') ?></a></li>
									<li><a href="https://wordpress.org/support/plugin/wp-attest" target="_blank"><?php _e('Support', 'attest') ?></a></li>
									<li><a href="https://wordpress.org/support/plugin/wp-attest" target="_blank"><?php _e('Report a bug', 'attest') ?></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<br class="clear" />

				<div class="about__section has-2-columns has-accent-background-color is-wider-right">
					<div class="column">
						<h2><?php _e('Design everything with Elementor', 'attest'); ?></h2>
						<p><?php _e('Customize the look and feel of every component in the frontend with our Elementor widgets.', 'attest');?></p>
						<a href="https://www.wpattest.com/pro/elementor/" target="_blank"><div><?php _e('Get it for 60€', 'attest'); ?></div></a>
						<p><?php _e('<strong>NOTE!</strong> You need Elementor PRO.', 'attest'); ?></p>
					</div>
					<div class="column about__image is-vertically-aligned-center">
						<iframe width="560" height="315" src="https://www.youtube.com/embed/w7sbF_x47zk" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>

				<br class="clear" />

				<div class="about__section has-1-column">
					<div class="column">
						<h2><?php _e('More…', 'attest'); ?></h2>
						<p><?php _e('Learn more about the Features of WP Attest and see how it can help you make the most of it.', 'attest'); ?></p>
						<p><?php _e('Our solution is good for Academics, Distance Learning, Employee Training, and help students to develop new Skills.', 'attest'); ?></p>
						<p><?php _e('Inspire a generation!.', 'attest'); ?></p>
					</div>
				</div>

				<br class="clear" />

				<div class="about__section has-subtle-background-color has-accent-background-color is-wider-right">
					<div class="column">
						<h2><?php _e('Version 1.7.3', 'attest'); ?></h2>
						<small><?php _e('January 14, 2021', 'attest'); ?></small>
						<p><?php _e('For more information, see <a href="https://www.wpattest.com/changelog/" target="_blank">the release notes</a>', 'attest');?></p>
					</div>
				</div>

			</div>
			<?php
		}


		//Add social links to tutors profile
		public function tutors_profile_fields( $user ) {

			echo '<h3 class="heading">About Author</h3>'; ?>

			<table class="form-table">
					<?php $about = get_user_meta( $user->ID, 'attest_about_author', true ); ?>
					<tr>
					<th><label for="attest_about_author"><?php _e('Title', 'att'); ?></label></th>
					<td>
							<input type="text" class="regular-text" name="attest_about_author" id="attest_about_author" value="<?php echo esc_attr($about); ?>" />
					</td>
					</tr>
			</table>

			<?php echo '<h3 class="heading">Social Links</h3>'; ?>

			<table class="form-table">
					<?php foreach ($this->excess_fields as $item) : ?>
					<?php $field_value = get_user_meta( $user->ID, $item[1], true ); ?>
					<tr>
			    <th><label for="<?php echo esc_attr($item[1]); ?>"><?php echo esc_attr($item[0]); ?></label></th>
					<td>
							<input type="text" class="regular-text" name="<?php echo esc_attr($item[1]); ?>" id="<?php echo esc_attr($item[1]); ?>" value="<?php echo esc_url_raw($field_value); ?>" />
					</td>
					</tr>
					<?php endforeach; ?>
			</table>
		<?php
		}


		//Elementor pro add on link
		public function elementor_pro_add_on_link() {

			global $submenu;

			if (false == $this->pro_installed) {

				$submenu['edit.php?post_type=attest_course'][500] = array( '<span style="color: #BF1B5A;">' . __('Elementor', 'attest') . '</span>', 'manage_options', 'https://www.wpattest.com/pro/elementor' );
			} else {

				$submenu['edit.php?post_type=attest_course'][500] = array( __('Documentation', 'attest'), 'manage_options', 'https://www.wpattest.com/documentation' );
			}
		}


		//Delighted survey
		public function delighted_survey() {

			if (isset($_GET['post_type']) && $_GET['post_type'] == 'attest_course' && isset($_GET['page']) && ($_GET['page'] == 'attest' || $_GET['page'] == 'settings')) {	?>

				<script type="text/javascript">
					!function(e,t,r,n){if(!e[n]){for(var a=e[n]=[],i=["survey","reset","config","init","set","get","event","identify","track","page","screen","group","alias"],o=0;o<i.length;o++){var s=i[o];a[s]=a[s]||function(e){return function(){var t=Array.prototype.slice.call(arguments);a.push([e,t])}}(s)}a.SNIPPET_VERSION="1.0.1";var c=t.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://d2yyd1h5u9mauk.cloudfront.net/integrations/web/v1/library/"+r+"/"+n+".js";var f=t.getElementsByTagName("script")[0];f.parentNode.insertBefore(c,f)}}(window,document,"wO7nf0WzjfoRlHod","delighted");
					delighted.survey();
				</script>
			<?php
			}
		}


		//Open in new tab
		public function pro_links_in_new_tab() {

			?>
			<script type="text/javascript">
				jQuery(document).ready( function($) {
					jQuery( "ul#adminmenu a[href$='https://www.wpattest.com/pro/elementor']" ).attr( 'target', '_blank' ).css('color', '#8750B9');
					jQuery( "ul#adminmenu a[href$='https://www.wpattest.com/documentation']" ).attr( 'target', '_blank' );
				});
			</script>
		<?php
		}


		//Update user profile
		public function save_tutors_profile_fields( $user_id ) {

			if ( !current_user_can( 'edit_user', $user_id ) ) return false;

			$to_save_about = sanitize_text_field($_POST['attest_about_author']);
			update_user_meta( $user_id, 'attest_about_author', $to_save_about );

			foreach ($this->excess_fields as $item) {

				$to_save = esc_url_raw(sanitize_text_field($_POST[$item[1]]));
				update_user_meta( $user_id, $item[1], $to_save );
			}
		}


		public function excess_fields() {

			return array(
				array( __('Facebook', 'attest'), 'attest_facebook' ),
				array( __('Instagram', 'attest'), 'attest_instagram' ),
				array( __('Linkedin', 'attest'), 'attest_linkedin' ),
				array( __('Twitter', 'attest'), 'attest_twitter' )
			);
		}


		public function list_pages() {

			$pages = get_pages();

			$data = array();
			foreach ($pages as $page) {
				$data[$page->ID] = $page->post_title;
			}

			return $data;
		}


		public function courses_list() {

			$args = array(
        'post_type' => 'attest_course',
        'post_status' => 'publish',
        'posts_per_page' => -1,
				'orderby' => 'ID',
        'order' => 'ASC',
				'fields' => array('ID', 'post_title'),
      );

      $uni_query = new WP_Query( $args );
			$posts = $uni_query->get_posts();
			$output = array();

			if (count($posts) > 0) {
				foreach($posts as $cpt) :
					$output[$cpt->ID] = $cpt->post_title;
				endforeach;
			}

			return $output;
		}


		public function get_activated_plugin() {

			$apl=get_option('active_plugins');
			$plugins=get_plugins();
			$activated_plugins=array();
			foreach ($apl as $p){
				if(isset($plugins[$p]['Name'])){
					array_push($activated_plugins, $plugins[$p]['Name']);
				}
			}

			return $activated_plugins;
		}
	}
} ?>
