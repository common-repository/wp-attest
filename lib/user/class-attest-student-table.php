<?php
/**
 * Implimentation of WordPress inbuilt functions for creating an extension of a default table class.
 *
 * $myPluginNameTable = new myPluginNameTable();
 * $myPluginNameTable->prepare_items();
 * $myPluginNameTable->display();
 *
 */
if ( ! class_exists( 'ATTEST_LMS_PRO_STUDENT_TABLE' ) ) {

	if ( ! class_exists( 'WP_List_Table' ) ) {
    	require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	final class ATTEST_LMS_PRO_STUDENT_TABLE extends WP_List_Table {


		public function __construct() {

			parent::__construct( [
				'singular' => __( 'Student', 'attest-pro' ),
				'plural'   => __( 'Students', 'attest-pro' ),
				'ajax'     => false,
			] );
		}


		//fetch the data using custom named method function
		public static function get_Students( $per_page = 5, $page_number = 1 ) {

			global $wpdb;

			//Build the db query base
			$sql = "SELECT * FROM {$wpdb->prefix}attest_students";

			//Set filters in the query using $_REQUEST
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			}
			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			//get the data from database
			$result = $wpdb->get_results( $sql, 'ARRAY_A' );

			return $result;
		}


		//If there is no data to show
		public function no_items() {

			_e( 'No Students Added yet.', 'attest-pro' );
		}


		// Delete individual data
		public static function delete_student( $id ) {

			global $wpdb;

			$wpdb->delete( "{$wpdb->prefix}attest_students", array( 'ID' => $id ), array( '%s' ) );
		}


		//How many rows are present there
		public static function record_count() {

			global $wpdb;

			//Build the db query base
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}attest_students";

			return $wpdb->get_var( $sql );
		}


		//Display column courses
		public function column_course_id( $item ) {

			$course_id = maybe_unserialize($item['course_id']);

			if(is_array($course_id) && count($course_id) > 1) {
				$courses = __('Multiple', 'attest-pro');
			} else {
				$courses = '<a href="' . get_permalink($course_id) . '" target="_blank">' . get_the_title( $course_id ) . '</a>';
			}

			return $courses;
		}


		//Display columns content
		public function column_name( $item ) {

			$delete_nonce = wp_create_nonce( 'delete_student' );
			$title = sprintf( '<strong>%s</strong>', $item['name'] );

			//Change the page instruction where you want to show it
			$actions = array(
					'delete' => sprintf( '<a href="edit.php?post_type=attest_course&page=%s&action=%s&instruction=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce, __('Are you sure you want to delete? The action is irreversible.', 'attest'), __( 'Delete', 'attest' ) )
					);

			return $title . $this->row_actions( $actions );

			return $title;
		}


		//Display date
		public function column_date( $item ) {

			$date_format = get_option('date_format');
			$date = date($date_format, strtotime( $item['date'] ));

			return $date;
		}


		//set coulmns name
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'name':
					return $this->column_course_id( $item );
				case 'email':
				case 'region':
				case 'city':
					return $item[ $column_name ];
				case 'course_id':
					return $this->column_course_id( $item );
				case 'date' :
					return $this->column_date( $item );

				default:
					//Show the whole array for troubleshooting purposes
					return print_r( $item, true );
			}
		}


		//Set checkboxes to delete
		public function column_cb( $item ) {

			return sprintf( '<input type="checkbox" name="bulk-select[]" value="%s" />', $item['ID'] );
		}


		//Columns callback
		public function get_columns() {

			$columns = array(
							'cb'					 => '<input type="checkbox" />',
							'name'	       => __( 'Name', 'attest-pro' ),
							'email'	       => __( 'Email', 'attest-pro' ),
							'course_id'    => __( 'Course Title', 'attest-pro' ),
							'region'       => __( 'Region', 'attest-pro' ),
							'city'         => __( 'City', 'attest-pro' ),
							'date'         => __( 'Date', 'attest-pro' ),
						);
			return $columns;
		}


		//Decide columns to be sortable by array input
		public function get_sortable_columns() {

			$sortable_columns = array(
				'name'   => array( 'name', true ),
				'email'  => array( 'email', false ),
				'region' => array( 'region', false ),
				'city'   => array( 'city', false ),
				'date'   => array( 'date', false ),
			);
			return $sortable_columns;
		}


		//Bulk delete
		public function get_bulk_actions() {

			$actions = array(
				'students-bulk-delete' => __( 'Delete', 'attest'),
			);

			return $actions;
		}


		//Prapare the display variables for screen options
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			/** Process bulk action */
			$this->process_bulk_action();
			$per_page     = $this->get_items_per_page( 'students_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			) );

			$this->items = self::get_Students( $per_page, $current_page );
		}


		public function process_bulk_action() {

			//Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {

				//In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $nonce, 'delete_student' ) ) {
					die( 'Go get a live script kiddies' );
				} else {
					self::delete_student( absint( $_GET['instruction'] ) ); //Remember the instruction param from column_name method
				}
			}

			//If the delete bulk action is triggered
			if ( isset( $_POST['action'] ) ) {
				if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'students-bulk-delete' ) ) {

					$delete_ids = esc_sql( $_POST['bulk-select'] );
					foreach ( $delete_ids as $id ) {
						self::delete_student( $id );
					}
				}
			}
		}
	}
} ?>
