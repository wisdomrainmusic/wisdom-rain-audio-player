<?php
/*
Plugin Name: Wisdom Rain Audio Player
Description: Modular audio player with playlist and continue-listening logic.
Version: 3.0
Author: Wisdom Rain
Text Domain: wrap
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WRAP_PATH', plugin_dir_path( __FILE__ ) );
define( 'WRAP_URL', plugin_dir_url( __FILE__ ) );

require_once WRAP_PATH . 'core/class-wrap-loader.php';

function wrap_init_plugin() {
    $loader = new WRAP_Loader();
    $loader->init();
}
add_action( 'plugins_loaded', 'wrap_init_plugin' );
