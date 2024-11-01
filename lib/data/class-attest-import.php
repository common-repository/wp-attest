<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Export data
 */
if ( ! class_exists( 'ATTEST_LMS_IMPORT' ) ) {

	final class ATTEST_LMS_IMPORT {


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

			if (($post_type == 'attest_course') && ($page == 'settings') && ($tab == 'data')) {
				if (isset($_POST['attest_import_data_execute'])) {

					$file = $this->upload_controller();
					if($file) {
						$data = file_get_contents($file);
						$content = json_decode($data, 1);
						$this->handle_data($content);
					}
				}
			}
		}


		public function handle_data($content) {

			$courses_data = (isset($content['courses']) ? $content['courses'] : false);
			if ($courses_data) {
				$courses = $this->insert_posts($courses_data, 'attest_course');
			}

			$lesson_data = (isset($content['lessons']) ? $content['lessons'] : false);
			if ($lesson_data) {
				$lessons = $this->insert_posts($lesson_data, 'attest_lesson');
			}

			if (count($courses) > 0 && is_array($lessons)) {
				$announcements = $this->insert_announcements($content, $courses);
				$post_meta = $this->insert_post_meta($content, $courses, $lessons, $announcements);
				$taxonomy = $this->insert_all_taxonomy($content);
				$taxonomy = $this->insert_associated_taxonomy($content, $courses);
				$emails = $this->insert_email_templates($content);
			}

			add_action('admin_notices', array($this, 'success_notice'));
		}


		protected function insert_posts($posts_data, $type) {

			$posts = array();

				foreach ($posts_data as $post) {

					$post_id_old = (isset($post['ID']) ? $post['ID'] : false);
					$post_content = (isset($post['post_content']) ? $post['post_content'] : false);
					$post_title = (isset($post['post_title']) ? $post['post_title'] : false);
					$post_status = (isset($post['post_status']) ? $post['post_status'] : false);

					$args = array(
						'post_title' => $post_title,
						'post_content' => $post_content,
						'post_status' => $post_status,
						'post_type' => $type
					);

					$post_id = wp_insert_post($args);
					if (!is_wp_error($post_id)) {
						$posts[$post_id_old] = $post_id;
					}
				}


			return $posts;
		}


		protected function insert_post_meta($content, $courses, $lessons, $announcements) {

			$output = false;
			$meta_data = array();

			$post_meta_course = (isset($content['post_meta']['course']) ? $content['post_meta']['course'] : false);
			$post_meta_lesson = (isset($content['post_meta']['lesson']) ? $content['post_meta']['lesson'] : false);

			if ($post_meta_course) {

				foreach ($post_meta_course as $course_id => $meta) {

					$modified_course_id = (isset($courses[$course_id]) ? $courses[$course_id] : false);
					if ($modified_course_id) {

						foreach (self::$course_meta_key as $key) {

							$meta_value = (isset($meta[$key][0]) ? $meta[$key][0] : false);
							if ($key == 'attest_announcement') {
								$meta_value = (isset($announcements[$modified_course_id]) ? $announcements[$modified_course_id] : false);
							}
							if ($key == 'attest_course_related_to_lesson') {
								$meta_value = $modified_course_id;
							}
							$meta_data[$key] = update_post_meta( $modified_course_id, $key, $meta_value );
						}
					}
				}

				$output = array_sum($meta_data);
			}

			if ($post_meta_lesson) {

				foreach ($post_meta_lesson as $lesson_id => $meta) {

					$modified_lesson_id = (isset($lessons[$lesson_id]) ? $lessons[$lesson_id] : false);
					if ($modified_lesson_id) {

						foreach (self::$lesson_meta_key as $key) {

							$meta_value = (isset($meta[$key][0]) ? $meta[$key][0] : false);
							$meta_data[$key] = update_post_meta( $modified_lesson_id, $key, $meta_value );
						}
					}
				}

				$output = array_sum($meta_data);
			}

			return $output;
		}


		protected function insert_all_taxonomy($content) {

			$taxonomy = (isset($content['taxonomy']) ? $content['taxonomy'] : false);

			if ($taxonomy) {
				foreach ($taxonomy as $key => $tax) {

					foreach ($tax as $item) {

						$title = (isset($item['name']) ? sanitize_text_field($item['name']) : false);
						$slug = (isset($item['slug']) ? sanitize_text_field($item['slug']) : false);
						$description = (isset($item['description']) ? sanitize_text_field($item['description']) : false);

						wp_insert_term( $title, $key,
							array(
								'description' => $description,
								'slug'        => $slug,
							) );
					}
				}
			}
		}


		protected function insert_associated_taxonomy($content, $courses) {

			$output = false;
			$taxonomy = (isset($content['associated_tax']) ? $content['associated_tax'] : false);

			if ($taxonomy) {

				$tax_data = array();
				foreach ($taxonomy as $course_id => $tax) {

					$modified_course_id = (isset($courses[$course_id]) ? $courses[$course_id] : false);
					if ($modified_course_id) {
						foreach (self::$course_tax as $key) {

							$tax_value = (isset($tax[$key]) ? $tax[$key] : false);
							$tax_data[$key] = wp_set_object_terms( $modified_course_id, $tax_value, $key );
						}
					}
				}
				$output = array_sum($tax_data);
			}

			return $output;
		}


		protected function insert_announcements($content, $courses) {

			$insert = false;

			$announcements = (isset($content['announcements']) ? $content['announcements'] : false);

			global $wpdb;
			$table = self::$table_name;

			if ($announcements) {

				$insert = array();
				foreach ($announcements as $announcement) {

					$title = (isset($announcement['title']) ?  sanitize_text_field($announcement['title']): false);
					$description = (isset($announcement['description']) ? sanitize_text_field($announcement['description']) : false);
					$related_course = (isset($announcement['related_course']) ? sanitize_text_field($announcement['related_course']) : false);
					$modified_related_course = (isset($courses[$related_course]) ? $courses[$related_course] : false);

					$data = array(
						'title'						=> $title,
						'description'			=> $description,
						'related_course'	=> $modified_related_course,
						'active'					=> 0,
						'trigger_email'		=> 0,
						'date'						=> current_time('mysql'),
					);

					if ($title && $description) {
						$insert_ID = $wpdb->insert( $wpdb->prefix.$table, $data, array('%s','%s','%d','%d','%d','%s') );
						$get_ID_data = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}{$table} ORDER BY date DESC LIMIT 1", 'ARRAY_A');
						$insert[$modified_related_course] = (isset($get_ID_data[0]) ? $get_ID_data[0] : fasle);
					}
				}
			}

			return $insert;
		}


		protected function insert_email_templates($content) {

			$ok = false;
			$emails = ( isset($content['emails']) ? $content['emails'] : false );

			if ($emails) {
				foreach (self::$email_keys as $key) {
					$data = (isset($emails[$key]) ? $emails[$key] : false);
					$output[] = $data;
					update_option($key, sanitize_text_field($data));
				}

				$ok = array_sum($output);
			}

			return $ok;
		}


		public function success_notice() { ?>

			<div class="notice notice-success is-dismissible">
				<p><strong><?php _e( 'Import successfull.', 'attest' ); ?></strong></p>
		 	</div>
		<?php
		}


		public function invalid_file() { ?>

			<div class="notice notice-error is-dismissible">
				<p><strong><?php _e( 'File type is invalid.', 'attest' ); ?></strong></p>
		 	</div>
		<?php
		}


		// Manage the Upload file
		public function upload_controller() {

			$upload_file = false;
			$file = $_FILES['attest_import_data_upload'];
			$type = $file['type'];

			// Check in your file type
			if( $type == 'application/json' ) {

				$upload_file = $file['tmp_name'];
			} else {

				add_action('admin_notices', array($this, 'invalid_file'));
			}

			return $upload_file;
		}
  }
}
