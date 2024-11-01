<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build a sample metabox in editor screen
 */
if ( ! class_exists( 'ATTEST_LMS_INTRO_VIDEO_METABOX' ) ) {

	final class ATTEST_LMS_INTRO_VIDEO_METABOX {

    private $video_types;

		public function __construct() {

      $this->video_types = $this->video_types();

			add_filter( 'wp_kses_allowed_html', array($this, 'custom_wpkses_post_tags'), 10, 2 );
			add_action( 'add_meta_boxes', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		}


		public function custom_wpkses_post_tags( $tags, $context ) {

			if ( 'post' === $context ) {
				$tags['iframe'] = array(
					'src'             => true,
					'height'          => true,
					'width'           => true,
					'frameborder'     => true,
					'allowfullscreen' => true,
				);
				$tags['video'] = array(
					'controls'        => true,
					'height'          => true,
					'width'           => true,
				);
				$tags['source'] = array(
					'src'        => true,
					'height'          => true,
					'width'           => true,
				);
				$tags['script'] = array(
					'src'        => true
				);
			}

			return $tags;
		}


		public function register() {

			add_meta_box(
				'attest_intro_video',
				esc_html__( 'Featured video', 'attest' ),
				array( $this, 'render' ),
				array('attest_course', 'attest_lesson'),
				'normal',
				'core'
			);
		}


		public function render() {

			global $post;

			wp_nonce_field( basename( __FILE__ ), 'attest_intro_video_nonce' );

      $stored_video_data = get_post_meta( $post->ID, 'attest_intro_video', false );
      $stored_video = array_key_exists(0, $stored_video_data) ? $stored_video_data[0] : array('type' => '', 'url' => '', 'embed' => '');
			$stored_type = (isset($stored_video['type']) ? $stored_video['type'] : false);
			$stored_link = (isset($stored_video['url']) ? $stored_video['url'] : false);
			$stored_wistia = (isset($stored_video['wistia']) ? $stored_video['wistia'] : false);
			$stored_embed = (isset($stored_video['embed']) ? $stored_video['embed'] : false);
			?>

			<p><?php _e('Replace the featured image with a video.', 'attest'); ?></p>
			<table style="">
				<tr>
					<td><?php _e('Source', 'attest') ?></td>
					<td style="padding-left:20px;" id="attest_intro_video_text"><?php _e('Value (URL Path or Code)', 'attest') ?></td>
				</tr>
				<tr>
					<td>
						<select name="attest_intro_video[type]" id="attest_intro_video_type" class="medium-text" autocomplete="off">
		          <?php foreach($this->video_types as $key => $type) : ?>
		            <option value="<?php echo $key; ?>" <?php selected( $stored_type, $key, true ); ?>><?php echo $type; ?></option>
		          <?php endforeach; ?>
		        </select>
							<input id="attest_intro_video_upload" class="components-button editor-post-preview is-tertiary" style="margin-left:20px;" type="button" value="<?php _e('Upload', 'attest'); ?>" />
					</td>
					<td style="padding-left:20px;">
						<input type="text" class="large-text" style="width:450px;" name="attest_intro_video[url]" id="attest_intro_video_url" autocomplete="off" value="<?php echo esc_url_raw($stored_link); ?>" />
						<textarea class="widefat" style="width:450px;"  rows="1" name="attest_intro_video[embed]" id="attest_intro_video_embed" autocomplete="off"><?php echo wp_kses_post($stored_embed); ?></textarea>
						<textarea class="widefat" style="width:450px;"  rows="1" name="attest_intro_video[wistia]" id="attest_intro_video_wistia" autocomplete="off"><?php echo wp_kses_post($stored_wistia); ?></textarea>
					</td>
				</tr>
			</table>
			<?php
		}


		//Save the post data
		function save( $post_id, $post ) {

			//Check if doing autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			//Verify the nonce before proceeding.
			if ( !isset( $_POST['attest_intro_video_nonce'] ) || !wp_verify_nonce( $_POST['attest_intro_video_nonce'], basename( __FILE__ ) ) ) return;

			//Get the post type object.
			$post_type = get_post_type_object( $post->post_type );

			//Check if the current user has permission to edit the post.
			if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) return $post_id;

			if ( isset( $_POST['attest_intro_video'] ) ) {

				$to_save = $this->sanitize($_POST['attest_intro_video']);
				update_post_meta( $post_id, 'attest_intro_video', $to_save );
			}
		}


		private function sanitize($video) {

			$data = array();
			if (is_array($video)) {

				foreach($video as $key => $item) {

					switch ($key) {
						case 'type':
							$data[$key] = sanitize_text_field($item);
							break;

						case 'url':
							$data[$key] =  sanitize_text_field($item);
							break;

						case 'embed':
							$data[$key] = wp_kses_post($item);
							break;

						case 'wistia':
							$data[$key] = wp_kses_post($item);
							break;
					}
				}
			}

			return $data;
		}


    public function video_types() {

			return array(
				'none'          => __('None', 'attest'),
				'upload'        => __('Upload', 'attest'),
        'external_url'  => __('External URL', 'attest'),
        'youtube_url'   => __('YouTube', 'attest'),
        'vimeo_url'     => __('Vimeo', 'attest'),
        'wistia_code'   => __('Wistia', 'attest'),
        'embed'         => __('Embedded', 'attest'),
			);
		}
	}
} ?>
