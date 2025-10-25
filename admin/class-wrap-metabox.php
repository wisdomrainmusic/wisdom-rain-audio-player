<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRAP_Metabox {

    public function register() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function enqueue_scripts( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        if ( ! function_exists( 'get_current_screen' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || 'wrap_player' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_script( 'wrap-admin-js', WRAP_URL . 'assets/js/wrap-admin.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'wrap-admin-css', WRAP_URL . 'assets/css/wrap-admin.css', array(), '1.0' );
    }

    public function add_metabox() {
        add_meta_box(
            'wrap_tracks',
            __( 'Tracks', 'wrap' ),
            array( $this, 'render_metabox' ),
            'wrap_player',
            'normal',
            'high'
        );
    }

    public function render_metabox( $post ) {
        wp_nonce_field( 'wrap_tracks_nonce_action', 'wrap_tracks_nonce' );

        $tracks = get_post_meta( $post->ID, '_wrap_tracks', true );
        if ( ! is_array( $tracks ) ) {
            $tracks = array();
        }

        if ( empty( $tracks ) ) {
            $tracks[] = array(
                'title'    => '',
                'url'      => '',
                'duration' => '',
            );
        }

        $placeholder_title    = __( 'Track Title', 'wrap' );
        $placeholder_url      = __( 'Bunny CDN URL', 'wrap' );
        $placeholder_duration = __( 'Duration (e.g. 03:24)', 'wrap' );
        $label_remove         = __( 'Remove', 'wrap' );
        ?>
        <div id="wrap-track-container">
            <p><?php esc_html_e( 'Add each track in the order you would like them to play.', 'wrap' ); ?></p>

            <div
                id="wrap-tracks-list"
                data-placeholder-title="<?php echo esc_attr( $placeholder_title ); ?>"
                data-placeholder-url="<?php echo esc_attr( $placeholder_url ); ?>"
                data-placeholder-duration="<?php echo esc_attr( $placeholder_duration ); ?>"
                data-label-remove="<?php echo esc_attr( $label_remove ); ?>"
            >
                <?php foreach ( $tracks as $index => $track ) :
                    $title    = isset( $track['title'] ) ? $track['title'] : '';
                    $url      = isset( $track['url'] ) ? $track['url'] : '';
                    $duration = isset( $track['duration'] ) ? $track['duration'] : '';
                    ?>
                    <div class="wrap-track-item">
                        <input type="text" name="wrap_tracks[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( $placeholder_title ); ?>" />
                        <input type="text" name="wrap_tracks[<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="<?php echo esc_attr( $placeholder_url ); ?>" />
                        <input type="text" name="wrap_tracks[<?php echo esc_attr( $index ); ?>][duration]" value="<?php echo esc_attr( $duration ); ?>" placeholder="<?php echo esc_attr( $placeholder_duration ); ?>" />
                        <button type="button" class="button wrap-remove-track"><?php echo esc_html( $label_remove ); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button button-primary" id="wrap-add-track"><?php esc_html_e( 'Add Track', 'wrap' ); ?></button>
        </div>
        <?php
    }

    public function save_metabox( $post_id ) {
        if ( 'wrap_player' !== get_post_type( $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['wrap_tracks_nonce'] ) || ! wp_verify_nonce( $_POST['wrap_tracks_nonce'], 'wrap_tracks_nonce_action' ) ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['wrap_tracks'] ) || ! is_array( $_POST['wrap_tracks'] ) ) {
            delete_post_meta( $post_id, '_wrap_tracks' );
            return;
        }

        $tracks = array();

        foreach ( $_POST['wrap_tracks'] as $t ) {
            $title    = isset( $t['title'] ) ? trim( wp_unslash( $t['title'] ) ) : '';
            $url      = isset( $t['url'] ) ? trim( wp_unslash( $t['url'] ) ) : '';
            $duration = isset( $t['duration'] ) ? trim( wp_unslash( $t['duration'] ) ) : '';

            if ( '' === $title || '' === $url ) {
                continue;
            }

            $tracks[] = array(
                'title'    => sanitize_text_field( $title ),
                'url'      => esc_url_raw( $url ),
                'duration' => sanitize_text_field( $duration ),
            );
        }

        if ( empty( $tracks ) ) {
            delete_post_meta( $post_id, '_wrap_tracks' );
            return;
        }

        update_post_meta( $post_id, '_wrap_tracks', $tracks );
    }
}
