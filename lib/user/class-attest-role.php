<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_ROLES' ) ) {

	class ATTEST_LMS_ROLES {


    public static $student_capabilities = array(
      'read'         => false,
      'edit_posts'   => false,
      'manage_options' => false
    );
		public static $teacher_capabilities = array(
      'read'         => true,
      'edit_posts'   => true,
			'upload_files' => true,
      'manage_options' => false
    );


    public function __construct() {

			add_role( 'attest_student', __( 'Student', 'attest' ), self::$student_capabilities);
			add_role( 'attest_tutor', __( 'Tutor', 'attest' ), self::$teacher_capabilities);
    }
  }
}
