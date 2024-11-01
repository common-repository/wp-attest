<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build a settings metabox in editor screen
 */
if ( ! class_exists( 'ATTEST_LMS_SETTINGS_METABOX' ) ) {

	final class ATTEST_LMS_SETTINGS_METABOX {


		public static $name = 'announcement-form';
		public static $table = 'attest_announcements';
		private $rewards_types;
		private $pro_installed;
		private $conditions_types;
		private $conditions_actions;


		public function __construct() {

			$this->rewards_types = $this->rewards_types();
			$this->conditions_types = $this->conditions_types();
			$this->conditions_actions = $this->conditions_actions();

			$plugins = $this->get_activated_plugin();
			if( in_array('WP Attest PRO - WooCommerce', $plugins) ) {
				$this->pro_installed = true;
			} else {
				$this->pro_installed = false;
			}

			$this->lns = get_option('attest_lms_lns');

			add_action( 'add_meta_boxes', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		}


		public function register() {

			add_meta_box(
				'attest_settings',
				esc_html__( 'Settings', 'attest' ),
				array( $this, 'render' ),
				array('attest_course', 'attest_lesson'),
				'normal',
				'core'
			);
		}


		public function render() {

			global $post;

			$post_id = $post->ID;
			$post_type = $post->post_type;

			wp_nonce_field( basename( __FILE__ ), 'attest_settings_nonce' ); ?>

			<div id="attest_settings">
			<?php if ($post_type == 'attest_lesson') : ?>

				<div id="tab-container" class="tab-container">
					<ul class="etabs lesson-tabs">
						<li class="tab"><a href="#duration"><?php _e('Duration', 'attest'); ?></a></li>
						<li class="tab"><a href="#assessment"><?php _e('Assessment', 'attest'); ?></a></li>
					</ul>
					<div class="panel-container lesson-panel-container">
						<div id="duration">
							<h2><strong><?php _e('Duration', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('Define manually the time that it takes for this Lesson to be finished. Usually, if it is a video, you can enter the length of the video in hours, minutes, and/or seconds.', 'attest'); ?></p>
							<?php $this->lesson_duration_content($post_id); ?>
						</div>
						<div id="assessment">
							<h2><strong><?php _e('Assessment', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('You can define the number of Points a student will be rewarded with after finishing the Lesson. (e.g. 10 points each Lesson) or you can consider it finished on a View basis. On the Course editor, you can use these criteria to automatically define when the course was completed.', 'attest'); ?></p>
							<?php $this->lesson_assessment_content($post_id); ?>
						</div>
					</div>
				</div>

			<?php elseif ($post_type == 'attest_course') : ?>

				<?php $pro_text = '<span class="pull-right attest-pro">' . __('PRO', 'attest') . '</span>'; ?>
				<div id="tab-container" class="tab-container">
				  <ul class="etabs course-tabs">
				    <li class="tab"><a href="#price"><?php _e('Price', 'attest'); ?></a></li>
				    <li class="tab"><a href="#students"><?php _e('Students', 'attest'); ?></a></li>
				    <li class="tab"><a href="#language"><?php _e('Language', 'attest'); ?></a></li>
				    <li class="tab"><a href="#duration"><?php _e('Duration', 'attest'); ?></a></li>
				    <li class="tab"><a href="#assessment"><?php _e('Assessment', 'attest'); ?></a></li>
				    <li class="tab"><a href="#featured"><?php _e('Featured', 'attest'); ?></a></li>
				    <li class="tab"><a href="#announcement"><?php _e('Announcement', 'attest'); ?></a></li>
				  </ul>
					<div class="panel-container">
				  	<div id="price">
				    	<h2><strong><?php _e('Price', 'attest'); ?></strong></h2>
				    	<p class="etab-content-text"><?php echo sprintf(__('Set out a pricing model for your course.', 'attest')); ?></p>
							<?php $this->course_price_content($post_id); ?>
				  	</div>
				  	<div id="students">
				    	<h2><strong><?php _e('Students', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('Use this feature to Disable the course when you are working on it.', 'attest'); ?></p>
				    	<?php $this->course_students_content($post_id); ?>
				  	</div>
				  	<div id="language">
				    	<h2><strong><?php _e('Language', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('Mention what is the language that will be used throughout the course. This will be shown on the frontend just as a label.', 'attest'); ?></p>
				    	<?php $this->course_language_content($post_id); ?>
				  	</div>
				  	<div id="duration">
				    	<h2><strong><?php _e('Duration', 'attest'); ?></strong></h2>
				    	<p class="etab-content-text"><?php _e('Check this feature if you want to show how much time it will take for a student to go through the entire course.', 'attest'); ?></p>
							<?php $this->course_duration_content($post_id); ?>
				  	</div>
				  	<div id="assessment">
				    	<h2><strong><?php _e('Assessment', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('Define the rules when the system will automatically allow the user to complete the course. Add conditions based on "Total points earned" (which will be defined on Lessons individually), and the number of Lessons that were "Opened". Use AND / OR rules for advanced setup.', 'attest'); ?></p>
				    	<?php $this->course_assessment_content($post_id); ?>
				  	</div>
				  	<div id="featured">
				    	<h2><strong><?php _e('Featured', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('Highlight this course with special predefined styling. Activating this feature will list your course with a border that will make it stand out. It can be seen on the listing page.', 'attest'); ?></p>
				    	<?php $this->course_featured_content($post_id); ?>
				  	</div>
				  	<div id="announcement">
				    	<h2><strong><?php _e('Announcement', 'attest'); ?></strong></h2>
							<p class="etab-content-text"><?php _e('When updating the course, you can craft an announcement or hide the current one from the course page. Once released, it will notify all students by email. Go to Settings > Email to define the message. You cannot edit the message once released.', 'attest'); ?></p>
				    	<?php $this->course_announcement_content($post_id); ?>
				  	</div>
					</div>
				</div>

			<?php	endif; ?>
			</div>
			<?php
		}


		public function lesson_duration_content($post_id) {

			$duration = get_post_meta( $post_id, 'attest_lesson_duration', false );
			$h = ( isset($duration[0]['h']) ? $duration[0]['h'] : false );
			$min = ( isset($duration[0]['min']) ? $duration[0]['min'] : false );
			$sec = ( isset($duration[0]['sec']) ? $duration[0]['sec'] : false );
			?>

			<table>
				<tr>
					<td><?php _e('Hours', 'attest'); ?></td>
					<td><?php _e('Minutes', 'attest'); ?></td>
					<td><?php _e('Seconds', 'attest'); ?></td>
				</tr>
				<tr>
					<td>
						<input type="number" class="small-text" min="0" step="1" max="9999" name="attest_lesson_duration[h]" id="attest_lesson_duration" autocomplete="off" value="<?php echo esc_attr($h); ?>" />
					</td>
					<td>
						<input type="number" class="small-text" min="0" step="1" max="9999" name="attest_lesson_duration[min]" id="attest_lesson_duration" autocomplete="off" value="<?php echo esc_attr($min); ?>" />
					</td>
					<td>
						<input type="number" class="small-text" min="0" step="1" max="60" name="attest_lesson_duration[sec]" id="attest_lesson_duration" autocomplete="off" value="<?php echo esc_attr($sec); ?>" />
					</td>
				</tr>
			</table>
		<?php
		}


		public function lesson_assessment_content($post_id) {

			$lesson_assessment = get_post_meta( $post_id, 'attest_lesson_assessment', false );
			$lesson_type = (isset($lesson_assessment[0]['type']) ? esc_attr($lesson_assessment[0]['type']) : false);
			$lesson_number = (isset($lesson_assessment[0]['number']) ? $lesson_assessment[0]['number'] : false); ?>

			<div id="assessment_lesson">
				<p>
					<label for="attest_lesson_assessment"><?php _e( "Reward", 'attest' ); ?></label><br />
					<table>
						<tr>
							<td>
								<select name="attest_lesson_assessment[type]" id="attest_lesson_assessment_type" class="medium-text" autocomplete="off">
									<?php foreach($this->rewards_types as $key => $item) : ?>
									<option value="<?php echo esc_attr($key); ?>" <?php selected($lesson_type, $key, true); ?>><?php echo esc_attr($item); ?></option>
								<?php endforeach; ?>
								</select>
							</td>
							<td class="assessment-left-pad">
								<input type="number" class="medium-text" min="0" step="1" max="9999"
								name="attest_lesson_assessment[number]"
								id="attest_lesson_assessment_number"
								autocomplete="off"
								value="<?php echo esc_attr($lesson_number); ?>"
								placeholder="<?php _e('Define a number', 'attest'); ?>"
								<?php echo (($lesson_type != 'points') ? 'style="display:none;"' : false ); ?> />
							</td>
						</tr>
					</table>
				</p>
			</div>
		<?php
		}


		public function course_price_content($post_id) {

			$price_data = get_post_meta( $post_id, 'attest_course_price', false );
			$price = (isset($price_data[0]) ? esc_attr($price_data[0]) : 'free');

			$price_amount_data = get_post_meta( $post_id, 'attest_course_price_amount', false );
			$price_actual = (isset($price_amount_data[0]['actual']) ? esc_attr($price_amount_data[0]['actual']) : 0);
			$price_sale = (isset($price_amount_data[0]['sale']) ? esc_attr($price_amount_data[0]['sale']) : 0);
			?>
			<p>
				<label for="attest_course_price_free">
					<input type="radio" id="attest_course_price_free" name="attest_course_price" value="free" <?php checked($price, 'free', true); ?> />
					<?php _e('Free(Requires Registration)', 'attest'); ?>
				</label>
			</p>
			<p>
				<label for="attest_course_price_paid">
					<input type="radio" id="attest_course_price_paid" name="attest_course_price" value="paid" <?php checked($price, 'paid', true); ?> />
					<?php _e('One-time payment', 'attest'); ?>
				</label>
			</p>
			<p>
				<table id="attest_price_value">
					<tr>
						<td><label for="attest_course_price_amount_actual"><?php _e('Price', 'attest'); ?></label></td>
						<td><label for="attest_course_price_amount_sale"><?php _e('Sale price', 'attest'); ?></label></td>
					</tr>
					<tr>
						<td><input type="number" id="attest_course_price_amount_actual" name="attest_course_price_amount[actual]" value="<?php echo intval( $price_actual ); ?>" /></td>
						<td><input type="number" id="attest_course_price_amount_sale" name="attest_course_price_amount[sale]" value="<?php echo intval( $price_sale ); ?>" /></td>
					</tr>
				</table>
			</p>
			<?php
		}


		public function course_language_content($post_id) {

			$stored_ln = get_post_meta( $post_id, 'attest_language', true ); ?>
			<p>
				<select name="attest_language">
					<?php foreach($this->lns as $ln) : ?>
						<option value="<?php echo $ln['short']; ?>" <?php selected( $stored_ln, $ln['short'], true ); ?>><?php echo $ln['long']; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php
		}


		public function course_assessment_content($post_id) {

			$lesson_curriculum_data = $this->get_lesson_curriculum_data($post_id);

			$course_assessment_data = get_post_meta( $post_id, 'attest_course_assessment', false );
			$course_assessment = (isset($course_assessment_data[0]) ? $course_assessment_data[0] : false);

			$course_type = (isset($course_assessment[0]['type']) ? esc_attr($course_assessment[0]['type']) : false);
			$course_action = (isset($course_assessment[0]['action']) ? $course_assessment[0]['action'] : false);
			$course_points = (isset($course_assessment[0]['points']) ? $course_assessment[0]['points'] : false);
			$course_opened_count = (isset($course_assessment[0]['opened']) ? $course_assessment[0]['opened'] : false);
			$course_connection = (isset($course_assessment[0]['connection']) ? $course_assessment[0]['connection'] : false);
			$course_new = (isset($course_assessment[0]['new']) ? $course_assessment[0]['new'] : '0');

			$course_type_more = (isset($course_assessment[1]['type']) ? esc_attr($course_assessment[1]['type']) : false);
			$course_action_more = (isset($course_assessment[1]['action']) ? $course_assessment[1]['action'] : false);
			$course_points_more = (isset($course_assessment[1]['points']) ? $course_assessment[1]['points'] : false);
			$course_opened_count_more = (isset($course_assessment[1]['opened']) ? $course_assessment[1]['opened'] : false);
			$course_connection_more = (isset($course_assessment[1]['connection']) ? $course_assessment[1]['connection'] : false); ?>

			<div id="assessment_course">
				<p>
					<table class="course_assessment_table">
						<tr>
							<td><?php _e('IF', 'attest'); ?></td>
							<td id="attest_course_assessment_text" class="assessment-left-pad"><?php _e('Condition', 'attest'); ?></td>
							<td id="attest_course_assessment_text" class="assessment-left-pad"><?php _e('IS', 'attest'); ?></td>
						</tr>
						<tr>
							<td class="course_assessment_table_pad_top">
								<select name="attest_course_assessment[0][type]" id="attest_course_assessment_type" class="medium-text" autocomplete="off">
									<?php foreach($this->conditions_types as $key => $item) : ?>
										<option value="<?php echo esc_attr($key); ?>" <?php selected($course_type, $key, true); ?>><?php echo esc_attr($item); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="course_assessment_table_pad_top assessment-left-pad">
								<select name="attest_course_assessment[0][action]" id="attest_course_assessment_action" class="medium-text" autocomplete="off" <?php echo (($course_type != 'lesson') ? 'style="display:none;"' : false ); ?>>
									<?php foreach($this->conditions_actions as $key => $item) : ?>
										<option value="<?php echo esc_attr($key); ?>" <?php selected($course_action, $key, true); ?>><?php echo esc_attr($item); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="course_assessment_table_pad_top assessment-left-pad">
								<input type="number" class="medium-text" min="0" step="1" max="9999"
								name="attest_course_assessment[0][points]"
								id="attest_course_assessment_points"
								autocomplete="off"
								value="<?php echo esc_attr($course_points); ?>"
								placeholder="<?php _e('Min. points', 'attest'); ?>"
								<?php echo (($course_type == 'lesson' && $course_action == 'point') ? false : 'style="display:none;"' ); ?> />

								<input type="number" class="medium-text" min="0" step="1" max="9999"
								name="attest_course_assessment[0][opened]"
								id="attest_course_assessment_opened"
								autocomplete="off"
								value="<?php echo esc_attr($course_opened_count); ?>"
								placeholder="<?php _e('Min. lessons opened', 'attest'); ?>"
								<?php echo (($course_type == 'lesson' && $course_action == 'open') ? false : 'style="display:none;"' ); ?> />
							</td>
							<td class="course_assessment_number">
								<span id="course_assessment_number" <?php echo (($course_type != 'lesson') ? 'style="display:none;"' : false ); ?>>
									<?php _e('Out of', 'attest'); ?>:
									<span id="lesson_assessment_count"><?php echo $lesson_curriculum_data['count']; ?></span>
									<span id="lesson_assessment_points"><?php echo $lesson_curriculum_data['points']; ?></span>
								</span>
							</td>
						</tr>
						<tr>
							<td class="course_assessment_table_pad_top" colspan="5">
								<input type="hidden" id="attest_course_assessment_new_table" name="attest_course_assessment[0][new]" value="<?php echo esc_attr($course_new); ?>" />
								<a href="javascript:void(0);" id="attest_course_assessment_add_new" <?php echo (($course_type == 'lesson' && $course_new == '0') ? false : 'style="display:none;"' ); ?>>
									<?php _e('Add another condition', 'attest') ?>
								</a>
							</td>
						</tr>
						<tr id="course_assessment_table">
							<td colspan="5">
								<select name="attest_course_assessment[1][connection]" id="attest_course_assessment_connection" class="course_assessment_table_pad_top medium-text" autocomplete="off">
									<option value="and" <?php selected($course_connection_more, 'and', true); ?>><?php _e('AND', 'attest'); ?></option>
									<option value="or" <?php selected($course_connection_more, 'or', true); ?>><?php _e('OR', 'attest'); ?></option>
								</select>
							</td>
						</tr>
						<tr id="course_assessment_table" <?php echo (($course_new == '1') ? false : 'style="display:none;"' ); ?>>
							<td class="course_assessment_table_pad_top">
								<select name="attest_course_assessment[1][type]" id="attest_course_assessment_type_more" class="medium-text" autocomplete="off">
									<option value="lesson"><?php _e('Lesson', 'attest'); ?></option>
								</select>
							</td>
							<td class="assessment-left-pad course_assessment_table_pad_top">
								<select name="attest_course_assessment[1][action]" id="attest_course_assessment_action_more" class="medium-text" autocomplete="off">
									<?php foreach($this->conditions_actions as $key => $item) : ?>
										<option value="<?php echo esc_attr($key); ?>" <?php selected($course_action_more, $key, true); ?>><?php echo esc_attr($item); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="assessment-left-pad course_assessment_table_pad_top">
								<input type="number" class="medium-text" min="0" step="1" max="9999"
								name="attest_course_assessment[1][points]"
								id="attest_course_assessment_points_more"
								autocomplete="off"
								value="<?php echo esc_attr($course_points_more); ?>"
								placeholder="<?php _e('Min. points', 'attest'); ?>"
								<?php echo (($course_type_more == 'lesson' && $course_action_more == 'point') ? false : 'style="display:none;"' ); ?> />

								<input type="number" class="medium-text" min="0" step="1" max="9999"
								name="attest_course_assessment[1][opened]"
								id="attest_course_assessment_opened_more"
								autocomplete="off"
								value="<?php echo esc_attr($course_opened_count_more); ?>"
								placeholder="<?php _e('Min. lessons opened', 'attest'); ?>"
								<?php echo (($course_type_more == 'lesson' && $course_action_more == 'open') ? false : 'style="display:none;"' ); ?> />
							</td>
							<td class="course_assessment_number">
								<span id="course_assessment_number">
									<?php _e('Out of', 'attest'); ?>:
									<span id="lesson_assessment_count_more"><?php echo $lesson_curriculum_data['count']; ?></span>
									<span id="lesson_assessment_points_more"><?php echo $lesson_curriculum_data['points']; ?></span>
								</span>
							</td>
							<td class="assessment-left-pad course_assessment_number">
								<span id="course_assessment_delete" class="material-icons delete-size">delete</span>
							</td>
						</tr>
					</table>
				</p>
			</div>
		<?php
		}


		public function course_duration_content($post_id) {

			$duration = get_post_meta( $post_id, 'attest_course_duration', true );
			$duration = ((isset($duration) && $duration) ? $duration : '1');	?>

			<p>
				<label for="attest_course_duration">
					<input type="checkbox" name="attest_course_duration" id="attest_course_duration" value="1" <?php checked( $duration, 1, true ); ?> />
					&nbsp;<?php _e( 'Show total course duration', 'attest' ); ?>
				</label>
			</p>
		<?php
		}


		public function course_featured_content($post_id) {

			$featured = get_post_meta( $post_id, 'attest_course_featured', true ); ?>

			<p>
				<label for="attest_course_featured">
					<input type="checkbox" name="attest_course_featured" id="attest_course_featured" value="1" <?php checked( $featured, 1, true ); ?> />
					&nbsp;<?php _e( 'Feature this course', 'attest' ); ?>
				</label>
			</p>
		<?php
		}


		public function course_students_content($post_id) {

			$students_data = get_post_meta( $post_id, 'attest_course_students', false );
			$student_enrolled = ( isset($students_data[0]['enrolled']) ? $students_data[0]['enrolled'] : 'auto' );
			$student_enrolled_number = ( isset($students_data[0]['enrolled_number']) ? $students_data[0]['enrolled_number'] : false );
			$student_to_enroll = ( isset($students_data[0]['to_enroll']) ? $students_data[0]['to_enroll'] : 'auto' );
			$student_to_enroll_number = ( isset($students_data[0]['to_enroll_number']) ? $students_data[0]['to_enroll_number'] : false );
			$student_to_excess_error = ( isset($students_data[0]['excess_error']) ? $students_data[0]['excess_error'] : __('Sorry for the inconvenience but the max. capacity for this course was reached. Try a different course or send us a message. Thank you!', 'attest') ); ?>


			<p><strong><?php _e('How many students can enroll in this course?', 'attest'); ?></strong></p>
			<p>
				<label for="attest_course_students_to_enroll_auto">
					<input type="radio" id="attest_course_students_to_enroll_auto"
					class="attest_course_students_to_enroll" name="attest_course_students[to_enroll]"
					value="auto" <?php checked($student_to_enroll, 'auto', true); ?> />
					<?php _e('No limit', 'attest'); ?>
				</label>
			</p><p>
				<label for="attest_course_students_to_enroll_define">
					<input type="radio" id="attest_course_students_to_enroll_define"
					class="attest_course_students_to_enroll" name="attest_course_students[to_enroll]"
					value="define" <?php checked($student_to_enroll, 'define', true); ?> />
					<?php _e('Define', 'attest'); ?>
				</label>
			</p>
			<p>
				<input type="number" class="medium-text" min="0" step="1" max="9999"
				name="attest_course_students[to_enroll_number]"
				id="attest_course_students_to_enroll_number"
				value="<?php echo esc_attr($student_to_enroll_number); ?>"
				placeholder="<?php _e('No of Students', 'attest'); ?>" />
			</p>
			<p id="attest_course_students_excess_error_conatiner">
				<label for="attest_course_students_excess_error">
					<?php _e('Display error message', 'attest'); ?>
				</label><br />
				<textarea id="attest_course_students_excess_error" class="widefat" name="attest_course_students[excess_error]"><?php echo esc_attr($student_to_excess_error); ?></textarea>
			</p>

			<p><strong><?php _e('Show how many students already enrolled', 'attest'); ?></strong></p>
			<p>
				<label for="attest_course_students_enrolled_auto">
					<input type="radio" id="attest_course_students_enrolled_auto"
					class="attest_course_students_enrolled" name="attest_course_students[enrolled]"
					value="auto" <?php checked($student_enrolled, 'auto', true); ?> />
					<?php _e('Calculate automatically', 'attest'); ?>
				</label>
			</p><p>
				<label for="attest_course_students_enrolled_define">
					<input type="radio" id="attest_course_students_enrolled_define"
					class="attest_course_students_enrolled" name="attest_course_students[enrolled]"
					value="define" <?php checked($student_enrolled, 'define', true); ?> />
					<?php _e('Define', 'attest'); ?>
				</label>
			</p>
			<p>
				<input type="number" class="medium-text" min="0" step="1" max="9999"
				name="attest_course_students[enrolled_number]"
				id="attest_course_students_enrolled_number"
				value="<?php echo esc_attr($student_enrolled_number); ?>"
				placeholder="<?php _e('No of Students', 'attest'); ?>" />
			</p>
		<?php
		}


		public function course_announcement_content($post_id) {

			$stored_announcement = get_post_meta( $post_id, 'attest_announcement', true );
			$announcements = $this->get_announcement($post_id);
			?>
			<p>
					<select name="attest_announcement" autocomplete="off">
						<option value="" <?php selected( $stored_announcement, '', true ); ?>><?php _e('None', 'attest') ?></option>
						<?php if(count($announcements) > 0): ?>
						<?php foreach($announcements as $announcement) : ?>
							<option value="<?php echo $announcement['ID']; ?>" <?php selected( $stored_announcement, $announcement['ID'], true ); ?>><?php echo $announcement['title']; ?></option>
						<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</p>
				<p>
					<a href="<?php echo admin_url('edit.php?post_type=attest_course&page=announcement#TB_inline?width=600&height=450&inlineId=' . self::$name); ?>" class="page-title-action" target="_blank"><?php _e( 'Add New Announcement', 'attest' ); ?></a>
				</p>
				<?php
		}


		//Save the post data
		function save( $post_id, $post ) {

			//Check if doing autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			//Verify the nonce before proceeding.
			if ( !isset( $_POST['attest_settings_nonce'] ) || !wp_verify_nonce( $_POST['attest_settings_nonce'], basename( __FILE__ ) ) ) return;

			//Get the post type object.
			$post_type = get_post_type_object( $post->post_type );

			//Check if the current user has permission to edit the post.
			if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) return $post_id;

			if ( isset( $_POST['attest_course_price'] ) ) {
				$to_save = sanitize_text_field($_POST['attest_course_price']);
				update_post_meta( $post_id, 'attest_course_price', $to_save );
			}

			if ( isset( $_POST['attest_course_price_amount'] ) ) {
				$to_save = array_filter($_POST['attest_course_price_amount'], 'sanitize_text_field');
				update_post_meta( $post_id, 'attest_course_price_amount', $to_save );
			}

			if ( isset( $_POST['attest_language'] ) ) {

				$to_save = sanitize_text_field($_POST['attest_language']);
				update_post_meta( $post_id, 'attest_language', $to_save );
			}

			if ( isset( $_POST['attest_course_featured'] ) ) {
				$to_save = sanitize_text_field($_POST['attest_course_featured']);
				update_post_meta( $post_id, 'attest_course_featured', $to_save );
			} else {
				update_post_meta( $post_id, 'attest_course_featured', 0 );
			}

			if ( isset( $_POST['attest_course_duration'] ) ) {
				$to_save = intval(sanitize_text_field($_POST['attest_course_duration']));
				update_post_meta( $post_id, 'attest_course_duration', $to_save );
			} else {
				update_post_meta( $post_id, 'attest_course_duration', 0 );
			}
			if ( isset( $_POST['attest_lesson_duration'] ) ) {
				$to_save = array_filter($_POST['attest_lesson_duration'], 'sanitize_text_field');
				update_post_meta( $post_id, 'attest_lesson_duration', $to_save );
			}

			if ( isset( $_POST['attest_course_students'] ) ) {
				$to_save = array_filter($_POST['attest_course_students'], 'sanitize_text_field');
				update_post_meta( $post_id, 'attest_course_students', $to_save );
			}

			if ( isset( $_POST['attest_announcement'] ) ) {
				$to_save = sanitize_text_field($_POST['attest_announcement']);
				$this->update_announcemnet($post_id, $to_save);
				update_post_meta( $post_id, 'attest_announcement', $to_save );
			}

			if ( isset( $_POST['attest_lesson_assessment'] ) ) {

				$to_save = $this->sanitize_lesson_assessment($_POST['attest_lesson_assessment']);
				update_post_meta( $post_id, 'attest_lesson_assessment', $to_save );
			} elseif ( isset( $_POST['attest_course_assessment'] ) ) {

				$to_save = $this->sanitize_course_assessment($_POST['attest_course_assessment']);
				update_post_meta( $post_id, 'attest_course_assessment', $to_save );
			}

			do_action('attest_do_woo_process', $post_id, $post);
		}


		public function sanitize_course_assessment($input) {

			$output = array();

			if (is_array($input) && count($input) > 0) {
				$output = array();
				foreach ($input as $key => $item) {
					$data = array();
					foreach ($item as $i => $value) {
						$data[$i] = sanitize_text_field($value);
					}
					$output[$key] = $data;
				}
			}

			return $output;
		}


		public function sanitize_lesson_assessment($input) {

			$output = array();

			if (is_array($input) && count($input) > 0) {
				foreach ($input as $key => $item) {
					$output[$key] = sanitize_text_field($item);
				}
			}

			return $output;
		}


		public function get_lesson_curriculum_data($post_id) {

			$data = get_post_meta( $post_id, 'attest_curriculum', true );

			$lesson_arr = array();
			$count_points = $count_opened = $count_lesson = 0;
			if ($data && count($data) > 0) {
				foreach ($data as $key => $section) {

					unset($section['title']);
					$lessons = $section[0];
					foreach ($lessons as $value) {
						$lesson_id = $value['lesson_id'];
						$meta = get_post_meta($lesson_id, 'attest_lesson_assessment', true);
						$type = (isset($meta['type']) ? $meta['type'] : 0);
						if ($type == 'points') {
							$number = (isset($meta['number']) ? $meta['number'] : 0);
							$count_points += $number;
						} elseif ($type == 'if_opened') {
	 						$count_opened ++;
						}
						$count_lesson++;
					}
				}
			}
			$lesson_arr['count'] = $count_lesson;
			$lesson_arr['points'] = $count_points;
			$lesson_arr['opened'] = $count_opened;

			return $lesson_arr;
		}


		public function get_announcement($post_ID) {

			$output = array();

      global $wpdb;

      $table_name = $wpdb->prefix . self::$table;
      $results = $wpdb->get_results(
                  $wpdb->prepare(
                    "SELECT ID, title
                    FROM {$table_name}
                    WHERE related_course = %d
                    ORDER BY date DESC",
                    $post_ID)
                  );

      if (count($results) > 0) {

				foreach ($results as $row) {

					$item = array();
					$item['ID'] = $row->ID;
	        $item['title'] = $row->title;
					$output[] = $item;
				}
      }

      return $output;
		}


		public function update_announcemnet($post_id, $announcement_id) {

			global $wpdb;

			$wpdb->update( $wpdb->prefix . self::$table, array('active' => 0), array('related_course' => $post_id) );

			if ($announcement_id != '') {
				$wpdb->update( $wpdb->prefix . self::$table, array('active' => 1), array('ID' => $announcement_id) );
			}
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


		public function rewards_types() {

			return array(
        ''   				=> __('N/A', 'attest'),
        'points'  	=> __('Points', 'attest'),
        'if_opened' => __('IF Opened', 'attest'),
			);
		}


		public function conditions_types() {

			return array(
        ''   				=> __('N/A', 'attest'),
        'lesson'  	=> __('Lessons', 'attest'),
			);
		}


		public function conditions_actions() {

			return array(
        'point'  => __('Total points earned', 'attest'),
        'open'  	=> __('Opened', 'attest'),
			);
		}
	}
} ?>
