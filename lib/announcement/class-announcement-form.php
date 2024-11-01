<?php
/**
 * Add scripts to the plugin. CSS and JS.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_ANNOUNCEMENT_FORM' ) ) {

	final class ATTEST_LMS_ANNOUNCEMENT_FORM {

    public static $name = 'announcement-form';
    public static $table = 'attest_announcements';


    public function create_announcement() {

      if ( isset( $_POST['announcement_form_nonce'] ) && wp_verify_nonce( esc_attr($_POST['announcement_form_nonce']), basename( __FILE__ ) ) ) {

        if (isset($_POST['announcement_submit']) && isset($_POST['announcement_title']) && isset($_POST['announcement_desc']) && isset($_POST['announcement_course'])) {

          $title = sanitize_text_field($_POST['announcement_title']);
          $description = wp_kses_post($_POST['announcement_desc']);
          $course = intval(sanitize_text_field($_POST['announcement_course']));
					$email = (isset($_POST['announcement_email']) ? intval(sanitize_text_field($_POST['announcement_email'])) : 0 );

					if (empty($title) || empty($description) || empty($course)) {

						echo $this->empty_msg();

					} else {

						if ($email == 1) {
							$this->trigger_email($title, $description, $course);
						}

	          global $wpdb;

	          $table = $wpdb->prefix . self::$table;
	          $data = array(
	            'title' => $title,
	            'description' => $description,
	            'related_course' => $course,
	            'active' => 1,
	            'trigger_email' => $email,
	            'date' => current_time('mysql'),
	          );
	          $format = array('%s','%s','%d','%d','%d','%s');
	          $wpdb->insert($table,$data,$format);

	          $insert = $wpdb->insert_id;

	          if ($insert) {
	            echo $this->success_msg();
	          } else {
							echo $this->failed_msg();
						}
					}
        }
      }
    }


		public function empty_msg() { ?>

			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'Title, description and course field is mandetory. Please try again.', 'attest' ); ?></p>
			</div>
		<?php
		}


		public function failed_msg() { ?>

			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'Could not add announcement. Please try again.', 'attest' ); ?></p>
			</div>
		<?php
		}


    public function success_msg() { ?>

      <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Successfully added announcement. Change it\'s activation status in the table bellow.', 'attest' ); ?></p>
      </div>
    <?php
    }


    public function trigger() {

      return '<a href="#TB_inline?width=600&height=450&inlineId=' . self::$name . '" class="thickbox page-title-action">' . __( 'Add New', 'attest' ) . '</a>';
    }


    public function body() {

      $courses = $this->get_courses();

      $body =
      '<div id="' . self::$name . '" style="display:none;">
        <h2>' . __('New Announcement', 'attest') . '</h2>
				<p>' . __('You can create a new announcement that will be published on the course page and notify all the students of that course by email (optional). You cannot edit the announcement. You can hide the announcement from the course page or delete it completely', 'attest') . '</p>
        <form action="" method="post">'
       . wp_nonce_field( basename( __FILE__ ), 'announcement_form_nonce' ) .
        '
        <p>
          <label>' . __('Title', 'attest') . '</label><br />
          <input type="text" name="announcement_title" class="large-text" value="" required/>
        </p>
        <p>
          <label>' . __('Description', 'attest') . '</label><br />
          <textarea class="widefat" rows="4" name="announcement_desc" required></textarea>
					<br />
					<span>You can use HTML tags like <code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;u&gt;</code>, <code>&lt;a&gt;</code>.<span>
        </p>
        <p>
          <label>' . __('Select Course', 'attest') . '</label><br />
          <select name="announcement_course" id="announcement_course_name_trigger" required>';

						$body .= '<option value="">' . __('Select', 'attest') . '</option>';
            foreach($courses as $course) {
              $body .= '<option value="' . intval($course->ID) . '" data-name="' . esc_attr($course->post_title) . '" >' . esc_attr($course->post_title) . '</option>';
            }

          $body .= '</select>
        </p>
        <p>
					<label><input type="checkbox" name="announcement_email" value="1" checked/>' . sprintf( __('Notify the %s students by email', 'attest'), '<span id="announcement_course_name"></span>') . '</label>
        </p>
        <p>
          <input type="submit" name="announcement_submit" class="button button-primary" value="' . __('Release announcement', 'attest') . '" />
        </p>
        </form>
      </div>';

      return $body;
    }


    public function get_courses() {

      $args = array(
        'post_type' => 'attest_course',
        'post_status' => 'publish',
        'numberposts' => -1,
        'order'    => 'ASC'
      );

      $posts = get_posts($args);

      return $posts;
    }


		public function trigger_email($title, $description, $course) {

			$send_email = new ATTEST_LMS_ANNOUNCEMENT_EMAIL();
			$send_email->title = $title;
			$send_email->description = $description;
			$send_email->course_id = $course;
			$send_email->execute();
		}
  }
} ?>
