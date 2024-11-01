<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//Main plugin object to define the plugin
if ( ! class_exists( 'ATTEST_LMS_TAX_TOPIC' ) ) {

	final class ATTEST_LMS_TAX_TOPIC {


    private $labels;
    private $args;

    public function __construct() {

      $this->labels = $this->labels();
      $this->args = $this->args($this->labels);

      register_taxonomy('topics', 'course', $this->args);
    }


    public function labels() {

      $labels = array(
        'name'                        => _x( 'Topics', 'Taxonomy general name', 'attest' ),
        'singular_name'               => _x( 'Topic', 'Taxonomy singular name', 'attest' ),
        'search_items'                =>  __( 'Search Topics', 'attest' ),
        'popular_items'               => __( 'Popular Topics', 'attest' ),
        'all_items'                   => __( 'All Topics', 'attest' ),
        'parent_item'                 => __( 'Parent Topic Category', 'attest' ),
        'edit_item'                   => __( 'Edit Topic', 'attest' ),
        'update_item'                 => __( 'Update Topic', 'attest' ),
        'add_new_item'                => __( 'Add New Topic', 'attest' ),
        'new_item_name'               => __( 'New Topic Name', 'attest' ),
        'separate_items_with_commas'  => __( 'Separate topics with commas', 'attest' ),
        'add_or_remove_items'         => __( 'Add or remove topics', 'attest' ),
        'choose_from_most_used'       => __( 'Choose from the most used topics', 'attest' ),
        'menu_name'                   => __( 'Topics', 'attest' ),
      );

      return $labels;
    }


    public function args($labels) {

      $args = array(
        'label'             => 'Topic',
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'args'              => array( 'orderby' => 'term_order' ),
        'rewrite'           => array( 'slug' => 'topic', 'with_front' => true, 'hierarchical' => true ),
        'query_var'         => true,
        'show_in_rest'      => true,
      );

      return $args;
    }
  }
}
