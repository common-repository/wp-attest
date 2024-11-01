<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ATTEST_LMS_ANNOUNCEMENT_TABLE' ) ) {

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

	final class ATTEST_LMS_TUTOR_TABLE extends WP_List_Table {


		public function __construct() {

			parent::__construct( [
				'singular' => __( 'Tutor', 'attest' ),
				'plural'   => __( 'Tutors', 'attest' ),
				'ajax'     => false,
			] );
		}


		//fetch the data using custom named method function
		public static function get_Tutors( $per_page = 10, $page_number = 1 ) {

		  $args = array(
			    'role__in' => 'attest_tutor',
          'exclude'  => array(get_current_user_id()),
		  );

      if ( ! empty( $_REQUEST['orderby'] ) ) {
       	$orderby = $this->get_orderby_name( esc_sql( $_REQUEST['orderby'] ) );
        $order = esc_sql( $_REQUEST['order'] );

        $args = array_merge( $args, array(
          'orderby'	  => $orderby,
          'order'		  => $order,
        ) );
      }

		  if ( false != $per_page && false != $page_number ) {
			  $args = array_merge( $args, array(
				  'number'	=> $per_page,
				  'paged'		=> $page_number,
			  ) );
		   }

		   $user_query = new WP_User_Query( $args );
		   $users = $user_query->get_results();

		   return $users;
		}


    //Get orderby name
    private function get_orderby_name( $orderby_get ) {

		 switch ( $orderby_get ) {
			 case 'username':
				 $orderby = 'user_login';
				 break;

			 case 'name':
				 $orderby = 'display_name';
				 break;

			 case 'email':
				 $orderby = 'user_email';
				 break;

			 case 'registered':
				 $orderby = 'user_registered';
				 break;
		 }

		 return $orderby;
    }


		//If there is no data to show
		public function no_items() {

			_e( 'No Tutors Added yet.', 'attest' );
		}


		//How many rows are present there
		public static function record_count() {

      $args = array(
        'role__in' => 'attest_tutor',
        'exclude'  => array(get_current_user_id()),
		  );

		  $user_query = new WP_User_Query( $args );
		  $users = (array) $user_query->get_results();
		  $users_count = count($users);

		  return $users_count;
		}


		//Display columns content
		public function column_title( $item ) {

      $delete_nonce = wp_create_nonce( 'delete_tutor' );
			$title = sprintf( '<a href="%s"><strong>%s</strong></a>', admin_url('user-edit.php?user_id=' . $item->ID), $item->user_login );

      $delete_link = wp_nonce_url( "users.php?action=delete&amp;user={$item->ID}", 'bulk-users' );

      //Change the page instruction where you want to show it
			$actions = array(
          'edit'    => sprintf( '<a href="user-edit.php?user_id=%s">%s</a>', absint( $item->ID ), __( 'Edit', 'attest' ) ),
					'delete'  => sprintf( '<a href="%s">%s</a>', $delete_link, __( 'Delete', 'attest' ) ),
			);
			return $title . $this->row_actions( $actions );
		}


		//set coulmns name
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'username':
					return $this->column_title( $item );
				case 'name':
          return $item->display_name;
				case 'email':
          return $item->user_email;
				case 'registered':
          return date_i18n( get_option( 'date_format' ), strtotime( $item->user_registered ) );
				default:
					return print_r( $item, true );
			}
		}


		//Set checkboxes to delete
		public function column_cb( $item ) {

			return sprintf( '<input type="checkbox" name="users[]" value="%s" />', $item->ID );
		}


		//Columns callback
		public function get_columns() {

      $columns = array(
			   //'cb'         => '<input type="checkbox" />',
			   'username'   => esc_html__( 'Username', 'attest' ),
			   'name'       => esc_html__( 'Name', 'attest' ),
			   'email'      => esc_html__( 'Email', 'attest' ),
			   'registered' => esc_html__( 'Registered', 'attest' ),
		  );

		  return $columns;
		}


		//Bulk delete
		public function get_bulk_actions() {

			$actions = array(
				//'tutors-bulk-delete' => __( 'Delete', 'attest'),
			);

			return $actions;
		}


		//Decide columns to be sortable by array input
		public function get_sortable_columns() {

      $columns = array(
			   'username'   => array( 'username', true ),
			   'name'       => array( 'name', true ),
			   'email'      => array( 'email', true ),
			   'registered' => array( 'registered', true ),
		  );

		  return $columns;
		}


		//Prapare the display variables for screen options
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			/** Process bulk action */
			$this->process_bulk_action();
			$per_page     = $this->get_items_per_page( 'tutors_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			) );

			$this->items = self::get_Tutors( $per_page, $current_page );
		}


		//process bulk action
		public function process_bulk_action() {

			//Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {

				//In our file that handles the request, verify the nonce.
				$delete_nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $delete_nonce, 'delete_tutor' ) ) {
					die( 'Go get a live script kiddies' );
				} else {
					self::delete_tutor( absint( $_GET['instruction'] ) );
			  }
		  }

      //If the delete bulk action is triggered
			if ( isset( $_POST['action'] ) ) {
				if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'tutors-bulk-delete' ) ) {

          $delete_ids = esc_sql( $_POST['users'] );

          $link = '';
          foreach($delete_ids as $key => $id) {
            $link .= '&users[]=' . $id;
          }

          $delete_link = wp_nonce_url( "users.php?action=delete{$link}", 'bulk-users' );

          wp_safe_redirect($delete_link);
          exit;
				}
			}
	   }
   }
} ?>
