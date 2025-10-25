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
        <div class="wrap-player-wrapper">
            <div class="wrap-player-container" data-player-id="<?php echo esc_attr( $post_id ); ?>">

                <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                    <div class="wrap-player-cover">
                        <img
                            src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'medium' ) ); ?>"
                            alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
                        >
                    </div>
                <?php endif; ?>

                <div class="wrap-player-info">
                    <h4 class="wrap-player-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h4>
                    <div class="wrap-player-progress" role="presentation"><span></span></div>
                    <div class="wrap-player-time" aria-live="polite">--:-- / --:--</div>
                </div>

                <div class="wrap-player-controls">
                    <label class="screen-reader-text" for="wrap-language-selector-<?php echo esc_attr( $post_id ); ?>">
                        <?php esc_html_e( 'Select narration language', 'wrap' ); ?>
                    </label>
                    <select id="wrap-language-selector-<?php echo esc_attr( $post_id ); ?>" class="wrap-language-selector">
                        <option value="en"><?php echo esc_html_x( 'English', 'Language option', 'wrap' ); ?></option>
                        <option value="de"><?php echo esc_html_x( 'German', 'Language option', 'wrap' ); ?></option>
                        <option value="tr"><?php echo esc_html_x( 'Turkish', 'Language option', 'wrap' ); ?></option>
                    </select>

                    <div class="wrap-controls">
                        <button class="wrap-prev" type="button" aria-label="<?php esc_attr_e( 'Previous track', 'wrap' ); ?>">
                            ⏮
                        </button>
                        <button class="wrap-play" type="button" aria-label="<?php esc_attr_e( 'Play or pause', 'wrap' ); ?>">
                            ▶
                        </button>
                        <button class="wrap-next" type="button" aria-label="<?php esc_attr_e( 'Next track', 'wrap' ); ?>">
                            ⏭
                        </button>
                    </div>
                </div>

                <audio preload="metadata" class="wrap-audio"></audio>

                <ul class="wrap-track-list">
                    <?php foreach ( $prepared_tracks as $track ) : ?>
                        <li class="wrap-track-item"
                            data-url="<?php echo esc_url( $track['url'] ); ?>">
                            <span class="wrap-track-title"><?php echo esc_html( $track['title'] ); ?></span>
                            <?php if ( '' !== $track['duration'] ) : ?>
                                <span class="wrap-track-duration"><?php echo esc_html( $track['duration'] ); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
