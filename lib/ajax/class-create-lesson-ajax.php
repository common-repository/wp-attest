<?php
/**
 * Doing AJAX the WordPress way.
 * Use this class in admin or user side
 */
if ( ! defined( 'ABSPATH' ) ) exit;

//AJAX helper class
if ( ! class_exists( 'ATTEST_LMS_NEW_LESSON_AJAX' ) ) {

	final class ATTEST_LMS_NEW_LESSON_AJAX {


		// Add basic actions
		public function __construct() {

			add_action( 'wp_ajax_attest_new_lesson', array( $this, 'attest_new_lesson' ) );
			add_action( 'wp_ajax_nopriv_attest_new_lesson', array( $this, 'attest_new_lesson' ) );
		}


		//The data processor
		public function attest_new_lesson() {

			$title = sanitize_text_field($_POST['title']);
			$course_id = intval(sanitize_text_field($_POST['course_id']));
      $nonce = sanitize_text_field($_POST['nonce']);

			if ( !isset( $nonce ) || !wp_verify_nonce( $nonce, ATTEST_LMS_FILE ) ) {

        $response = array( 'alert' => __('Are you a hacker?', 'attest' ));
      } else {

        $args = array(
			   'post_content' => '',
			   'post_title' => $title,
			   'post_status' => 'publish',
			   'post_type' => 'attest_lesson'
			  );
			  $post_id = wp_insert_post($args);

        if (!is_wp_error($post_id)) {

					update_post_meta($post_id, 'attest_course_related_to_lesson', $course_id);

          $get_title = get_the_title($post_id);
					$get_permalink = get_permalink($post_id);
          $get_edit_link = get_edit_post_link($post_id);

          $response = array(
            'alert' => 1,
            'id' => esc_attr($post_id),
            'title' => esc_attr($get_title),
            'url' => esc_url_raw($get_permalink),
            'edit_url' => esc_url_raw($get_edit_link),
          );
        } else {

          $response = array( 'alert' => __('Lesson creation failed. Try again!', 'attest') );
        }
      }

      echo json_encode( $response );
      wp_die();
		}
	}
} ?>
