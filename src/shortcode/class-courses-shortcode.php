<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode class for rendering in front end
 */
if ( ! class_exists( 'ATTEST_LMS_COURSES_SHORTCODE' ) ) {

	class ATTEST_LMS_COURSES_SHORTCODE {

		protected $query;
		protected $number_courses;
		protected $functions;

		public function __construct() {

			global $wp_query;

			$this->functions = new ATTEST_LMS_COURSE_ARCHIVE_FUNCTIONS();

			$this->number_courses = get_option('attest_number_courses');

			$plugins = $this->get_activated_plugin();
			if( in_array('WP Attest PRO - WooCommerce', $plugins) ) {
				$this->pro_installed = true;
			} else {
				$this->pro_installed = false;
			}

			add_shortcode( 'wp_attest_courses', array( $this, 'courses_cb' ) );
			add_shortcode( 'wp_attest_account', array( $this, 'account_cb' ) );

			$account_id = get_option('attest_template_my_account');

			if (isset($wp_query->post->ID)) {

				$post_id = $wp_query->post->ID;
				if (! empty($account_id) && $post_id == $account_id) {
					add_filter( 'body_class', array( $this, 'body_classes' ) );
				}
			}
		}


		public function body_classes( $classes ) {

		    $classes[] = 'woocommerce-account';

		    return $classes;

		}


		public function courses_cb($atts) {

			$data = shortcode_atts( array(
								'thumbnail' => true,
								'author' => true,
							), $atts );

			$is_account = false;

			return $this->shortcode_html($data, $is_account);
		}


		public function account_cb($atts) {

			$data = shortcode_atts( array(
								'thumbnail' => true,
								'author' => true,
							), $atts );

			$is_account = true;

			return $this->shortcode_html($data, $is_account);
		}


		/**
		 * Shortcode Display
		 */
		public function shortcode_html($data, $is_account) {

			if (false != $is_account && ! is_user_logged_in()) {
				return;
			}

			global $wp_query;
			$post_title = strtolower( str_replace( ' ', '_', get_the_title($wp_query->post->ID) ) );

			$query = $this->get_posts();
			$posts = $query['posts'];
			$courses = $this->filter_query($posts, $is_account);

			$body = '';

				if ($is_account && $this->pro_installed) {
					$body .= '<div class="woocommerce"><nav class="woocommerce-MyAccount-navigation"><ul>';
					foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
						$body .= '<li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link-' . $endpoint . ($endpoint == $post_title ? ' is-active' : false) . '">
						<a href="' . esc_url( wc_get_account_endpoint_url( $endpoint ) ) . '">' . esc_html( $label ) . '</a>
						</li>';
					endforeach;
					$body .= '</ul></nav><div class="' . ((!$is_account || !$this->pro_installed) ? 'col-md-8' : 'woocommerce-MyAccount-content') . '">';
				}

        if (is_array($courses) && count($courses) > 0) :
          foreach ( $courses as $post ) :

					$ID = $post->ID;
					$author_id = $post->post_author;
					$featured = get_post_meta( $ID, 'attest_course_featured', true);
					$enrollment_data = $this->functions->get_enrollment_data($ID);
					$first_lesson = $this->functions->get_course_first_lesson($ID);

					$body .= '<div class="row attest-course-item attest-course-item-shadow attest-course-item-radius course-' . $ID . (($featured == '1' && !is_user_logged_in()) ? ' course-featured' : false) . '">';

						if ($data['thumbnail']) :
							$body .= '<div class="col-md-4 attest-course-thumb attest-course-thumb-border-radius" style="background-image: url(\'' . esc_url(get_the_post_thumbnail_url( $ID, 'post-thumbnail' )) . '\');">' .
								( ($featured == '1' && !is_user_logged_in()) ?
									'<span class="badge badge-featured">' . __('FEATURED', 'attest') . '</span>'
								: false ) .
							'</div>';
						endif;

						$body .=
						'<div class="col-md-' . (false != $data['thumbnail'] ? '8' : '12') . '">
            	<div class="media-body attest-course-item-content">
              	<a class="attest-course-link" href="' . ((false != $enrollment_data['enrolled']) ? esc_url_raw(get_permalink($first_lesson)) : esc_url_raw(get_permalink($ID)) ) . '">
									<h4 class="mt-0 attest-course-title">' . esc_attr(get_the_title($ID)) . '</h4>
								</a>
								<div class="course-meta">';

									if (false != $data['author']) :
										$body .= '<span>' . __('Created by', 'attest') . '&nbsp;' .  get_the_author_meta( 'display_name' , $author_id ) . '</span>';
									endif;

										$body .= $enrollment_data['html'];

								$body .=
								'</div>
            	</div>
						</div>
          </div>';

				endforeach;
        endif;

			$body .= $this->functions->navigation($query['max_pages']);

			$body .= ($is_account ? '</div>' : false);

			return $body;
		}


		public function get_price_data($post_id) {

			$price_type = get_post_meta($post_id, 'attest_course_price', true);
		  if (false != $price_type) {
		    if ($price_type == 'free') {
		      $text = __('FREE', 'attest');
		    } elseif ($price_type == 'paid') {
		      $price_amount_data = get_post_meta( $post_id, 'attest_course_price_amount', false );
					$price_actual = (isset($price_amount_data[0]['actual']) ? esc_attr($price_amount_data[0]['actual']) : 0);
					$price_sale = (isset($price_amount_data[0]['sale']) ? esc_attr($price_amount_data[0]['sale']) : 0);
					$discount = round( ( ( ($price_actual - $price_sale) / $price_actual ) * 100 ), 0);

					if ($price_sale != 0) {
						$text = '$ ' . $price_sale;
					} else if ($price_sale == 0 && $price_actual != 0) {
						$text = '$ ' . $price_actual;
					}
		    }
		  }

			$html = '<p class="attest-course-price"><strong>' . $text . '</strong></p>
			<small>' . __('Requires registration', 'attest') . '</small>';

			return $html;
		}


		public function filter_query($query, $is_account) {

			$current_user = get_current_user_id();

			if (false != $is_account) {
				foreach ($query as $key => $post) {

					$students = get_post_meta($post->ID, 'attest_enrolled_students', false);
					if (false != $students && isset($students[0]) && is_array($students[0])) {
						if (!in_array($current_user, $students[0])) {
							unset($query[$key]);
						}
					} else {
						unset($query[$key]);
					}
				}
			}

			return $query;
		}


		public function get_posts() {

			$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);
			$offset = ( $paged - 1 ) * $this->number_courses;

			$args = array(
							'post_type'      => 'attest_course',
							'posts_per_page' => $this->number_courses,
							'orderby'        => array(
								'attest_course_featured' => 'DESC'
							),
							'count_total'    => true,
							'paged'          => $paged,
							'offset'         =>  $offset,
					);

			$this->query = new WP_Query($args);
			wp_reset_postdata();

			return array(
				'posts'     => $this->query->posts,
				'max_pages' => $this->query->max_num_pages,
			);
		}


		public function get_activated_plugin() {

			$apl=get_option('active_plugins');
			$plugins=get_plugins();
			$activated_plugins=array();
			foreach ($apl as $p){
				if(isset($plugins[$p]['Name'])){
					array_push($activated_plugins, $plugins[$p]['Name']);
				}
			}

			return $activated_plugins;
		}
  }
}
