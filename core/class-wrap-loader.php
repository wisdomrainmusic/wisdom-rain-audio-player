<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Loader {

    /**
     * Stored module instances to keep hooks active after init.
     *
     * @var WRAP_Shortcode
     */
    private $shortcode;

    public function init() {
        require_once WRAP_PATH . 'core/class-wrap-cpt.php';
        require_once WRAP_PATH . 'core/class-wrap-taxonomy.php';
        require_once WRAP_PATH . 'admin/class-wrap-metabox.php';
        require_once WRAP_PATH . 'core/class-wrap-shortcode.php';

        ( new WRAP_CPT() )->register();
        ( new WRAP_Taxonomy() )->register();
        ( new WRAP_Metabox() )->register();

        $this->shortcode = new WRAP_Shortcode();
        $this->shortcode->register();

        add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );
    }

    private function pick_file( $candidates ) {
        foreach ( $candidates as $rel ) {
            $abs = WRAP_PATH . $rel;
            if ( file_exists( $abs ) ) {
                return array( $rel, filemtime( $abs ) );
            }
        }

        return array( null, null );
    }

    public function register_frontend_assets() {
        list( $css_rel, $css_ver ) = $this->pick_file( array(
            'assets/css/wrap-frontend.css',
            'assets/css/wrap-player.css',
        ) );

        list( $js_rel, $js_ver ) = $this->pick_file( array(
            'frontend/wrap-frontend.js',
            'frontend/wrap-player.js',
        ) );

        if ( $css_rel ) {
            wp_register_style( 'wrap-player', WRAP_URL . $css_rel, array(), $css_ver ?: '3.1' );
        }

        if ( $js_rel ) {
            wp_register_script( 'wrap-player', WRAP_URL . $js_rel, array(), $js_ver ?: '3.1', true );
        }
    }
}
