<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Backend settings for reCaptcha script
 */
if ( ! class_exists( 'ATTEST_RE_CAPTCHA_SCRIPT' ) ) {

	final class ATTEST_RE_CAPTCHA_SCRIPT {


		public $register_post_id;
		public $site_key;
		public $post_id;


    public function __construct() {

			$this->register_post_id = get_option('attest_template_register');
			$this->site_key = get_option('attest_site_key_recaptcha');

			add_action( 'wp_head', array($this, 're_capthcha_script') );
    }


		public function re_capthcha_script() {

			$this->post_id = get_the_ID();
			if ($this->post_id == $this->register_post_id) : ?>

			<script type="text/javascript">
      	var onloadCallback = function() {
        	grecaptcha.render('attest_g_recaptcha', {
          	'sitekey' : '<?php echo $this->site_key; ?>'
        	});
      	};
    	</script>

		<?php endif;
		}
  }
}
