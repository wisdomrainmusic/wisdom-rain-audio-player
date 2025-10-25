<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_CPT {

    public function register() {
        add_action( 'init', array( $this, 'register_cpt' ) );
    }

    public function register_cpt() {
        $labels = array(
            'name'               => __( 'Players', 'wrap' ),
            'singular_name'      => __( 'Player', 'wrap' ),
            'menu_name'          => __( 'Players', 'wrap' ),
            'add_new'            => __( 'Add New', 'wrap' ),
            'add_new_item'       => __( 'Add New Player', 'wrap' ),
            'edit_item'          => __( 'Edit Player', 'wrap' ),
            'new_item'           => __( 'New Player', 'wrap' ),
            'all_items'          => __( 'All Players', 'wrap' ),
            'view_item'          => __( 'View Player', 'wrap' ),
            'search_items'       => __( 'Search Players', 'wrap' ),
            'not_found'          => __( 'No players found', 'wrap' ),
            'not_found_in_trash' => __( 'No players found in Trash', 'wrap' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'show_ui'            => true,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-format-audio',
            'supports'           => array( 'title', 'thumbnail' ),
            'has_archive'        => false,
            'rewrite'            => array( 'slug' => 'players' ),
            'show_in_rest'       => false,
        );

        register_post_type( 'wrap_player', $args );

        // === Shortcode column ===
        add_filter( 'manage_edit-wrap_player_columns', function( $columns ) {
            $columns['shortcode'] = __( 'Shortcode', 'wrap' );
            return $columns;
        } );

        add_action( 'manage_wrap_player_posts_custom_column', function( $column, $post_id ) {
            if ( $column === 'shortcode' ) {
                echo '<code>[wrap_player id="' . esc_attr( $post_id ) . '"]</code>';
            }
        }, 10, 2 );
    }
}
