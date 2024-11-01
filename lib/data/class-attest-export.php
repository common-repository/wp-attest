<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Export data
 */
if ( ! class_exists( 'ATTEST_LMS_EXPORT' ) ) {

	final class ATTEST_LMS_EXPORT {


		protected static $course_meta_key = array(
			'attest_announcement',
			'attest_audience',
			'attest_curriculum',
			'attest_faq',
			'attest_key_features',
			'attest_language',
			'attest_requirements',
			'attest_course_price',
			'attest_course_assessment',
			'attest_course_duration',
			'attest_course_featured',
			'attest_course_students',
			'attest_intro_video',
			'attest_course_related_to_lesson'
		);
		protected static $lesson_meta_key = array(
			'attest_faq',
			'attest_lesson_duration',
			'attest_lesson_assessment',
			'attest_intro_video'
		);
		protected static $course_tax = array(
			'post_tag',
			'topics',
			'difficulty'
		);
		protected static $table_name = 'attest_announcements';
		protected static $email_keys = array(
			'attest_email_announcement_subject',
			'attest_email_announcement_body',
			'attest_target_new_course',
			'attest_email_new_course_subject',
			'attest_email_new_course_body',
			'attest_email_completed_course_subject',
			'attest_email_completed_course_body',
			'attest_ok_updated_course',
			'attest_email_updated_course_subject',
			'attest_email_updated_course_body',
			'attest_email_continue_course_time',
			'attest_email_continue_course_subject',
			'attest_email_continue_course_body',
		);


		public function __construct() {

			$post_type = ( isset($_GET['post_type']) ? $_GET['post_type'] : false );
			$page = ( isset($_GET['page']) ? $_GET['page'] : false );
			$tab = ( isset($_GET['tab']) ? $_GET['tab'] : false );
			$export = ( isset($_GET['export']) ? $_GET['export'] : false );

			if (($post_type == 'attest_course') && ($page == 'settings') && ($tab == 'data') && ($export == 'true')) {

				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

				$datetime = date('F-j-Y:g-i-a', strtotime('now'));
				$filename = 'WPAttest-Backup-' . $datetime . ".json";

				$data = $this->fetch_data();
				$this->send_headers($filename);
        echo $this->export($data);
        exit();
			}
		}


		public function fetch_data() {

			$courses				= $this->get_courses();
			$lessons				= $this->get_lessons();
			$post_meta			= $this->get_post_meta($courses, $lessons);
			$taxonomy				= $this->get_associated_taxonomy($courses);
			$tax_data				= $this->get_all_tax();
			$announcements	= $this->get_announcements();
			$emails					= $this->get_email_templates();

			return json_encode( array(
				'courses' 			=> $courses,
				'lessons' 			=> $lessons,
				'post_meta' 		=> $post_meta,
				'associated_tax'=> $taxonomy,
				'taxonomy'			=> $tax_data,
				'announcements' => $announcements,
				'emails' 				=> $emails,
			) );
		}


		protected function get_courses() {

			$args = array(
        'post_type' => 'attest_course',
        'posts_per_page' => -1,
				'orderby' => 'ID',
        'order' => 'ASC'
      );

      $uni_query = new WP_Query( $args );
			$posts = $uni_query->get_posts();

			return $posts;
		}


		protected function get_lessons() {

			$args = array(
        'post_type' => 'attest_lesson',
        'posts_per_page' => -1,
				'orderby' => 'ID',
        'order' => 'ASC'
      );

      $uni_query = new WP_Query( $args );
			$posts = $uni_query->get_posts();

			return $posts;
		}


		protected function get_post_meta($courses, $lessons) {

			$courses_data = array();
			if (is_array($courses) && count($courses) > 0) {
				foreach ($courses as $course) {
					$courses_meta = array();
					foreach (self::$course_meta_key as $key) {
						$courses_meta[$key] = get_post_meta($course->ID, $key);
					}
					$courses_data[$course->ID] = $courses_meta;
				}
			}

			$lesson_data = array();
			if (is_array($lessons) && count($lessons) > 0) {
				foreach ($lessons as $lesson) {
					$lesson_meta = array();
					foreach (self::$lesson_meta_key as $key) {
						$lesson_meta[$key] = get_post_meta($lesson->ID, $key);
					}
					$lesson_data[$lesson->ID] = $lesson_meta;
				}
			}

			return array(
				'course' => $courses_data,
				'lesson' => $lesson_data,
			);
		}


		protected function get_all_tax() {

			$tax_data = array();
			foreach (self::$course_tax as $key) {
				$tax_data[$key] = get_terms( $key, array(
					'hide_empty' => false,
				) );
			}

			return $tax_data;
		}


		protected function get_associated_taxonomy($courses) {

			$taxonomy = array();
			foreach ($courses as $course) {
				foreach (self::$course_tax as $key) {
					$tax_data[$key] = wp_get_object_terms( $course->ID, $key, array( 'fields' => 'names' ) );
				}
				$taxonomy[$course->ID] = $tax_data;
			}

			return $taxonomy;
		}


		protected function get_announcements() {

			global $wpdb;
			$table = self::$table_name;

			$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}{$table}", 'ARRAY_A' );

			return $result;
		}


		protected function get_email_templates() {

			$output = array();
			foreach (self::$email_keys as $key) {
				$output[$key] = get_option($key);
			}

			return $output;
		}


		public function export($content) {

			ob_start();

      $define = fopen("php://output", 'w');
      fwrite($define, $content);
      fclose($define);

      return ob_get_clean();
		}


		public function send_headers($filename) {

      // disable caching
      $now = gmdate("D, d M Y H:i:s");
      header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
      header("Last-Modified: {$now} GMT");

      // force download
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream");
      header("Content-Type: application/download");
			header('Content-Type: application/json');

      // disposition / encoding on response body
      header("Content-Disposition: attachment;filename={$filename}");
      header("Content-Transfer-Encoding: binary");
    }
  }
}
