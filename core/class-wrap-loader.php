<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Loader {

    public function init() {
        require_once WRAP_PATH . 'core/class-wrap-cpt.php';
        require_once WRAP_PATH . 'core/class-wrap-taxonomy.php';
        require_once WRAP_PATH . 'admin/class-wrap-metabox.php';
        require_once WRAP_PATH . 'core/class-wrap-shortcode.php';

        ( new WRAP_CPT() )->register();
        ( new WRAP_Taxonomy() )->register();
        ( new WRAP_Metabox() )->register();
        ( new WRAP_Shortcode() )->register();

        add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );
    }

    private function find_asset( $paths = array() ) {
        foreach ( $paths as $rel ) {
            $abs = trailingslashit( WRAP_PATH ) . $rel;
            if ( file_exists( $abs ) ) {
                return array( $rel, filemtime( $abs ) );
            }
        }

        return array( false, false );
    }

    public function register_frontend_assets() {
        list( $css_file, $css_ver ) = $this->find_asset( array(
            'assets/css/wrap-frontend.css',
            'assets/css/wrap-player.css',
        ) );

        list( $js_file, $js_ver ) = $this->find_asset( array(
            'frontend/wrap-frontend.js',
            'frontend/wrap-player.js',
        ) );

        if ( $css_file ) {
            wp_register_style( 'wrap-player', WRAP_URL . $css_file, array(), $css_ver ?: '3.1' );
        }

        if ( $js_file ) {
            wp_register_script( 'wrap-player', WRAP_URL . $js_file, array(), $js_ver ?: '3.1', true );
        }

        add_action(
            'wp',
            function () use ( $css_file, $js_file ) {
                if ( is_singular() ) {
                    global $post;

                    if ( $post && has_shortcode( $post->post_content, 'wrap_player' ) ) {
                        if ( $css_file ) {
                            wp_enqueue_style( 'wrap-player' );
                        }

                        if ( $js_file ) {
                            wp_enqueue_script( 'wrap-player' );

                            wp_add_inline_script(
                                'wrap-player',
                                sprintf(
                                    "console.log('WRAP: CSS → %s'); console.log('WRAP: JS → %s');",
                                    esc_js( $css_file ?: 'none' ),
                                    esc_js( $js_file ?: 'none' )
                                ),
                                'before'
                            );
                        }
                    }
                }
            }
        );
    }
}
