<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Shortcode {

    public function register() {
        add_shortcode( 'wrap_player', array( $this, 'render_player' ) );
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

            <div class="wrap-player-header">
                <h3 class="wrap-player-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
            </div>

            <div class="wrap-tracks wrap-player-tracklist" role="list">
                <?php foreach ( $prepared_tracks as $track ) : ?>
                    <div class="wrap-track-item wrap-track" role="listitem"
                         data-index="<?php echo esc_attr( $track['index'] ); ?>"
                         data-url="<?php echo esc_url( $track['url'] ); ?>">
                        <span class="wrap-track-title">
                            <button type="button" class="wrap-inline-play" aria-label="<?php esc_attr_e( 'Play track', 'wrap' ); ?>"></button>
                            <span class="wrap-track-name"><?php echo esc_html( $track['title'] ); ?></span>
                        </span>
                        <?php if ( '' !== $track['duration'] ) : ?>
                            <span class="wrap-track-duration"><?php echo esc_html( $track['duration'] ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wrap-controls" role="group" aria-label="<?php esc_attr_e( 'Playback controls', 'wrap' ); ?>">
                <button class="wrap-prev" type="button" aria-label="<?php esc_attr_e( 'Previous track', 'wrap' ); ?>">⏮</button>
                <button class="wrap-play" type="button" aria-label="<?php esc_attr_e( 'Play or pause', 'wrap' ); ?>">▶</button>
                <button class="wrap-next" type="button" aria-label="<?php esc_attr_e( 'Next track', 'wrap' ); ?>">⏭</button>
            </div>

            <audio preload="metadata" class="wrap-audio" style="display:none;"></audio>
        </div>
        <?php
        return ob_get_clean();
    }
}
