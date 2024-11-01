<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Miscelleneous functions for front end
 */
if ( ! class_exists( 'ATTEST_LMS_COURSE_ARCHIVE_FUNCTIONS' ) ) {

	class ATTEST_LMS_COURSE_ARCHIVE_FUNCTIONS {

		/**
		 * Get the data if user is already enrolled
		 */
    public function get_enrollment_data($ID) {

		  $enrolled = false;
			$completed = false;
		  $current_user = get_current_user_id();
		  $requires = true;
			$currency = currency_symbol();

		  $price_type = get_post_meta($ID, 'attest_course_price', true);
		  if (false != $price_type) {
		    if ($price_type == 'free') {
		      $text = __('FREE', 'attest');
		    } elseif ($price_type == 'paid') {
		      $price_amount_data = get_post_meta( $ID, 'attest_course_price_amount', false );
					$price_actual = (isset($price_amount_data[0]['actual']) ? esc_attr($price_amount_data[0]['actual']) : 0);
					$price_sale = (isset($price_amount_data[0]['sale']) ? esc_attr($price_amount_data[0]['sale']) : 0);
					$discount = round( ( ( ($price_actual - $price_sale) / $price_actual ) * 100 ), 0);

					if ($price_sale != 0) {
						$text = $currency . ' ' . $price_sale;
					} elseif ($price_sale == 0 && $price_actual != 0) {
						$text = $currency . ' ' . $price_actual;
					}
		    }
		  }

		  $enrolled_data = get_post_meta($ID, 'attest_enrolled_students', false);
		  if (is_user_logged_in() && false != $enrolled_data && is_array($enrolled_data) && count($enrolled_data) > 0) {

		    $existing = (array) $enrolled_data[0];
		    if (in_array($current_user, $existing)) {
		      $text = __('Enrolled', 'attest');
		      $requires = false;
		      $enrolled = true;
		    }
		  }

		  $completed_data = get_post_meta($ID, 'attest_completed_students', false);
		  if (false != $completed_data && count($completed_data) > 0) {

		    $existing = $completed_data[0];
		    if (in_array($current_user, $existing)) {
		      $text = __('Completed', 'attest');
		      $requires = false;
					$completed = true;
		    }
		  }

		  $html = '<p class="attest-course-price"><strong class="attest-archive-meta-prime">' . $text . '</strong></p>';
			if ($price_type == 'free') {
				$html .= ( $requires ? '<small class="attest-archive-meta-sub">' . __('Requires registration', 'attest') . '</small>' : false );
			} elseif ($price_type == 'paid') {
				if ($price_sale != 0) {
					$html .= ( $requires ? '<small class="attest-archive-meta-sub"><strike>' . $currency . ' ' . $price_actual . '</strike>&nbsp;&nbsp;&nbsp;' . $discount . '% ' . __( 'off', 'attest' ) . '</small>' : false );
				}
			}

			if (false != $completed || false != $enrolled) {
				$dates_array = get_post_meta( $ID, 'attest_student_dates', false );
				if (false != $dates_array && is_array($dates_array[0]) && count($dates_array[0]) > 0) {
					$date = (isset($dates_array[0][$current_user]) ? date( get_option('date_format'), strtotime($dates_array[0][$current_user]) ) : false );
					$html .= ( !$requires ? '<small class="attest-archive-meta-sub">' . $date . '</small>' : false );
				}
			}

		  return array(
		    'enrolled' => $enrolled,
		    'html' => $html,
		  );
		}

		/**
		 * The first lesson of a specified course
		 */
		function get_course_first_lesson($course_id) {

		  $output = false;

		  $meta = get_post_meta($course_id, 'attest_curriculum', false);
		  if (false != $meta && count($meta[0]) > 0) {

		    $sections = $meta[0];
		    $count_section = count($sections);

		    $lesson_count = 0;
		    if (is_array($sections) && $count_section > 0) {

		      $k = 0;
		      foreach ($sections as $section) {

		        unset($section['title']);
		        $lesson_count += count($section[0]);
		        if (count($section) > 0) {

		          foreach ($section as $lesson) {

		            $lesson_id = ( isset($lesson[0]['lesson_id']) ? $lesson[0]['lesson_id'] : false );

		            if ($k == 0) {
		              $collect_first_lesson = $lesson_id;
		            }

		            $k++;
		          }
		        }
		      }

		      $output = $collect_first_lesson;
		    }
		  }

		  return $output;
		}


		public function navigation($max_num_pages) {

			$big = 999999999; // need an unlikely integer
			$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);

			$links = paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?page=%#%',
				'current' => max( 1, $paged ),
				'total' => $max_num_pages,
				'prev_next' => false,
				'before_page_number' => '<span class="btn-lg btn-secondary attest-nav-button">',
				'after_page_number' => '</span>',
				'type' => 'array'
			) );

			$nav =
			'<div class="row courses-nav">
				<div class="col-md-12">
				<div class="btn-toolbar justify-content-between" role="toolbar">
  				<div class="btn-group" role="group">';

			$nav .= ((is_array($links) && count($links) > 0 ) ?implode('&nbsp;', $links) : false );

			$nav .= '</div></div></div></div>';

			return $nav;
		}


		public function currency_symbol() {

			if (function_exists('get_woocommerce_currency_symbol')) {
				$currency = get_woocommerce_currency_symbol();
			} else {
				$currency = '$';
			}

			return $currency;
		}
  }
}
