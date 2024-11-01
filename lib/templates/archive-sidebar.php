<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_COURSES_SIDEBAR' ) ) {

	class ATTEST_LMS_COURSES_SIDEBAR {

		//Add a sidebar
		public function __construct() {

		    register_sidebar( array(
		        'name'          => __( 'Attest course archive sidebar', 'attest' ),
		        'id'            => 'attest_course_archive_sidebar',
		        'description'   => __( 'Widgets in this area will be shown on courses archive by Attest.', 'attest' ),
		        'before_widget' => '<div id="%1$s" class="widget %2$s">',
		        'after_widget'  => '</div>',
		        'before_title'  => '<h2 class="widgettitle">',
		        'after_title'   => '</h2>',
		    ) );
		}
  }
}
