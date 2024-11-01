<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Miscelleneous functions for front end
 */
if ( ! class_exists( 'ATTEST_LMS_COURSE_FUNCTIONS' ) ) {

	class ATTEST_LMS_COURSE_FUNCTIONS {


    public static $table = 'attest_announcements';
		public static $student_table = 'attest_students';


		public function get_paid_url($current_post_ID) {

			$link = false;

      $product_ID = get_post_meta( $current_post_ID, 'attest_product_related_to_course', true );
      if (false != $product_ID && ! empty($product_ID)) {

        $link = get_permalink($product_ID);
      }

      return $link;
		}


    public function view_course($course, $user) {

      $condition = false;

      $meta = get_post_meta($course, 'attest_enrolled_students', false);
      if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

        if (in_array($user, $meta[0])) {

          $condition = true;
        }
      }

      return $condition;
    }


		public function update_student($course, $user) {

			$meta = get_user_meta($user, 'attest_enrolled_courses', false);
			if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

				$courses = $meta[0];
				if (!in_array($course, array_column($courses, 'course'))) {

					array_push($courses, array(
						'course' => $course,
						'date' => time(),
					));
				}
			} else {

				$courses = array(
					array(
					'course' => $course,
					'date' => time(),
				));
			}

			$courses = array_map("unserialize", array_unique(array_map("serialize", $courses)));
			update_user_meta($user, 'attest_enrolled_courses', $courses);
		}


		public function count_registered_student($course) {

      $data = __('No', 'attest');

			$students_data = get_post_meta( $course, 'attest_course_students', false );
			$student_enrolled = ( isset($students_data[0]['enrolled']) ? $students_data[0]['enrolled'] : false );
			$student_enrolled_number = ( isset($students_data[0]['enrolled_number']) ? $students_data[0]['enrolled_number'] : false );

			if ($student_enrolled == 'auto') {

				$meta = get_post_meta($course, 'attest_enrolled_students', false);
	      if (false != $meta) {

					$data = count($meta[0]);
	      }
			} elseif ($student_enrolled == 'define') {

				$data = $student_enrolled_number;
			}

      return $data;
    }


    public function enroll_to_course($course_id) {

			$product_ID = get_post_meta( $course_id, 'attest_product_related_to_course', true );

			$message = $enrolled = false;

			$students_data = get_post_meta( $course_id, 'attest_course_students', false );
			$student_to_enroll = ( isset($students_data[0]['to_enroll']) ? $students_data[0]['to_enroll'] : false );
			$student_to_enroll_number = ( isset($students_data[0]['to_enroll_number']) ? $students_data[0]['to_enroll_number'] : false );
			$student_to_excess_error = ( isset($students_data[0]['excess_error']) ? $students_data[0]['excess_error'] : false );

      if ($student_to_enroll && isset($_POST['attest_enroll_course'])) {

        $course = (isset($_POST['attest_course_id']) ? sanitize_text_field($_POST['attest_course_id']) : false);
        $student = (isset($_POST['attest_student_id']) ? sanitize_text_field($_POST['attest_student_id']) : false);

        if (wp_verify_nonce( sanitize_text_field($_POST['attest_enroll_nonce']), 'attest_enroll_nonce_action' ) && $course && $student) {

          $meta = get_post_meta($course, 'attest_enrolled_students', false);
          if (false != $meta && is_array($meta[0]) && count($meta) > 0) {

						$existing = $meta[0];
						if ($student_to_enroll == 'auto') {

							$this->proceed_to_Checkout($product_ID);

							array_push($existing, $student);
							$enrolled = true;

						} elseif ($student_to_enroll == 'define') {

							if ($student_to_enroll_number > count($existing)) {

								$this->proceed_to_Checkout($product_ID);

								array_push($existing, $student);
								$enrolled = true;

							} else {

								$message = $student_to_excess_error;
								$enrolled = false;

							}
						}

						$student_list = array_values( array_unique( $existing ) );
          } else {

						$this->proceed_to_Checkout($product_ID);

						$student_list = array(0 => $student);
						$enrolled = true;
          }

					if (empty($product_ID)) {

						update_post_meta( $course, 'attest_enrolled_students', $student_list );

						//Update dates for enrolled students
						$dates_array = get_post_meta( $course, 'attest_student_dates', false );
						if (false != $dates_array && is_array($dates_array[0]) && count($dates_array[0]) > 0) {

							$student_date_array = $dates_array[0];
							$student_date_array[$student] = current_time('mysql');
						} else {
							$student_date_array = array($student => current_time('mysql'));
						}

						update_post_meta( $course, 'attest_student_dates', $student_date_array );

						$this->update_students_table($course, $student);
					}
        }
      }

			return array(
				'message' => $message,
				'enrolled' => $enrolled,
			);
    }


		public function update_students_table($course, $student) {

			global $wpdb;

			$table = $wpdb->prefix . self::$student_table;

			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT course_id FROM {$table} WHERE user_id = %d",
					$student
				));

			if (!empty($existing)) {

				$data = array(
					'course_id' => maybe_serialize(array_merge(maybe_unserialize($existing), $course)),
				);
				$where = array(
					'user_id' => $student,
				);

				$format = array('%s');
				$where_format = array('%d');

				$wpdb->update( $table, $data, $where, $format, $where_format );
			} else {

				$user_data = get_userdata($student);
				$name = $user_data->first_name . ' ' . $user_data->last_name;
				$email = $user_data->user_email;
				$city = '';
				$country = '';

				$data = array(
					'user_id'   => $student,
					'name'      => $name,
					'email'     => $email,
					'course_id' => maybe_serialize($course),
					'region'    => $country,
					'city'      => $city,
					'date'      => current_time('mysql'),
				);
				$format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');

				$wpdb->insert($table, $data, $format);
			}
		}


		public function proceed_to_Checkout($product_ID) {

			global $woocommerce;

			if (false != $product_ID && ! empty($product_ID)) {
				$woocommerce->cart->add_to_cart( $product_ID, $quantity=1 );

				$script =
				'<script type="text/javascript">
					function redirect() {
						window.location = \'' . get_permalink( wc_get_page_id( 'cart' ) ) . '\';
					}
					redirect();
				</script>';

				echo $script;
				exit;
			}
		}

		public function topic_links($current_post_ID) {

			$topics = array();

			$terms = wp_get_post_terms( $current_post_ID, 'topics' );
			foreach( $terms as $term ) {
				$topics[] = '<a href="' . esc_url_raw(get_term_link($term)) . '">' . esc_attr($term->name) . '</a>';
			}

			return $topics;
		}


		public function author_links($author_ID) {

			$data =array();
			$author_link_fields = $this->author_link_fields();
			foreach ($author_link_fields as $item) {

				$link = get_user_meta($author_ID, $item[1], true);
				if ($link) {

					$data[] = '<a href="' . esc_url_raw($link) . '">' . esc_attr($item[0]) . '</a>';
				}
			}

			$html = implode('</span> <span class="ml-4 attest-widget-author-link attest-widget-author-link-pad">', $data);
			return $html;
		}


		public function get_video($current_post_ID) {

			$content = false;

			$video_data = get_post_meta($current_post_ID, 'attest_intro_video', false);
			if (false != $video_data && count($video_data) > 0) {

				$video = $video_data[0];
				$type = (isset($video['type']) ? $video['type'] : false);
				$url = (isset($video['url']) ? $video['url'] : false);
				$embed = (isset($video['embed']) ? $video['embed'] : false);

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
				} elseif ($type == 'embed' && false != $embed) {

					$content = $embed;
				}
			}

			return $content;
		}


    public function get_course_language($current_post_ID) {

      $language_key = get_post_meta($current_post_ID, 'attest_language', false);
      $language_data = get_option('attest_lms_lns');
      foreach($language_data as $ln) {
        if ($ln['short'] == $language_key[0]) {
          $language = $ln['long'];
        }
      }
      if (false == $language) {
        $language = $language_key;
      }

      return $language;
    }


    public function get_course_announcement($current_post_ID) {

      $output = false;

      global $wpdb;

      $table_name = $wpdb->prefix . self::$table;
      $results = $wpdb->get_results(
                  $wpdb->prepare(
                    "SELECT title, description, date
                    FROM {$table_name}
                    WHERE related_course = %d AND active = 1
                    ORDER BY date DESC
                    LIMIT 1",
                    $current_post_ID)
                  );

      if (count($results) > 0) {

        $row = $results[0];
        $output['title'] = $row->title;
        $output['description'] = $row->description;
        $output['date'] = $this->date_format($row->date);
      }

      return $output;
    }


    public function get_course_curriculum($current_post_ID) {

      $output = false;
			$collect_first_lesson = false;
			$duration = 0;

      $meta = get_post_meta($current_post_ID, 'attest_curriculum', false);
      if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

        $sections = $meta[0];
        $count_section = count($sections);

        $lesson_count = 0;
        if ($count_section > 0) {

					$k = 0;
          foreach ($sections as $section) {

						unset($section['title']);
            $lesson_count += count($section[0]);
						if (count($section) > 0) {

							foreach ($section[0] as $lesson) {

								$lesson_id = ( isset($lesson['lesson_id']) ? $lesson['lesson_id'] : false );
								$duration_data = get_post_meta( $lesson_id, 'attest_lesson_duration', false );
								$h = (isset($duration_data[0]['h']) ? $duration_data[0]['h'] : false );
								$min = (isset($duration_data[0]['min']) ? $duration_data[0]['min'] : false );
								$sec = (isset($duration_data[0]['sec']) ? $duration_data[0]['sec'] : false );
								$duration += (($h * 3600) + ($min * 60) + $sec);

								if ($k == 0) {
									$collect_first_lesson = $lesson_id;
								}

								$k++;
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

				$output['first_lesson'] = $collect_first_lesson;
        $output['section'] = $count_section;
        $output['lesson'] = $lesson_count;
        $output['data'] = $sections;
      }

      return $output;
    }


		public function get_course_faq($current_post_ID) {

			$output = false;

      $meta = get_post_meta($current_post_ID, 'attest_faq', false);
      if (false != $meta && is_array($meta[0]) && count($meta[0]) > 0) {

				$faqs = $meta[0];
				if ( is_array( $faqs ) && count( $faqs ) > 0 ) {

					foreach ($faqs as $faq) {

						$output[] = $faq;
					}
				}
			}

			return $output;
		}


		public function get_course_price($current_post_ID) {

			if (function_exists('get_woocommerce_currency_symbol')) {
				$currency = get_woocommerce_currency_symbol();
			} else {
				$currency = '$';
			}

			$price_type = get_post_meta($current_post_ID, 'attest_course_price', true);
		  if (false != $price_type) {
		    if ($price_type == 'free') {

					$text = '<strong>' . __('FREE', 'attest') . '</strong>';
		    } elseif ($price_type == 'paid') {

					$price_amount_data = get_post_meta( $current_post_ID, 'attest_course_price_amount', false );
					$price_actual = (isset($price_amount_data[0]['actual']) ? esc_attr($price_amount_data[0]['actual']) : 0);
					$price_sale = (isset($price_amount_data[0]['sale']) ? esc_attr($price_amount_data[0]['sale']) : 0);
					$discount = round( ( ( ($price_actual - $price_sale) / $price_actual ) * 100 ), 0);

					if ($price_sale != 0) {
						$text = '<strong class="attest-price-actual">' . $currency . ' ' . esc_attr($price_sale) . '</strong>';
					} elseif ($price_sale == 0 && $price_actual != 0) {
						$text = '<strong class="attest-price-actual">' . $currency . ' ' . esc_attr($price_actual) . '</strong>';
					}
		    }
		  }

			if ($price_type == 'free') {
				$text .= '<p class="mt-0 mb-4 attest-price-discount">' . __('Requires registration', 'attest') . '</p>';
			} elseif ($price_type == 'paid') {
				if ($price_sale != 0) {
					$text .= '<p class="mt-0 mb-4 attest-price-discount"><strike>' . $currency . ' ' . esc_attr($price_actual) . '</strike>&nbsp;&nbsp;&nbsp;' . esc_attr($discount) . '% ' . __( 'off', 'attest' ) . '</p>';
				}
			}

			return $text;
		}


		public function get_course_audience($current_post_ID) {

			return $this->break_meta_to_new_line($current_post_ID, 'attest_audience');
		}


    public function get_course_features($current_post_ID) {

      return $this->break_meta_to_new_line($current_post_ID, 'attest_key_features');
    }


    public function get_course_requirements($current_post_ID) {

      return $this->break_meta_to_new_line($current_post_ID, 'attest_requirements');
    }


    public function break_meta_to_new_line($current_post_ID, $key) {

      $output = false;

      $meta = get_post_meta($current_post_ID, $key, false);
      if (false != $meta && strlen($meta[0]) > 0) {

				if ($meta[0] == 'Type Something...') {
					$output = false;
				} else {
					$output = explode("\n", $meta[0]);
				}
      }

      return $output;
    }


    public function date_format($datetime) {

      $timestamp = strtotime($datetime);
      $date_format = get_option('date_format');
	    $time_format = get_option('time_format');
	    $date = date("{$date_format}", $timestamp);

      return $date;
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


		public function author_link_fields() {

			return array(
				array( __('Facebook', 'attest'), 'attest_facebook' ),
				array( __('Instagram', 'attest'), 'attest_instagram' ),
				array( __('Linkedin', 'attest'), 'attest_linkedin' ),
				array( __('Twitter', 'attest'), 'attest_twitter' )
			);
		}
  }
}
