<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Main plugin object to define the plugin
if ( ! class_exists( 'ATTEST_LMS_CPT_LESSON' ) ) {

	final class ATTEST_LMS_CPT_LESSON {


    private $labels;
    private $args;

    public function __construct() {

      $this->labels = $this->labels();
      $this->args = $this->args($this->labels);

      register_post_type( 'attest_lesson', $this->args );
    }


    public function labels() {

      $labels = array(
        'name'                => _x( 'Lessons', 'Post Type General Name', 'attest' ),
        'singular_name'       => _x( 'Lesson', 'Post Type Singular Name', 'attest' ),
        'menu_name'           => __( 'Attest', 'attest' ),
        'parent_item_colon'   => __( 'Parent Lesson', 'attest' ),
        'all_items'           => __( 'Lessons', 'attest' ),
        'view_item'           => __( 'View Lesson', 'attest' ),
        'add_new_item'        => __( 'Add New Lesson', 'attest' ),
        'add_new'             => __( 'Add New', 'attest' ),
        'edit_item'           => __( 'Edit Lesson', 'attest' ),
        'update_item'         => __( 'Update Lesson', 'attest' ),
        'search_items'        => __( 'Search Lesson', 'attest' ),
        'not_found'           => __( 'Not Found', 'attest' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'attest' ),
      );

      return $labels;
    }


    public function args($labels) {

      $args = array(
          'label'               => __( 'Lessons', 'attest' ),
          'description'         => __( 'Lessons for Attest', 'attest' ),
          'labels'              => $labels,
          'supports'            => array( 'title', 'editor', 'thumbnail' ),
          'taxonomies'          => array(),
          'hierarchical'        => true,
          'public'              => true,
					'rewrite'			  			=> array( 'slug' => 'lesson' ),
          'show_ui'             => true,
          'show_in_menu'        => 'edit.php?post_type=attest_course',
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'menu_position'       => 30,
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
