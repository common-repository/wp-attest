<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build a settings metabox in editor screen
 */
if ( ! class_exists( 'ATTEST_LMS_DETAILS_METABOX' ) ) {

	final class ATTEST_LMS_DETAILS_METABOX {

		public function __construct() {

			add_action( 'add_meta_boxes', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		}


		public function register() {

			add_meta_box(
				'attest_details',
				esc_html__( 'Details', 'attest' ),
				array( $this, 'render' ),
				array('attest_course'),
				'normal',
				'core'
			);
		}


		public function render() {

			global $post;

			$post_id = $post->ID;
			$post_type = $post->post_type;

			wp_nonce_field( basename( __FILE__ ), 'attest_details_nonce' ); ?>

			<div id="attest_details">

				<div id="details-tab-container" class="tab-container">
					<ul class="etabs details-tabs">
						<li class="tab"><a href="#features"><?php _e('Key features', 'attest'); ?></a></li>
						<li class="tab"><a href="#requirements"><?php _e('Requirements', 'attest'); ?></a></li>
						<li class="tab"><a href="#audience"><?php _e('Audience', 'attest'); ?></a></li>
						<li class="tab"><a href="#faq"><?php _e('FAQ', 'attest'); ?></a></li>
					</ul>
					<div class="panel-container details-panel-container">
						<div id="features">
							<h2><strong><?php _e('Key features', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php echo sprintf(__('List down all the features, mentioning all the important elements that the student will learn going through the course. Write one per row and style it using HTML tags like %s.', 'attest'), '<code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;a&gt;</code>'); ?></p>
							<?php $this->course_fetaures_content($post_id); ?>
						</div>
						<div id="requirements">
							<h2><strong><?php _e('Requirements', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php echo sprintf(__('List down all the prerequisites that the student has to achieve before enrolling in the course. Write one per row and style it using HTML tags like %s.', 'attest'), '<code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;a&gt;</code>'); ?></p>
							<?php $this->course_requirements_content($post_id); ?>
						</div>
						<div id="audience">
							<h2><strong><?php _e('Audience', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php echo sprintf(__('List down all the types of people that might be interested in taking this course. Ask yourself who is this course good for. Write one per row and style it using HTML tags like %s.', 'attest'), '<code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;a&gt;</code>'); ?></p>
							<?php $this->course_audience_content($post_id); ?>
						</div>
						<div id="faq">
							<h2><strong><?php _e('Frequent Asked Questions', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('Reduce support requests with questions that have thorough explanations.', 'attest'); ?></p>
							<?php $this->course_faq_content($post_id); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}


		public function course_fetaures_content($post_id) {?>

			<p>
				<textarea class="widefat" rows="3" name="attest_key_features" id="attest_key_features"><?php echo $this->textarea_placeholder($post_id, 'attest_key_features'); ?></textarea>
			</p>
			<?php
		}


		public function course_requirements_content($post_id) {?>

			<p>
				<textarea class="widefat" rows="3" name="attest_requirements" id="attest_requirements"><?php echo $this->textarea_placeholder($post_id, 'attest_requirements'); ?></textarea>
			</p>
			<?php
		}


		public function course_audience_content($post_id) {?>

			<p>
			<textarea class="widefat" rows="3" name="attest_audience" id="attest_audience"><?php echo $this->textarea_placeholder($post_id, 'attest_audience'); ?></textarea>
			</p>
			<?php
		}


		public function textarea_placeholder($post_id, $key) {

			$data = wp_kses_post( get_post_meta( $post_id, $key, true ) );
			if (empty($data)) {
				$data = __('Type Something', 'attest') . '...';
			}

			return $data;
		}


		public function course_faq_content($post_id) { ?>

		<style type="text/css">
			.faq-heading {margin: 20px 0px 10px 5px;}
			.faq-left-pad {padding-left: 5px;}
			.delete_q_a {font-size: 18px; cursor: pointer;}
			#attest_template, #attest_fq_id {display: none;}
		</style>
		<?php
			$faq_data = get_post_meta( $post_id, 'attest_faq', false );
			$faq = (isset($faq_data[0]) ? $faq_data[0] : false);

			$q = ( $faq && count($faq) == 1 && isset($faq[0]['q']) && $faq[0]['q'] == '' ) ? true : false;
			$a = ( $faq && count($faq) == 1 && isset($faq[0]['a']) && $faq[0]['a'] == '' ) ? true : false; ?>

			<p>
				<a href="javascript:void(0);" class="attest-faq-add-new" id="attest_faq_add_new"><?php _e( 'Add question', 'attest' ); ?></a>
			</p>

			<div id="attest_faq_append_to">
			<?php if ( is_array( $faq ) && count( $faq ) > 0 ) :
			if (false == $q && false == $a) :
			foreach( $faq as $key => $item ) : ?>

				<div id="attest_faq_container">
					<div id="attest_fq_id"><?php echo intval( $key ); ?></div>
					<p class="faq-heading">
						<strong>
							<?php _e( 'Question', 'attest' ); ?>
							<span id="attest_faq_number">
								<?php echo ( intval( $key ) + 1 ); ?>
							</span>
						</strong>
					</p>
					<table id="attest_faq_table">
						<tr>
							<td>
								<input type="text" class="regular-text" id="attest_faq_q"
								name="attest_faq[<?php echo intval( $key ); ?>][q]"
								placeholder="<?php _e( 'Question', 'attest' ) ?>"
								value="<?php echo esc_attr( $item['q'] ); ?>" />
							</td>
							<td class="faq-left-pad">
								<input type="text" class="regular-text" id="attest_faq_a"
								name="attest_faq[<?php echo intval( $key ); ?>][a]"
								placeholder="<?php _e( 'Answer', 'attest' ) ?>"
								value="<?php echo esc_attr( $item['a'] ); ?>" />
							</td>
							<td class="faq-left-pad">
								<span id="delete_q_a" class="material-icons delete_q_a">delete</span>
							</td>
						</tr>
					</table>
				</div>

			<?php endforeach; endif; endif; ?>
			</div>
			<div id="attest_template">
				<div id="attest_faq_container">
					<div id="attest_fq_id">0</div>
					<p class="faq-heading">
						<strong>
							<?php _e( 'Question', 'attest' ); ?>
							<span id="attest_faq_number">0</span>
						</strong>
					</p>
					<table id="attest_faq_table">
						<tr>
							<td>
								<input type="text" class="regular-text" id="attest_faq_q"
								name="attest_faq_temp[0][q]"
								placeholder="<?php _e( 'Question', 'attest' ) ?>"
								value="" />
							</td>
							<td class="faq-left-pad">
								<input type="text" class="regular-text" id="attest_faq_a"
								name="attest_faq_temp[0][a]"
								placeholder="<?php _e( 'Answer', 'attest' ) ?>"
								value="" />
							</td>
							<td class="faq-left-pad">
								<span id="delete_q_a" class="material-icons delete_q_a">delete</span>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php
		}


		//Save the post data
		function save( $post_id, $post ) {

			//Check if doing autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			//Verify the nonce before proceeding.
			if ( !isset( $_POST['attest_details_nonce'] ) || !wp_verify_nonce( $_POST['attest_details_nonce'], basename( __FILE__ ) ) ) return;

			//Get the post type object.
			$post_type = get_post_type_object( $post->post_type );

			//Check if the current user has permission to edit the post.
			if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) return $post_id;

			if ( isset( $_POST['attest_key_features'] ) ) {

				$to_save = wp_kses_post($_POST['attest_key_features']);
				update_post_meta( $post_id, 'attest_key_features', $to_save );
			}

			if ( isset( $_POST['attest_requirements'] ) ) {

				$to_save = wp_kses_post($_POST['attest_requirements']);
				update_post_meta( $post_id, 'attest_requirements', $to_save );
			}

			if ( isset( $_POST['attest_audience'] ) ) {

				$to_save = wp_kses_post($_POST['attest_audience']);
				update_post_meta( $post_id, 'attest_audience', $to_save );
			}

			if ( isset( $_POST['attest_faq'] ) ) {

				$to_save = $this->sanitize_faq($_POST['attest_faq']);
				update_post_meta( $post_id, 'attest_faq', $to_save );
			} else {

				update_post_meta( $post_id, 'attest_faq', false );
			}
		}


		public function sanitize_faq($input) {

			$output = array();

			if (is_array($input) && count($input) > 0) {
				foreach ($input as $item) {
					$data = array();
					$data['q'] = ( isset($item['q']) ? sanitize_text_field( $item['q'] ) : false );
					$data['a'] = ( isset($item['a']) ? sanitize_text_field( $item['a'] ) : false );
					$output[] = $data;
				}
			}

			return $output;
		}
  }
}
