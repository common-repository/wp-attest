<?php
/**
 Plugin Name: WP Attest
 Plugin URI: http://www.wpattest.com/
 Description: Attest is a <strong>WordPress LMS Plugin</strong>, good for Tutors, that helps create free <strong>Online Courses</strong>, <strong>Classes</strong> and <strong>Lessons</strong> with a Gutenberg-ready interface.
 Version: 1.7.4
 Author: WP Attest
 Author URI: https://profiles.wordpress.org/attest/
 Text Domain: attest
 Domain Path: /asset/ln
 License: GPLv3
 License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH')) exit;


//Define base names
defined('ATTEST_LMS_DEBUG') or define('ATTEST_LMS_DEBUG', false);

defined('ATTEST_LMS_PATH') or define('ATTEST_LMS_PATH', plugin_dir_path(__FILE__));
defined('ATTEST_LMS_FILE') or define('ATTEST_LMS_FILE', plugin_basename(__FILE__));

defined('ATTEST_LMS_EXECUTE') or define('ATTEST_LMS_EXECUTE', plugin_dir_path(__FILE__).'src/');
defined('ATTEST_LMS_HELPER') or define('ATTEST_LMS_HELPER', plugin_dir_path(__FILE__).'helper/');
defined('ATTEST_LMS_TRANSLATE') or define('ATTEST_LMS_TRANSLATE', plugin_basename( plugin_dir_path(__FILE__).'asset/ln/'));

defined('ATTEST_LMS_JS') or define('ATTEST_LMS_JS', plugins_url('/asset/js/', __FILE__));
defined('ATTEST_LMS_CSS') or define('ATTEST_LMS_CSS', plugins_url('/asset/css/', __FILE__));
defined('ATTEST_LMS_IMAGE') or define('ATTEST_LMS_IMAGE', plugins_url('/asset/img/', __FILE__));


//The Plugin
require_once('autoload.php');
function wp_attest_lms() {
  if ( class_exists( 'ATTEST_LMS_BUILD' ) )
    new ATTEST_LMS_BUILD();
}

wp_attest_lms(); ?>
