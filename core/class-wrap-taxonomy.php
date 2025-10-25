<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Taxonomy {

    public function register() {
        add_action( 'init', array( $this, 'register_language_taxonomy' ) );
    }

    public function register_language_taxonomy() {

        $labels = array(
            'name'              => __( 'Languages', 'wrap' ),
            'singular_name'     => __( 'Language', 'wrap' ),
            'search_items'      => __( 'Search Languages', 'wrap' ),
            'all_items'         => __( 'All Languages', 'wrap' ),
            'parent_item'       => __( 'Parent Language', 'wrap' ),
            'parent_item_colon' => __( 'Parent Language:', 'wrap' ),
            'edit_item'         => __( 'Edit Language', 'wrap' ),
            'update_item'       => __( 'Update Language', 'wrap' ),
            'add_new_item'      => __( 'Add New Language', 'wrap' ),
            'new_item_name'     => __( 'New Language Name', 'wrap' ),
            'menu_name'         => __( 'Languages', 'wrap' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_menu'      => 'edit.php?post_type=wrap_player',
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'wrap-language' ),
        );

        register_taxonomy( 'wrap_language', array( 'wrap_player' ), $args );
    }
}
