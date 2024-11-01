<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Miscelleneous functions for front end
 */
if ( ! class_exists( 'ATTEST_LMS_LESSON_FUNCTIONS' ) ) {

	class ATTEST_LMS_LESSON_FUNCTIONS {


    public static $table = 'attest_announcements';


    public function lesson_loaded($current_post_ID, $student) {

      $meta = get_post_meta($current_post_ID, 'attest_enrolled_students', false);
      if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

				$existing = $meta[0];
        array_push($existing, $student);
				$student_list = array_values( array_unique( $existing ) );
      } else {

				$student_list = array(0 => $student);
			}

      update_post_meta( $current_post_ID, 'attest_enrolled_students', $student_list );
    }


		public function get_video_type($current_post_ID) {

			$type = false;

			$video_data = get_post_meta($current_post_ID, 'attest_intro_video', false);
			if (false != $video_data && count($video_data) > 0) {
				$type = (isset($video['type']) ? $video['type'] : false);
			}

			return $type;
		}


		public function get_video($current_post_ID) {

			$content = false;

			$video_data = get_post_meta($current_post_ID, 'attest_intro_video', false);
			if (false != $video_data && count($video_data) > 0) {

				$video = $video_data[0];
				$type = (isset($video['type']) ? $video['type'] : false);
				$url = (isset($video['url']) ? $video['url'] : false);
				$embed = (isset($video['embed']) ? $video['embed'] : false);
				$wistia = (isset($video['wistia']) ? $video['wistia'] : false);

				if ($type == 'upload' && false != $url) {

					$content = '<video class="embed-responsive-item" controls><source src="' . $url . '" type="video/mp4"></video>';
				} elseif ($type == 'external_url' && false != $url) {

					$content = '<video class="embed-responsive-item" controls><source src="' . $url . '" type="video/mp4"></video>';
				} elseif ($type == 'youtube_url' && false != $url) {

					if (strpos($url, 'youtu.be') !== false) {
						$youtube = str_replace('youtu.be/', 'youtube.com/embed/', $url);
					} elseif (strpos($url, 'youtube.com') !== false) {
						$youtube = str_replace('watch?v=', 'embed/', $url);
					}
					$content = '<iframe class="embed-responsive-item" width="100%" height="400" src="' . $youtube . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
				} elseif ($type == 'vimeo_url' && false != $url) {

					$explode = explode('vimeo.com/', $url);
					$content = '<iframe class="embed-responsive-item" src="https://player.vimeo.com/video/' . $explode[1] . '" width="100%" height="400" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				} elseif ($type == 'wistia_code' && false != $wistia) {

					$content = $wistia;
				} elseif ($type == 'embed' && false != $embed) {

					$content = $embed;
				}
			}

			return $content;
		}


		public function get_related_course($current_post_ID) {

			$output = false;

			$meta = get_post_meta($current_post_ID, 'attest_course_related_to_lesson', true);
			if ($meta) {
				$output = $meta;
			}

			return $output;
		}


		public function nav_lessons($current_post_ID, $course) {

			$output = $found = $pre = $post = false;
			$list_lessons = array();

			$meta = get_post_meta($course, 'attest_curriculum', false);
			if (false != $meta && count($meta[0]) > 0) {
				$curriculum = $meta[0];

				if (count($curriculum) > 0) {
					$key = 0;
					foreach ($curriculum as $section) {

						unset($section['title']);
						foreach ($section[0] as $lesson) {

							$lesson_id = $lesson['lesson_id'];
							$list_lessons[$key] = $lesson_id;
							if ($lesson_id == $current_post_ID) {
								$found = $key;
							}
							$key++;
						}
					}
				}
			}

			if (isset($list_lessons[$found]) && ($current_post_ID == $list_lessons[$found])) {
				$pre = (isset($list_lessons[($found - 1)]) ? $list_lessons[($found - 1)] : false);
				$post = (isset($list_lessons[($found + 1)]) ? $list_lessons[($found + 1)] : false);
			}

			return array(
				'pre' => $pre,
				'post' => $post,
			);
		}


    public function get_course_curriculum($current_post_ID) {

      $output = false;
			$duration = 0;

      $meta = get_post_meta($current_post_ID, 'attest_curriculum', false);
      if (false != $meta && count($meta[0]) > 0) {

        $sections = $meta[0];
        $count_section = count($sections);

        $lesson_count = 0;
        if ($count_section > 0) {

          foreach ($sections as $section) {

						unset($section['title']);
            $lesson_count += count($section[0]);
						if (count($section) > 0) {

							foreach ($section as $lesson) {

								$lesson_id = ( isset($lesson[0]['lesson_id']) ? $lesson[0]['lesson_id'] : false );
								$duration_data = get_post_meta( $lesson_id, 'attest_lesson_duration', false );
								$h = (isset($duration_data[0]['h']) ? $duration_data[0]['h'] : false );
								$min = (isset($duration_data[0]['min']) ? $duration_data[0]['min'] : false );
								$sec = (isset($duration_data[0]['sec']) ? $duration_data[0]['sec'] : false );
								$duration += ($h * 3600) + ($min * 60) + $sec;
							}
						}
          }
        }
        $output = $meta;

				$show_duration = get_post_meta( $current_post_ID, 'attest_course_duration', true );
				if ($show_duration == '1') {
					$duration = $this->format_duration_curriculum($duration);
					$output['duration'] = $duration;
				} else {
					$output['duration'] = false;
				}

        $output['section'] = $count_section;
        $output['lesson'] = $lesson_count;
        $output['data'] = $sections;
      }

      return $output;
    }


		public function get_course_faq($current_post_ID) {

			$output = false;

      $meta = get_post_meta($current_post_ID, 'attest_faq', false);
      if (false != $meta && count($meta[0]) > 0) {

				$faqs = $meta[0];
				if ( is_array( $faqs ) && count( $faqs ) > 0 ) {

					foreach ($faqs as $faq) {

						$output[] = $faq;
					}
				}
			}

			return $output;
		}


		public function format_duration_curriculum($duration) {

			$output = false;

			if ($duration >= 3600) {

				$h = floor($duration / 3600);
				if ($h < 1) {
					$hour = false;
				} else {
					$hour = $h . 'h ';
				}

				$min = floor(($duration - floor($duration / 3600) * 3600) / 60);
				if ($min < 1) {
					$minute = false;
				} else {
					$minute = $min . 'm ';
				}

				$second = ($duration - (($h * 3600) + ($min * 60))) . 's ';

				$output = $hour . $minute . $second;

			} elseif ($duration >= 60 ) {

				$min = floor($duration / 60);
				if ($min < 1) {
					$minute = false;
				} else {
					$minute = $min . 'm ';
				}

				$sec = floor(($duration - floor($duration / 60) * 60));
				if ($sec < 1) {
					$second= false;
				} else {
					$second = $sec . 's ';
				}

				$output = $minute . $second;
			} else {

				$second = $duration . 's ';

				$output = $second;
			}

			return $output;
		}


		public function format_duration($lesson_ID) {

			$duration_data = get_post_meta( $lesson_ID, 'attest_lesson_duration', false );
			$h = (isset($duration_data[0]['h']) ? $duration_data[0]['h'] : false );
			$min = (isset($duration_data[0]['min']) ? $duration_data[0]['min'] : false );
			$sec = (isset($duration_data[0]['sec']) ? $duration_data[0]['sec'] : false );

			$hour = $minute = $second = false;

			if ($h != false || $min != false || $sec != false) {
				if ($h > 0) {
					$hour = $h . 'h ';
				}
				if ($min > 0) {
					$minute = $min . 'm ';
				}
				if ($sec > 0) {
					$second = $sec . 's';
				}

				$duration = $hour . $minute . $second;
			} else {
				$duration = false;
			}

			return $duration;
		}
  }
}
