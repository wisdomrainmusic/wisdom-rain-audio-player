<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Shortcode {

    public function register() {
        add_shortcode( 'wrap_player', array( $this, 'render_player' ) );
    }

    private function enqueue_assets() {
        $style_enqueued = false;
        if ( wp_style_is( 'wrap-player', 'registered' ) ) {
            wp_enqueue_style( 'wrap-player' );
            $style_enqueued = true;
        }

        if ( ! $style_enqueued ) {
            $candidates = array(
                'assets/css/wrap-frontend.css',
                'assets/css/wrap-player.css',
            );

            foreach ( $candidates as $css_rel ) {
                $css_path = WRAP_PATH . $css_rel;
                if ( file_exists( $css_path ) ) {
                    wp_enqueue_style(
                        'wrap-player',
                        WRAP_URL . $css_rel,
                        array(),
                        filemtime( $css_path ) ?: '3.1'
                    );
                    break;
                }
            }
        }

        $script_enqueued = false;
        if ( wp_script_is( 'wrap-player', 'registered' ) ) {
            wp_enqueue_script( 'wrap-player' );
            $script_enqueued = true;
        }

        if ( ! $script_enqueued ) {
            $candidates = array(
                'frontend/wrap-frontend.js',
                'frontend/wrap-player.js',
            );

            foreach ( $candidates as $js_rel ) {
                $js_path = WRAP_PATH . $js_rel;
                if ( file_exists( $js_path ) ) {
                    wp_enqueue_script(
                        'wrap-player',
                        WRAP_URL . $js_rel,
                        array(),
                        filemtime( $js_path ) ?: '3.1',
                        true
                    );
                    break;
                }
            }
        }
    }

    public function render_player( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'wrap_player'
        );

        $post_id = absint( $atts['id'] );
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

            if ( '' === $url ) {
                continue;
            }

            if ( '' === $title ) {
                $title = sprintf(
                    /* translators: %d: track number */
                    esc_html__( 'Track %d', 'wrap' ),
                    $index + 1
                );
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

        $this->enqueue_assets();

        ob_start();
        ?>
        <div class="wrap-player-container" data-player-id="<?php echo esc_attr( $post_id ); ?>">

            <div class="wrap-player-header">
                <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                    <img src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'medium' ) ); ?>" alt="">
                <?php endif; ?>
                <h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
            </div>

            <ul class="wrap-player-tracklist">
                <?php foreach ( $prepared_tracks as $track ) : ?>
                    <li class="wrap-track-item"
                        data-url="<?php echo esc_url( $track['url'] ); ?>"
                        data-index="<?php echo esc_attr( $track['index'] ); ?>">
                        <span class="wrap-track-title"><?php echo esc_html( $track['title'] ); ?></span>
                        <?php if ( '' !== $track['duration'] ) : ?>
                            <span class="wrap-track-duration"><?php echo esc_html( $track['duration'] ); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="wrap-controls">
                <button class="wrap-prev" type="button" aria-label="<?php esc_attr_e( 'Previous track', 'wrap' ); ?>">⏮</button>
                <button class="wrap-play" type="button" aria-label="<?php esc_attr_e( 'Play or pause', 'wrap' ); ?>">▶</button>
                <button class="wrap-next" type="button" aria-label="<?php esc_attr_e( 'Next track', 'wrap' ); ?>">⏭</button>
            </div>

            <div class="wrap-progress"><div class="wrap-progress-bar"></div></div>
            <audio preload="metadata" class="wrap-audio"></audio>
        </div>
        <?php
        return ob_get_clean();
    }
}
