<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build a sample metabox in editor screen
 */
if ( ! class_exists( 'ATTEST_LMS_CURRICULUM_METABOX' ) ) {

	final class ATTEST_LMS_CURRICULUM_METABOX {

    private $allowed_html;

		public function __construct() {

			$this->check_quizz_pro_add_on_installed();

			add_action( 'add_meta_boxes', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save' ), 30, 2 );
		}


		public function register() {

			add_meta_box(
				'attest_curriculum',
				esc_html__( 'Curriculum', 'attest' ),
				array( $this, 'render' ),
				'attest_course',
				'normal',
				'core'
			);
		}

		public function render() {

			global $post;

			$post_id = $post->ID;
			wp_nonce_field( basename( __FILE__ ), 'attest_curriculum_nonce' );
 			$data = get_post_meta( $post->ID, 'attest_curriculum', true ); ?>

			<p><?php _e('Group all your Lessons into Modules. Create new Lessons or select from the already existing ones. Sort the Lessons within the Module or the Modules between them, with a simple drag and drop. Edit or delete the Lessons(the Lesson would not be deleted permanently). Activate any Lesson as Teaser and make it free for students to check it out, without enrolling in the course.', 'attest'); ?></p>
			<br />
			<div class="section_append_to" id="section_append_to">
			<?php if ($data != false && count($data) > 0) :

				$disabled = $this->lesson_disabled($data);
				foreach ($data as $key => $section) :
					$section_title = $section['title']; ?>

				<div id="section_container" class="components-panel__body">
					<?php $this->accordion_title('section', __('Module', 'attest'), $key, $key, $section_title, $post_id, false); ?>
					<div id="section" class="section-wrap">
						<div id="section_ID" data-serial="<?php echo intval($key); ?>"></div>
						<div class="lesson_append_to" id="lesson_append_to">
						<?php unset($section['title']);
						if (array_key_exists(0, $section) && count($section[0]) > 0) :
						$lesson_key = 0;
						$quizz_key = 0;
						foreach ($section[0] as $item => $lesson) :

							$item_ID = (isset($lesson['lesson_id']) ? $lesson['lesson_id'] : false);
							$teaser = (isset($lesson['lesson_teaser']) ? $lesson['lesson_teaser'] : false);
							$lesson_title = get_the_title($item_ID);
							$post_type = get_post_type($item_ID);
							?>
							<div id="lesson_container" class="components-panel__body">
								<?php
								if ($this->quizz_pro_installed && $post_type == 'attest_quizz') :
									$this->accordion_title('quizz', __('quizz', 'attest'), $quizz_key, $item, $lesson_title, $post_id, false);
									$quizz_key++;
								elseif ($post_type == 'attest_lesson') :
									$this->accordion_title('lesson', __('Lesson', 'attest'), $lesson_key, $item, $lesson_title, $post_id, false);
									$lesson_key++;
								endif;
								?>
								<div id="lesson_ID" data-serial="<?php echo intval($item); ?>" <?php echo ($post_type == 'attest_quizz' ? 'data-quizz-serial="' . $quizz_key . '"' : ''); ?>></div>
								<div class="lesson-wrap">
									<?php $this->lessons_and_quizzs($post_type, $key, $item, $item_ID, $data, $teaser, $disabled, $post_id, false); ?>
									<br />
								</div>
							</div>
						<?php endforeach; endif; ?>
						</div>
						<?php $this->add_new_lesson_or_quizz($post_id); ?>
					</div>
				</div>

			<?php endforeach; endif; ?>
			</div>
			<div class="add-new-section">
				<a href="javascript:void(0);" id="new_section_link" class="components-button is-primary"><?php _e('New Module', 'attest'); ?></a>
				<div id="new_section_form">
					<input type="text" id="add_new_section" class="regular-text" placeholder="<?php _e('Write the title', 'attest') ?>..." value="" />
					<a class="new_section components-button is-secondary" href="javascript:void(0);" type="button"><?php _e( 'Create New', 'attest'); ?></a>
				</div>
			</div>
			<div id="appendFrom" class="attest-hide">
				<?php $this->lesson_template($data, $post_id); ?>
			</div>
			<div id="quizzAppendFrom" class="attest-hide">
				<?php $this->quizz_template($data, $post_id); ?>
			</div>
			<?php
		}


		//Accordion title
		public function accordion_title($type, $name, $serial, $key, $title, $post_id, $is_template) { ?>

			<div class="components-panel_<?php echo esc_attr($type); ?>_body-title">
				<div aria-expanded="true" class="components-button components-panel__body-toggle">
					<span class="accordion-title-number-<?php echo esc_attr($type); ?>">
						<?php //if ($type == 'lesson') : ?>
						<span class="material-icons drag-icon-<?php echo esc_attr($type); ?>">drag_indicator</span>
						<?php //endif; ?>
						<?php echo esc_attr($name); ?>&nbsp;
						<span class="accordion_title_number" id="accordion_title_number_<?php echo esc_attr($type); ?>"><?php echo (is_integer($serial) ? ($serial + 1) : 1); ?></span>&nbsp;
					</span>
					<span class="accordion-title-name-<?php echo esc_attr($type); ?>" id="accordion_title_name_<?php echo esc_attr($type); ?>">
					<?php
					if ( ($type == 'lesson' || $type == 'quizz') && $is_template == true ) :
						$first_ID = $this->lesson_id();
						echo esc_attr(get_the_title($first_ID));
					else :
						echo esc_attr($title);
					endif; ?>
					</span>&nbsp;&nbsp;
					<?php if ($type == 'section') : ?>
						<span class="material-icons edit_section">edit</span>
					<?php endif; ?>
					<?php if ($type == 'section') : ?>
						<?php wp_nonce_field( ATTEST_LMS_FILE, 'attest_save_lesson_nonce' ); ?>
						<div class="attest_edit_title_container">
							<input type="text" class="attest-curriculum-title regular-text" name="attest_curriculum<?php echo ($is_template ? '_temp' : false) ?>[<?php echo intval($key); ?>][title]" id="attest_curriculum_title" value="<?php echo esc_attr($title); ?>" />
							<a class="save_title button button-secondary save_rule_style" href="javascript:void(0);" type="button" id="save_title" data-id="<?php echo $post_id; ?>"><?php _e( 'Save', 'attest'); ?></a>
						</div>
					<?php endif; ?>
					<span aria-hidden="true">
						<span class="components-panel__arrow" role="img" aria-hidden="true" focusable="false">
							<span class="section_close material-icons">keyboard_arrow_down</span>
							<span class="section_open material-icons">keyboard_arrow_up</span>
							<?php if ($type == 'section') : ?>
							&nbsp;<a href="javascript:void(0);" class="delete_section editor-post-trash is-destructive">Delete</a>
							<?php endif; ?>
						</span>
					</span>
				</div>
			</div>
		<?php
		}


		public function lessons_and_quizzs($type, $key, $item, $item_ID, $data, $teaser, $disabled, $post_id, $is_template) { ?>

			<table id="lesson_table" class="lesson-table">
				<tr>
					<td colspan="3">
						<p><span class="attest-lesseon-title-label"><?php _e( "Select from existing", "attest" ); ?></span></p>
					</td>
				</tr>
				<tr>
					<td class="lesson_id_container">
						<?php if ($type == 'attest_lesson') : ?>
							<select name="attest_curriculum<?php echo ($is_template ? '_temp' : false) ?>[<?php echo intval($key); ?>][<?php echo intval($item); ?>][lesson_id]" class="regular-text" id="attest_lesson_id" autocomplete="off">
								<?php echo $this->lessons_list($item_ID); ?>
							</select>
						<?php elseif ($this->quizz_pro_installed && $type == 'attest_quizz') : ?>
							<select name="attest_curriculum<?php echo ($is_template ? '_temp' : false) ?>[<?php echo intval($key); ?>][<?php echo intval($item); ?>][lesson_id]" class="regular-text" id="attest_quizz_id" autocomplete="off">
								<?php echo $this->quizzes_list($item_ID); ?>
							</select>
						<?php endif; ?>
					</td><td class="lesson_link_container">
						<a id="lesson_link" class="lesson-link-edit" href="<?php echo esc_url_raw(get_edit_post_link($item_ID)); ?>" target="_blank"><?php _e('Edit', 'attest') ?></a>
					</td><td class="lesson_link_container">
						<a id="lesson_link" class="lesson-link-view" href="<?php echo esc_url_raw(get_permalink($item_ID)); ?>" target="_blank"><?php _e('View', 'attest') ?></a>
					</td><td class="lesson_delete_container">
						<a class="delete_lesson editor-post-trash is-destructive" href="javascript:void(0);"><?php _e('Delete', 'attest') ?></a>
					</td>
				</tr>
				<tr>
					<td>
						<p>
							<?php if ($type == 'attest_lesson') : ?>
							<label>
							<input id="attest_lesson_teaser" type="checkbox" name="attest_curriculum<?php echo ($is_template ? '_temp' : false) ?>[<?php echo intval($key); ?>][<?php echo intval($item); ?>][lesson_teaser]" value="0" <?php checked($teaser, '1', true); ?>/>
							<?php _e('Activate as Teaser', 'attest'); ?>
							</label>
							<?php endif; ?>
						</p>
					</td>
				</tr>
			</table>
			<?php
		}


		public function add_new_lesson_or_quizz($post_id) {

			$array = array(
				'new'   => __('Create new', 'attest'),
				'exist' => __('Select from existing', 'attest')
			);

			$select = '<select id="new_lesson_or_quizz_type_selector" autocomplete="off">';
			foreach ($array as $key => $value) {
				$select .= '<option value="' . $key . '">' . $value . '</option>';
			}
			$select .= '</select>'; ?>

			<div class="add-new-lesson">

				<a href="javascript:void(0);" class="new_lesson_link"><?php ($this->quizz_pro_installed) ? _e('Add New', 'attest') : _e('New Lesson', 'attest'); ?></a>
				<div class="new_lesson_form">
					<?php echo $select; ?>
					<input type="hidden" id="attest_course_id" name="attest_course_id" value="<?php echo $post_id; ?>" />
					<?php wp_nonce_field( ATTEST_LMS_FILE, 'attest_new_lesson_or_quizz_nonce' ); ?>

					<?php if ($this->quizz_pro_installed) : ?>
						<select id="choose_quizz_or_lesson" autocomplete="off">
							<option value="attest_lesson"><?php _e('Lesson', 'attest') ?></option>
							<option value="attest_quizz"><?php _e('Quizz', 'attest') ?></option>
						</select>
						<input type="text" id="add_new_quizz" class="regular-text" placeholder="<?php _e('Write the title', 'attest') ?>..." value="" />
						<select id="add_new_quizz_exist" class="regular-text" autocomplete="off">
							<?php echo $this->quizzes_list(false); ?>
						</select>
					<?php endif; ?>
					<input type="text" id="add_new_lesson" class="regular-text" placeholder="<?php _e('Write the title', 'attest') ?>..." value="" />
					<select id="add_new_lesson_exist" class="regular-text" autocomplete="off">
						<?php echo $this->lessons_list(false); ?>
					</select>

					<a class="new_lesson components-button is-secondary" href="javascript:void(0);" type="button"><?php _e( 'Create', 'attest'); ?></a>
				</div>
			</div>
			<?php
		}


		public function lesson_template($data, $post_id) { ?>

				<div id="section_container" class="components-panel__body">
					<?php $this->accordion_title('section', __('Module', 'attest'), 0, '', '', $post_id, true); ?>
					<div id="section" class="section-wrap">
						<div id="section_ID" data-serial="0"></div>
						<div class="lesson_append_to" id="lesson_append_to">
							<div id="lesson_container" class="components-panel__body">
								<?php $this->accordion_title('lesson', __('Lesson', 'attest'), 0, '', '', $post_id, true); ?>
								<div id="lesson_ID" data-serial="0"></div>
								<div class="lesson-wrap">
	        				<?php
									$first_ID = $this->lesson_id();
									$this->lessons_and_quizzs('attest_lesson', 0, 0, $first_ID, $data, 0, true, $post_id, true); ?>
	    					</div>
							</div>
						</div>
						<?php $this->add_new_lesson_or_quizz($post_id); ?>
					</div>
				</div>
		<?php
		}


		public function quizz_template($data, $post_id) { ?>

				<div id="section_container" class="components-panel__body">
					<?php $this->accordion_title('section', __('Module', 'attest'), 0, '', '', $post_id, true); ?>
					<div id="section" class="section-wrap">
						<div id="section_ID" data-serial="0"></div>
						<div class="lesson_append_to" id="lesson_append_to">
							<div id="lesson_container" class="components-panel__body">
								<?php $this->accordion_title('quizz', __('quizz', 'attest'), 0, '', '', $post_id, true); ?>
								<div id="lesson_ID" data-serial="0" data-quizz-serial="0"></div>
								<div class="lesson-wrap">
	        				<?php
									$first_ID = $this->lesson_id();
									$this->lessons_and_quizzs('attest_quizz', 0, 0, $first_ID, $data, 0, true, $post_id, true); ?>
	    					</div>
							</div>
						</div>
						<?php $this->add_new_lesson_or_quizz($post_id); ?>
					</div>
				</div>
		<?php
		}


		public function lesson_id() {

			$args = array(
        'post_type' => 'attest_lesson',
        'post_status' => 'publish',
        'posts_per_page' => 1,
				'orderby' => 'ID',
        'order' => 'ASC',
				'fields' => array('ID'),
      );

      $uni_query = new WP_Query( $args );
			$posts = $uni_query->get_posts();

			if (isset($posts[0]->ID)) {
				$ID = $posts[0]->ID;
			} else {
				$ID = false;
			}

			return $ID;
		}


		public function lessons_list($lesson_ID) {

			$args = array(
        'post_type' => 'attest_lesson',
        'post_status' => 'publish',
        'posts_per_page' => -1,
				'orderby' => 'ID',
        'order' => 'ASC',
				'fields' => array('ID', 'post_title'),
      );

      $sr_query = new WP_Query( $args );
			$posts = $sr_query->get_posts();

			$html = '';
			if (count($posts) > 0) {
				foreach($posts as $cpt) :
					$html .= '<option value="' . intval($cpt->ID) . '" data-title="' . esc_attr(get_the_title($cpt->ID)) . '" data-link="' . esc_url_raw(get_permalink($cpt->ID)) . '"  data-edit-link="' . esc_url_raw(get_edit_post_link($cpt->ID)) . '" ' . selected($lesson_ID, $cpt->ID, false) . '>' . esc_attr($cpt->post_title) . '</option>';
				endforeach;
			}

      return $html;
		}


		public function lesson_disabled($data) {

			$diabled_lesson = false;

			$count = 0;
			foreach ($data as $key => $section) {
				unset($section['title']);
				$count += count($section[0]);
			}

			if ($count <= 1) {
				$diabled_lesson = 'disabled="disabled"';
			}

			return $diabled_lesson;
		}


		//Save the post data
		function save( $post_id, $post ) {

			//Check if doing autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			//Verify the nonce before proceeding.
			if ( !isset( $_POST['attest_curriculum_nonce'] ) || !wp_verify_nonce( $_POST['attest_curriculum_nonce'], basename( __FILE__ ) ) ) return;

			//Get the post type object.
			$post_type = get_post_type_object( $post->post_type );

			//Check if the current user has permission to edit the post.
			if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) return $post_id;

			if ( isset( $_POST['attest_curriculum'] ) ) {

				$to_save = $this->sanitize($_POST['attest_curriculum']);
				update_post_meta( $post_id, 'attest_curriculum', $to_save );
			} else {
				update_post_meta( $post_id, 'attest_curriculum', false );
			}
		}


		private function sanitize($sections) {

			$data = array();
			$k = 0;
			if (is_array($sections)) {

				foreach($sections as $section) {
					$data[$k]['title'] = (isset($section['title']) ? sanitize_text_field($section['title']) : false);
					$lesson_sanitized = array();
					$i = 0;
					foreach ($section as $lesson) {
						if(isset($lesson['lesson_id'])) {
							$lesson_sanitized[$i]['lesson_id'] = sanitize_text_field($lesson['lesson_id']);
						}
						if(isset($lesson['lesson_teaser'])) {
							$lesson_sanitized[$i]['lesson_teaser'] = (isset($lesson['lesson_teaser']) ? '1' : '0');
						}
						$i++;
					}
					array_push($data[$k], array_values($lesson_sanitized));
					$k++;
				}
			}

			return $data;
		}


		//quizz pro add on link
		public function check_quizz_pro_add_on_installed() {

		  $installed = false;

		  $plugins = $this->get_activated_plugin();
		  if( in_array('WP Attest PRO - Quizz', $plugins) ) {
		    $status = get_option('_attest_quizz_pro_activate_status');
		    if (intval($status) == 1) {
		      $installed = true;
		    }
		  }

		  $this->quizz_pro_installed = $installed;
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


		public function quizzes_list($lesson_ID) {

		  $args = array(
		    'post_type' => 'attest_quizz',
		    'post_status' => 'publish',
		    'posts_per_page' => -1,
		    'orderby' => 'ID',
		    'order' => 'ASC',
		    'fields' => array('ID', 'post_title'),
		  );

		  $sr_query = new WP_Query( $args );
		  $posts = $sr_query->get_posts();

		  $html = '';
		  if (count($posts) > 0) {
		    foreach($posts as $cpt) :
		      $html .= '<option value="' . intval($cpt->ID) . '" data-title="' . esc_attr(get_the_title($cpt->ID)) . '" data-link="' . esc_url_raw(get_permalink($cpt->ID)) . '"  data-edit-link="' . esc_url_raw(get_edit_post_link($cpt->ID)) . '" ' . selected($lesson_ID, $cpt->ID, false) . '>' . esc_attr($cpt->post_title) . '</option>';
		    endforeach;
		  }

		  return $html;
		}

	}
} ?>
