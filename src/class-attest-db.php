<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * DB installation class
 */
if ( ! class_exists( 'ATTEST_LMS_DB' ) ) {

	class ATTEST_LMS_DB {


		public $table; //string. Declare before using
		public $sql; //string. Declare before using


		public function __construct() {

			$this->up_path = ABSPATH . 'wp-admin/includes/upgrade.php';
			$this->build();
		}


		//Define the necessary database tables
		public function build() {

			global $wpdb;
			$wpdb->hide_errors();
			$this->table_name = $wpdb->prefix . $this->table;
			update_option( '_plugin_db_exist', 0 );
			if ( $wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") != $this->table_name ) {
				$execute_sql = $this->execute( $this->table_name, $this->collate(), $this->sql );
				dbDelta( $execute_sql );
			}
		}


		//Define the variables for db table creation
		public function collate() {

			global $wpdb;
			$wpdb->hide_errors();
			$collate = "";
		    if ( $wpdb->has_cap( 'collation' ) ) {
				if( ! empty($wpdb->charset ) )
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				if( ! empty($wpdb->collate ) )
					$collate .= " COLLATE $wpdb->collate";
		    }
    		require_once( $this->up_path );
			return $collate;
		}


		//SQL query to create the main plugin table.
		public function execute( $table_name, $collate, $sql ) {
			return "CREATE TABLE $table_name ( $sql ) $collate;";
		}


		//Check options and tables and output the info to check if db install is successful
		public function __destruct() {
			global $wpdb;
			$this->table_name = $wpdb->prefix . $this->table;
			if ( $wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") == $this->table_name ) {
				update_option( '_attest_db_exist', 1 );
			}
		}
	}
} ?>
