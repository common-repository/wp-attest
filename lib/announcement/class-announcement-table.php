<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_ANNOUNCEMENT_TABLE' ) ) {

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

	final class ATTEST_LMS_ANNOUNCEMENT_TABLE extends WP_List_Table {


		public function __construct() {

			parent::__construct( [
				'singular' => __( 'Announcement', 'attest' ),
				'plural'   => __( 'Announcements', 'attest' ),
				'ajax'     => false,
			] );
		}


		//fetch the data using custom named method function
		public static function get_Announcements( $per_page = 10, $page_number = 1 ) {

			global $wpdb;

			//Build the db query base
			$sql = "SELECT * FROM {$wpdb->prefix}attest_announcements";

			//Set filters in the query using $_REQUEST
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
			}
			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			//get the data from database
			$result = $wpdb->get_results( $sql, 'ARRAY_A' );

			return $result;
		}


    //Update announcement status
    public function update_announcement_status( $id ) {

      global $wpdb;
			$results = $wpdb->get_results(
        $wpdb->prepare("SELECT active FROM {$wpdb->prefix}attest_announcements WHERE ID=%d", $id), 'ARRAY_A');

      $is_active = $results[0]['active'];
      switch ($is_active) {
        case 1:
          $set_active = 0;
          break;

        default:
          $set_active = 1;
          break;
      }

      $updated = $wpdb->update( $wpdb->prefix . 'attest_announcements', array('active' => $set_active), array('ID' => $id) );
    }


		//Delete individual data
		public static function delete_announcement( $id ) {

			global $wpdb;
			$wpdb->delete("{$wpdb->prefix}attest_announcements", array( 'ID' => $id ), array( '%s' ) );
		}


		//If there is no data to show
		public function no_items() {

			_e( 'No Items Added yet.', 'attest' );
		}


		//How many rows are present there
		public static function record_count() {

			global $wpdb;

			//Build the db query base
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}attest_announcements";

			return $wpdb->get_var( $sql );
		}


		//Display columns content
		public function column_title( $item ) {

      $delete_nonce = wp_create_nonce( 'delete_announcement' );
      $status_nonce = wp_create_nonce( 'activate_announcement' );
			$title = sprintf( '<strong>%s</strong>', $item['title'] );

      $is_active = $item['active'];
      switch ($is_active) {
        case 1:
          $text_active = __( 'Hide on course page', 'attest' );
          break;

        default:
          $text_active = __( 'Show on course page', 'attest' );
          break;
      }

			//Change the page instruction where you want to show it
			$actions = array(
        'status-change' => sprintf( '<a href="?post_type=attest_course&page=%s&action=%s&instruction=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'status-change', absint( $item['ID'] ), $status_nonce, $text_active ),
				'delete'        => sprintf( '<a href="?post_type=attest_course&page=%s&action=%s&instruction=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce, __( 'Delete', 'attest' ) ),
			);
			return $title . $this->row_actions( $actions );
		}


    //Display column course permalink
    public function column_course( $item ) {

      $course = $item['related_course'];
      $link = get_permalink($course);
      $title = get_the_title($course);

      return '<a href="' . $link . '" target="_blank">' . $title . '</a>';
    }


    public function column_date( $item ) {

      $timestamp = strtotime($item['date']);
      $date_format = get_option('date_format');
	    $time_format = get_option('time_format');
	    $date = date("{$date_format} {$time_format}", $timestamp);

      return $date;
    }


    //Display column status active/inactive
    public function column_active( $item ) {

      $active = $item['active'];

      switch ($active) {
        case 1:
          $is_active = __('Yes', 'attest');
          break;

        default:
          $is_active = __('No', 'attest');
          break;
      }

      return $is_active;
    }


		//set coulmns name
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'title':
					return $this->column_title( $item );
				case 'description':
          return $item[ $column_name ];
				case 'related_course':
          return $this->column_course( $item );
				case 'active':
					return $this->column_active( $item );
        case 'date':
          return $this->column_date( $item );
				default:
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
							'cb'		=> '<input type="checkbox" />',
							'title'	=> __( 'Title', 'attest' ),
							'description'	=> __( 'Description', 'attest' ),
							'related_course'	=> __( 'Course', 'attest' ),
							'active'	=> __( 'Active', 'attest' ),
							'date'	=> __( 'Created at', 'attest' ),
						);
			return $columns;
		}


		//Decide columns to be sortable by array input
		public function get_sortable_columns() {

			$sortable_columns = array(
				'title' => array( 'title', true ),
  			'date' => array( 'date', true ),
			);
			return $sortable_columns;
		}


		//Determine bulk actions in the table dropdown
		public function get_bulk_actions() {

			$actions = array(
        'bulk-delete' => __( 'Delete', 'attest'),
        'bulk-status-change' => __( 'Show/Hide', 'attest'),
      );
			return $actions;
		}


		//Prapare the display variables for screen options
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			$this->process_bulk_action();
			$per_page     = $this->get_items_per_page( 'announcements_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			) );

			$this->items = self::get_Announcements( $per_page, $current_page );
		}


		//process bulk action
		public function process_bulk_action() {

			//Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {

				//In our file that handles the request, verify the nonce.
				$delete_nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $delete_nonce, 'delete_announcement' ) ) {
					die( 'Go get a live script kiddies' );
				} else {
					self::delete_announcement( absint( $_GET['instruction'] ) );
			  }
		  }

      if ( 'status-change' === $this->current_action() ) {

				//In our file that handles the request, verify the nonce.
				$status_nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $status_nonce, 'activate_announcement' ) ) {
					die( 'Go get a live script kiddies' );
				} else {
					self::update_announcement_status( absint( $_GET['instruction'] ) );
			  }
		  }

			//If the delete bulk action is triggered
			if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) ) {
        if (isset($_POST['bulk-select'])) {
          $delete_ids = esc_sql( $_POST['bulk-select'] );
				  foreach ( $delete_ids as $id ) {
					  self::delete_announcement( absint($id) );
				  }
        }
			}

      if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-status-change' ) ) {
        if (isset($_POST['bulk-select'])) {
          $select_ids = esc_sql( $_POST['bulk-select'] );
          foreach ( $select_ids as $id ) {
            self::update_announcement_status( absint($id) );
          }
        }
      }
	   }
   }
} ?>
