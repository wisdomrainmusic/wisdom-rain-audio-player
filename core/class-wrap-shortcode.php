<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Shortcode {

    public function register() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_shortcode( 'wrap_player', array( $this, 'render_player' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'wrap-player', WRAP_URL . 'assets/css/wrap-player.css', array(), '3.1' );
        wp_enqueue_script( 'wrap-player-js', WRAP_URL . 'frontend/wrap-player.js', array(), '3.1', true );
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

        ob_start();
        ?>
        <div class="wrap-player-container wrap-playlist-container"
             data-wrap-player="<?php echo esc_attr( $post_id ); ?>"
             data-player-id="<?php echo esc_attr( $post_id ); ?>">

            <div class="wrap-playlist-cover">
                <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                    <img src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'medium' ) ); ?>" alt="">
                <?php endif; ?>
                <div class="wrap-playlist-info">
                    <h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
                    <p><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php esc_html_e( 'E-Books, Audiobooks, Royalty-Free Music & Mindful Creations', 'wrap' ); ?></p>
                </div>
            </div>

            <div class="wrap-tracks wrap-player-tracklist" role="list">
                <?php foreach ( $prepared_tracks as $track ) : ?>
                    <div class="wrap-track wrap-track-item" role="listitem"
                         data-index="<?php echo esc_attr( $track['index'] ); ?>"
                         data-url="<?php echo esc_url( $track['url'] ); ?>"
                         data-src="<?php echo esc_url( $track['url'] ); ?>">
                        <div class="wrap-track-title">
                            <button type="button" class="wrap-inline-play" aria-label="<?php esc_attr_e( 'Play track', 'wrap' ); ?>">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="5,3 19,12 5,21" /></svg>
                            </button>
                            <span><?php echo esc_html( $track['title'] ); ?></span>
                        </div>
                        <?php if ( '' !== $track['duration'] ) : ?>
                            <div class="wrap-track-meta"><?php echo esc_html( $track['duration'] ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wrap-player-progress" aria-hidden="true"><span></span></div>

            <div class="wrap-player-controls wrap-controls" role="group" aria-label="<?php esc_attr_e( 'Playback controls', 'wrap' ); ?>">
                <button class="wrap-prev" type="button" aria-label="<?php esc_attr_e( 'Previous track', 'wrap' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="11,12 24,3 24,21" /><rect x="0" y="3" width="4" height="18" /></svg>
                </button>
                <button class="wrap-play" type="button" aria-label="<?php esc_attr_e( 'Play or pause', 'wrap' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="5,3 19,12 5,21" /></svg>
                </button>
                <button class="wrap-next" type="button" aria-label="<?php esc_attr_e( 'Next track', 'wrap' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="13,12 0,3 0,21" /><rect x="20" y="3" width="4" height="18" /></svg>
                </button>
            </div>

            <div class="wrap-player-time">00:00 / --:--</div>
            <audio preload="metadata" class="wrap-audio" style="display:none;"></audio>
        </div>
        <?php
        return ob_get_clean();
    }
}
