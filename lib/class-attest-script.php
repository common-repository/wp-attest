<?php
/**
 * Add scripts to the plugin. CSS and JS.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_SCRIPT' ) ) {

	final class ATTEST_LMS_SCRIPT {


		public $page;
		public $action;
		public $translation_array;
		public $curriculum_array;
		public $faq_array;


		public function __construct() {

			$this->page = (isset($_GET['page']) ? sanitize_text_field($_GET['page']) : false);
			$this->action = (isset($_GET['action']) ? sanitize_text_field($_GET['action']) : false);
			$this->post_type = (isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : false);
			$this->translation_array = array(
					'title' => __( 'Insert a video', 'attest' ),
					'button' => __('Insert', 'attest'),
			);
			$this->curriculum_array = array(
				'url' => admin_url( 'admin-ajax.php' ),
				'lesson_error' => __('Something Went wrong', 'attest'),
				'lesson_name' => __('Lesson', 'attest'),
				'saving_text' => __('Saving', 'attest') . '...',
				'saved_text' => __('Save', 'attest'),
				'delete_lesson' => __('Do you really want to delete the lesson?', 'attest'),
				'delete_section' => __('Do you really want to delete the section?', 'attest'),
			);
			$this->faq_array = array(
				'delete' => __('Do you really want to delete the question?', 'attest'),
			);

			add_action( 'admin_head', array( $this, 'data_table_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'modal_script' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'backend_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'metabox_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		}


		// Table css for settings page data tables
		public function data_table_css() {

			$table_css = '<style type="text/css">
							.announcements .column-active { width: 10%; }
						</style>';

			echo $table_css;
		}


		//Add modal script to announcement page
		public function modal_script() {

			if($this->page == 'announcement') {

				wp_enqueue_script( 'thickbox' );
    		wp_enqueue_style( 'thickbox' );
			}
		}


		// Enter scripts into pages
		public function backend_scripts() {

			if ($this->page == 'announcement') {
				$page = sanitize_text_field($_GET['page']);
				wp_enqueue_script( 'announcement-js', ATTEST_LMS_JS . 'admin-announcement.js', array('jquery'), '0.3' );
			}

			// Set condition to add script
			if ( (isset( $_GET['post'] ) && $this->action == 'edit') || $this->post_type == 'attest_course' ) {
				wp_enqueue_script( 'jquery-ui', 'https://code.jquery.com/ui/1.10.1/jquery-ui.js', array('jquery'), '1.2' );
				wp_enqueue_script( 'curriculum-js', ATTEST_LMS_JS . 'admin-curriculum.js', array('jquery', 'jquery-ui'), '0.3' );
				wp_localize_script( 'curriculum-js', 'curriculum_ajax', $this->curriculum_array );

				wp_enqueue_style( 'curriculum-css', ATTEST_LMS_CSS . 'admin-curriculum.css' );
				wp_enqueue_style( 'curriculum-icon-css', 'https://fonts.googleapis.com/icon?family=Material+Icons' );
			}
		}


		public function metabox_scripts() {

			if ( ((isset( $_GET['post'] ) && $this->action == 'edit') || $this->post_type == 'attest_course'  || $this->post_type == 'attest_lesson') && $this->page != 'announcement' ) {

				wp_enqueue_script('media-upload');

				wp_enqueue_script('thickbox');

		    wp_register_script('video-admin', ATTEST_LMS_JS.'admin-video.js', array('jquery', 'media-upload','thickbox'));
				wp_localize_script( 'video-admin', 'video_modal', $this->translation_array );
				wp_enqueue_script('video-admin');

				wp_register_script('assessment-admin', ATTEST_LMS_JS.'admin-assessment.js', array('jquery'));
				wp_enqueue_script('assessment-admin');

				wp_register_script('faq-admin', ATTEST_LMS_JS.'admin-faq.js', array('jquery'));
				wp_enqueue_script('faq-admin');
				wp_localize_script( 'faq-admin', 'data', $this->faq_array );

				wp_register_script('easytabs-admin', ATTEST_LMS_JS.'jquery.easytabs.min.js', array('jquery'));
				wp_enqueue_script('easytabs-admin');

				wp_register_script('hashchange-admin', ATTEST_LMS_JS.'jquery.hashchange.min.js', array('jquery'));
				//wp_enqueue_script('hashchange-admin');

				wp_register_script('settings-admin', ATTEST_LMS_JS.'admin-settings.js', array('easytabs-admin'));//, 'hashchange-admin'));
				wp_enqueue_script('settings-admin');

				wp_enqueue_style('thickbox');
				wp_enqueue_style( 'settings-css', ATTEST_LMS_CSS . 'admin-settings.css' );
			}
		}


		// Enter scripts into pages
		public function frontend_scripts() {

			wp_enqueue_script( 'wp-attest-bootstrap-js', ATTEST_LMS_JS . 'bootstrap.min.js', array('jquery') );
			wp_enqueue_script( 'wp-attest-register-js', ATTEST_LMS_JS . 'wp-attest.js', array('jquery', 'wp-attest-bootstrap-js') );

			wp_enqueue_style( 'wp-attest-bootstrap-css', ATTEST_LMS_CSS . 'bootstrap.css' );
			wp_enqueue_style( 'wp-attest-plugin-css', ATTEST_LMS_CSS . 'wp-attest.css' );
			wp_enqueue_style( 'wp-attest-icon-css', 'https://fonts.googleapis.com/icon?family=Material+Icons' );
		}
	}
} ?>
