<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Main plugin object to define the plugin
if ( ! class_exists( 'ATTEST_LMS_CPT_COURSE' ) ) {

	final class ATTEST_LMS_CPT_COURSE {


    private $labels;
    private $args;
		private static $menu_svg = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMjAwcHgiIGhlaWdodD0iMjAxcHgiIHZpZXdCb3g9IjAgMCAyMDAgMjAxIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPHRpdGxlPndwLWF0dGVzdC1mYXYtZ3JheTwvdGl0bGU+CiAgICA8ZyBpZD0iQnJhbmQiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJMb2dvIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNjYwLjAwMDAwMCwgLTgyLjAwMDAwMCkiPgogICAgICAgICAgICA8ZyBpZD0id3AtYXR0ZXN0LWZhdi1ncmF5IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2NjAuMDAwMDAwLCA4Mi4wMDAwMDApIj4KICAgICAgICAgICAgICAgIDxjaXJjbGUgaWQ9InBsYWNlaG9sZGVyIiBmaWxsPSIjMDAwMDAwIiBvcGFjaXR5PSIwIiBjeD0iMTAwIiBjeT0iMTAwIiByPSIxMDAiPjwvY2lyY2xlPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTEwMCwyMy4wNzY5MjMxIEMxNDIuNDgzNDQyLDIzLjA3NjkyMzEgMTc2LjkyMzA3Nyw1Ny41MTY1NTc3IDE3Ni45MjMwNzcsMTAwIEMxNzYuOTIzMDc3LDEyNi44NTM5ODUgMTYzLjE2MjUwMSwxNTAuNDk0MDE1IDE0Mi4zMDc5MTgsMTY0LjI1MzUyMSBMMTQyLjMwNzY5MiwyMDAgTDEyMS4xNTM4NDYsMTkyLjk0ODg0NiBMMTAwLDIwMCBMMTAwLDE3Ni45MjMwNzcgQzExNS42Mjk0NTcsMTc2LjkyMzA3NyAxMzAuMTcwMjEsMTcyLjI2MTc3OCAxNDIuMzA3OTE4LDE2NC4yNTM1MjEgTDE0Mi4zMDc2OTIsMTAwIEwxNDIuMzAyMDI0LDEwMC42OTk2MzUgQzE0MS45Mjg0MTQsMTIzLjc0MjgzNSAxMjMuMTMyMjM0LDE0Mi4zMDc2OTIgMTAwLDE0Mi4zMDc2OTIgTDEwMCwxNDIuMzA3NjkyIEwxMDAsMTc2LjkyMzA3NyBMMTAwLDE3Ni45MjMwNzcgQzU3LjUxNjU1NzcsMTc2LjkyMzA3NyAyMy4wNzY5MjMxLDE0Mi40ODM0NDIgMjMuMDc2OTIzMSwxMDAgQzIzLjA3NjkyMzEsNTcuNTE2NTU3NyA1Ny41MTY1NTc3LDIzLjA3NjkyMzEgMTAwLDIzLjA3NjkyMzEgWiBNMTAwLDU3LjY5MjMwNzcgQzc2LjYzNDEwNjcsNTcuNjkyMzA3NyA1Ny42OTIzMDc3LDc2LjYzNDEwNjcgNTcuNjkyMzA3NywxMDAgQzU3LjY5MjMwNzcsMTIzLjM2NTg5MyA3Ni42MzQxMDY3LDE0Mi4zMDc2OTIgMTAwLDE0Mi4zMDc2OTIgTDEwMCwxMDAgTDE0Mi4zMDc2OTIsMTAwIEwxNDIuMzA3NjkyLDEwMCBDMTQyLjMwNzY5Miw3Ni42MzQxMDY3IDEyMy4zNjU4OTMsNTcuNjkyMzA3NyAxMDAsNTcuNjkyMzA3NyBaIiBpZD0iaWNvbiIgZmlsbD0iI0E2QUFBRiI+PC9wYXRoPgogICAgICAgICAgICA8L2c+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=';

    public function __construct() {

      $this->labels = $this->labels();
      $this->args = $this->args($this->labels);

      register_post_type( 'attest_course', $this->args );
    }


    public function labels() {

      $labels = array(
        'name'                => _x( 'Courses', 'Post Type General Name', 'attest' ),
        'singular_name'       => _x( 'Course', 'Post Type Singular Name', 'attest' ),
        'menu_name'           => __( 'Attest', 'attest' ),
        'parent_item_colon'   => __( 'Parent Course', 'attest' ),
        'all_items'           => __( 'Courses', 'attest' ),
        'view_item'           => __( 'View Course', 'attest' ),
        'add_new_item'        => __( 'Add New Course', 'attest' ),
        'add_new'             => __( 'Add New', 'attest' ),
        'edit_item'           => __( 'Edit Course', 'attest' ),
        'update_item'         => __( 'Update Course', 'attest' ),
        'search_items'        => __( 'Search Course', 'attest' ),
        'not_found'           => __( 'Not Found', 'attest' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'attest' ),
      );

      return $labels;
    }


    public function args($labels) {

      $args = array(
          'label'               => __( 'courses', 'attest' ),
          'description'         => __( 'Courses for Attest', 'attest' ),
          'labels'              => $labels,
          'supports'            => array( 'title', 'editor', 'thumbnail' ),
          'taxonomies'          => array( 'topics', 'post_tag' ),
          'hierarchical'        => true,
          'public'              => true,
					'rewrite'			  			=> array( 'slug' => 'course' ),
          'show_ui'             => true,
          'show_in_menu'        => true,
		  		'menu_icon' 					=> 'data:image/svg+xml;base64,' . self::$menu_svg,
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'menu_position'       => 5,
          'can_export'          => true,
          'has_archive'         => true,
          'exclude_from_search' => false,
          'publicly_queryable'  => true,
          'capability_type'     => 'post',
          'show_in_rest'        => true,
      );

      return $args;
    }
  }
}
