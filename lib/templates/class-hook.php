<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template hooking class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_TEMPLATES' ) ) {

	class ATTEST_LMS_TEMPLATES {


    public $single_temp_course;
		public $single_temp_lesson;
		public $archive_temp_course;


    public function __construct($single_temp_course, $archive_temp_course, $single_temp_lesson) {

      $this->single_temp_course  = $single_temp_course;
			$this->archive_temp_course = $archive_temp_course;
			$this->single_temp_lesson  = $single_temp_lesson;

      add_filter('single_template', array($this, 'single_post_type'));
			add_filter('template_include', array($this, 'archive_post_type'));
    }


		public function archive_post_type( $template ) {

		  if ( is_post_type_archive('attest_course') || is_tax('topics') ) {
				if ( file_exists( $this->archive_temp_course ) ) {
            return $this->archive_temp_course;
        }
		  }

			return $template;
		}


    public function single_post_type($single) {

      global $post;

      if ( $post->post_type == 'attest_course' ) {
        if ( file_exists( $this->single_temp_course ) ) {
            return $this->single_temp_course;
        }
      }
			if ( $post->post_type == 'attest_lesson' ) {
        if ( file_exists( $this->single_temp_lesson ) ) {
            return $this->single_temp_lesson;
        }
      }

      return $single;
    }
  }
}
