<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Main plugin object to define the plugin
if ( ! class_exists( 'ATTEST_LMS_INSERT_DIFFICULTY' ) ) {

	final class ATTEST_LMS_INSERT_DIFFICULTY {


		protected $data;


    public function __construct() {

			$this->data = array(
				array( __('Beginner', 'attest'), 'beginner' ),
				array( __('Intermediate', 'attest'), 'intermediate' ),
				array( __('Advanced', 'attest'), 'advanced' ),
			);

			$this->insert($this->data);
    }


		public function insert($data) {

			foreach ($data as $item) {

				$term = wp_insert_term(
					$item[0],
					'difficulty',
					array(
						'slug' => $item[1]
					)
				);
			}
		}
  }
}
