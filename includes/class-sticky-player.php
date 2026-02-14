<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LFSP_Sticky_Player {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function register_ajax() {
        add_action( 'wp_ajax_lfsp_get_song_data', array( $this, 'ajax_get_song_data' ) );
        add_action( 'wp_ajax_nopriv_lfsp_get_song_data', array( $this, 'ajax_get_song_data' ) );
    }

    public function init_frontend() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_player' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'lfsp-sticky-player',
            LFSP_PLUGIN_URL . 'assets/css/sticky-player.css',
            array(),
            LFSP_VERSION
        );

        wp_add_inline_style( 'lfsp-sticky-player', $this->get_custom_css() );

        wp_enqueue_script(
            'lfsp-sticky-player',
            LFSP_PLUGIN_URL . 'assets/js/sticky-player.js',
            array(),
            LFSP_VERSION,
            true
        );

        $station          = sanitize_key( $this->settings['station_name'] );
        $custom_stream    = trim( $this->settings['custom_stream_url'] ?? '' );
        $inline_playback  = ! empty( $this->settings['inline_playback'] );
        $use_custom       = ! empty( $custom_stream );
        $allow_inline     = $use_custom || $inline_playback;

        $stream_url = $use_custom
            ? esc_url( $custom_stream )
            : esc_url( LFSP_Lautfm_API::get_stream_url( $station ) );

        $popup_url = 'https://laut.fm/' . rawurlencode( $station );

        wp_localize_script( 'lfsp-sticky-player', 'lfspConfig', array(
            'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
            'nonce'           => wp_create_nonce( 'lfsp_nonce' ),
            'streamUrl'       => $stream_url,
            'stationName'     => $station,
            'autoplay'        => ! empty( $this->settings['autoplay'] ) && $allow_inline,
            'defaultClosed'   => ! empty( $this->settings['default_closed'] ),
            'updateInterval'  => 30000,
            'inlinePlayback'  => $allow_inline,
            'popupUrl'        => esc_url( $popup_url ),
            'popupWidth'      => 500,
            'popupHeight'     => 600,
            'i18n'            => array(
                'play'        => esc_html__( 'Play', 'laut-fm-sticky-player' ),
                'pause'       => esc_html__( 'Pause', 'laut-fm-sticky-player' ),
                'loading'     => esc_html__( 'Loading...', 'laut-fm-sticky-player' ),
                'liveNow'     => esc_html__( 'Live', 'laut-fm-sticky-player' ),
                'toggleOpen'  => esc_html__( 'Open Player', 'laut-fm-sticky-player' ),
                'toggleClose' => esc_html__( 'Close Player', 'laut-fm-sticky-player' ),
                'openPopup'   => esc_html__( 'Open laut.fm', 'laut-fm-sticky-player' ),
            ),
        ) );
    }

    private function get_custom_css() {
        return sprintf(
            ':root {
                --lfsp-accent-1: %s;
                --lfsp-accent-2: %s;
                --lfsp-bg: %s;
                --lfsp-panel: %s;
                --lfsp-text: %s;
                --lfsp-height: %dpx;
            }',
            esc_attr( $this->settings['color_accent_1'] ?? '#ff003c' ),
            esc_attr( $this->settings['color_accent_2'] ?? '#00f0ff' ),
            esc_attr( $this->settings['color_bg'] ?? '#101010' ),
            esc_attr( $this->settings['color_bg'] ?? '#101010' ),
            esc_attr( $this->settings['color_text'] ?? '#ffffff' ),
            absint( $this->settings['player_height'] ?? 90 )
        );
    }

    public function render_player() {
        $station        = sanitize_key( $this->settings['station_name'] );
        $slogan         = sanitize_text_field( $this->settings['station_slogan'] ?? '' );
        $position       = sanitize_text_field( $this->settings['player_position'] ?? 'bottom' );
        $show_clock     = ! empty( $this->settings['show_clock'] );
        $show_toggle    = ! empty( $this->settings['show_toggle'] );
        $show_mobile    = ! empty( $this->settings['show_on_mobile'] );
        $show_soundnode = ! empty( $this->settings['show_soundnode'] );
        $stream_label   = sanitize_text_field( $this->settings['stream_link_label'] ?? 'STREAM' );
        $default_closed = ! empty( $this->settings['default_closed'] );

        $mobile_class  = $show_mobile ? '' : ' lfsp-hide-mobile';
        $wrapper_class = $default_closed ? ' lfsp-closed' : '';
        $aria_expanded = $default_closed ? 'false' : 'true';
        ?>

        <div id="lfsp-sticky-wrapper"
             class="lfsp-wrapper lfsp-position-<?php echo esc_attr( $position ); ?><?php echo esc_attr( $mobile_class . $wrapper_class ); ?>"
             role="region"
             aria-label="<?php esc_attr_e( 'Radio Player', 'laut-fm-sticky-player' ); ?>"
             data-station="<?php echo esc_attr( $station ); ?>">

            <?php if ( $show_toggle ) : ?>
            <button id="lfsp-toggle-btn"
                    class="lfsp-toggle-btn"
                    type="button"
                    aria-label="<?php esc_attr_e( 'Toggle Player', 'laut-fm-sticky-player' ); ?>"
                    aria-expanded="<?php echo esc_attr( $aria_expanded ); ?>">
                <span class="lfsp-toggle-icon" aria-hidden="true">&#9660;</span>
            </button>
            <?php endif; ?>

            <div class="lfsp-player-body">
                <div class="lfsp-inner-wrapper">

                    <div class="lfsp-left-area">
                        <button id="lfsp-play-btn"
                                class="lfsp-play-btn"
                                type="button"
                                aria-label="<?php esc_attr_e( 'Play', 'laut-fm-sticky-player' ); ?>">
                            <span class="lfsp-icon-play" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="20" height="20">
                                    <polygon points="7,3 21,12 7,21" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="lfsp-icon-pause" aria-hidden="true" style="display:none;">
                                <svg viewBox="0 0 24 24" width="20" height="20">
                                    <rect x="5" y="3" width="4" height="18" fill="currentColor"/>
                                    <rect x="15" y="3" width="4" height="18" fill="currentColor"/>
                                </svg>
                            </span>
                        </button>

                        <div class="lfsp-volume-control">
                            <button id="lfsp-mute-btn"
                                    class="lfsp-mute-btn"
                                    type="button"
                                    aria-label="<?php esc_attr_e( 'Mute', 'laut-fm-sticky-player' ); ?>">
                                <span class="lfsp-volume-icon" aria-hidden="true">&#128266;</span>
                            </button>
                            <input type="range"
                                   id="lfsp-volume-slider"
                                   class="lfsp-volume-slider"
                                   min="0" max="100" value="80"
                                   aria-label="<?php esc_attr_e( 'Volume', 'laut-fm-sticky-player' ); ?>">
                        </div>
                    </div>

                    <div class="lfsp-song-info">
                        <div id="lfsp-song-title" class="lfsp-title"><?php esc_html_e( 'Loading...', 'laut-fm-sticky-player' ); ?></div>
                        <?php if ( ! empty( $slogan ) ) : ?>
                        <div class="lfsp-subtitle"><?php echo esc_html( $slogan ); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="lfsp-meta">
                        <?php if ( $show_clock ) : ?>
                        <div id="lfsp-clock" class="lfsp-time">--:--</div>
                        <?php endif; ?>

                        <a href="<?php echo esc_url( 'https://laut.fm/' . rawurlencode( $station ) ); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="lfsp-link lfsp-stream-link">
                            <?php echo esc_html( $stream_label ); ?>
                        </a>

                        <?php if ( $show_soundnode ) : ?>
                        <a href="<?php echo esc_url( LFSP_Lautfm_API::get_soundnode_url( $station ) ); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="lfsp-link lfsp-soundnode-link"
                           title="<?php esc_attr_e( 'Discover more stations on soundnode.de', 'laut-fm-sticky-player' ); ?>">
                            soundnode.de
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_get_song_data() {
        check_ajax_referer( 'lfsp_nonce', 'nonce' );

        $station = isset( $_GET['station'] )
            ? sanitize_key( wp_unslash( $_GET['station'] ) )
            : '';

        if ( empty( $station ) ) {
            wp_send_json_error( array( 'message' => 'No station specified.' ) );
        }

        $song = LFSP_Lautfm_API::get_current_song( $station );

        if ( false === $song || empty( $song ) ) {
            $songs = LFSP_Lautfm_API::get_last_songs( $station );
            if ( false === $songs || empty( $songs ) ) {
                wp_send_json_error( array( 'message' => 'Could not fetch song data.' ) );
            }
            $song = $songs[0];
        }

        $type = sanitize_text_field( $song['type'] ?? '' );

        $result = array(
            'type'   => $type,
            'artist' => '',
            'title'  => sanitize_text_field( $song['title'] ?? '' ),
        );

        if ( 'song' === $type ) {
            $result['artist'] = sanitize_text_field( $song['artist']['name'] ?? '' );
        }

        wp_send_json_success( $result );
    }
}
