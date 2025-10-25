<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Shortcode {

    const STYLE_HANDLE  = 'wrap-player-style';
    const SCRIPT_HANDLE = 'wrap-player-script';

    public function register() {
        add_shortcode( 'wrap_player', array( $this, 'render_player' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    public function register_assets() {
        wp_register_style( self::STYLE_HANDLE, WRAP_URL . 'assets/css/wrap-frontend.css', array(), '1.0' );
        wp_register_script( self::SCRIPT_HANDLE, WRAP_URL . 'assets/js/wrap-frontend.js', array(), '1.0', true );
    }

    public function render_player( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'wrap_player' );

        $post_id = intval( $atts['id'] );
        if ( ! $post_id ) {
            return '';
        }

        $tracks = get_post_meta( $post_id, '_wrap_tracks', true );
        if ( empty( $tracks ) || ! is_array( $tracks ) ) {
            return '<p>' . esc_html__( 'No tracks found.', 'wrap' ) . '</p>';
        }

        $prepared_tracks = array();

        foreach ( $tracks as $index => $track ) {
            $title    = isset( $track['title'] ) ? trim( $track['title'] ) : '';
            $url      = isset( $track['url'] ) ? trim( $track['url'] ) : '';
            $duration = isset( $track['duration'] ) ? trim( $track['duration'] ) : '';

            if ( '' === $title || '' === $url ) {
                continue;
            }

            $prepared_tracks[] = array(
                'index'    => $index,
                'title'    => $title,
                'url'      => $url,
                'duration' => $duration,
            );
        }

        if ( empty( $prepared_tracks ) ) {
            return '<p>' . esc_html__( 'No tracks found.', 'wrap' ) . '</p>';
        }

        wp_enqueue_style( self::STYLE_HANDLE );
        wp_enqueue_script( self::SCRIPT_HANDLE );

        ob_start();
        ?>
        <div class="wrap-player-container" data-player-id="<?php echo esc_attr( $post_id ); ?>">
            <div class="wrap-player-header">
                <h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
            </div>

            <ul class="wrap-player-tracklist">
                <?php foreach ( $prepared_tracks as $track ) :
                    ?>
                    <li class="wrap-track-item" data-index="<?php echo esc_attr( $track['index'] ); ?>" data-url="<?php echo esc_url( $track['url'] ); ?>">
                        <span class="wrap-track-title"><?php echo esc_html( $track['title'] ); ?></span>
                        <?php if ( ! empty( $track['duration'] ) ) : ?>
                            <span class="wrap-track-duration"><?php echo esc_html( $track['duration'] ); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="wrap-controls">
                <button type="button" class="wrap-prev" aria-label="<?php esc_attr_e( 'Previous track', 'wrap' ); ?>">⏮</button>
                <button type="button" class="wrap-play" aria-label="<?php esc_attr_e( 'Play or pause', 'wrap' ); ?>">▶</button>
                <button type="button" class="wrap-next" aria-label="<?php esc_attr_e( 'Next track', 'wrap' ); ?>">⏭</button>
            </div>

            <audio id="wrap-audio-<?php echo esc_attr( $post_id ); ?>" preload="metadata"></audio>
        </div>
        <?php
        return ob_get_clean();
    }
}
