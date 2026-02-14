<?php
/**
 * Admin Settings
 *
 * @package LFSP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LFSP_Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_laut-fm-sticky-player' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style(
            'lfsp-admin',
            LFSP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LFSP_VERSION
        );
        wp_enqueue_script(
            'lfsp-admin',
            LFSP_PLUGIN_URL . 'assets/js/admin.js',
            array( 'wp-color-picker', 'jquery' ),
            LFSP_VERSION,
            true
        );
    }

    public function add_menu() {
        add_options_page(
            __( 'Laut.fm Sticky Player', 'laut-fm-sticky-player' ),
            __( 'Laut.fm Player', 'laut-fm-sticky-player' ),
            'manage_options',
            'laut-fm-sticky-player',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'lfsp_options_group',
            'lfsp_settings',
            array( $this, 'sanitize_settings' )
        );

        // === SECTION: Station ===
        add_settings_section(
            'lfsp_section_station',
            __( '📻 Station Settings', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . esc_html__( 'Configure which laut.fm station to stream.', 'laut-fm-sticky-player' ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'station_name', __( 'Station Name', 'laut-fm-sticky-player' ), 'field_station_name', 'lfsp_section_station' );
        $this->add_field( 'station_slogan', __( 'Slogan / Subtitle', 'laut-fm-sticky-player' ), 'field_station_slogan', 'lfsp_section_station' );
        $this->add_field( 'autoplay', __( 'Autoplay', 'laut-fm-sticky-player' ), 'field_autoplay', 'lfsp_section_station' );

        // === SECTION: Design ===
        add_settings_section(
            'lfsp_section_design',
            __( '🎨 Design Settings', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . esc_html__( 'Customize the player appearance.', 'laut-fm-sticky-player' ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'player_position', __( 'Position', 'laut-fm-sticky-player' ), 'field_position', 'lfsp_section_design' );
        $this->add_field( 'player_height', __( 'Player Height (px)', 'laut-fm-sticky-player' ), 'field_height', 'lfsp_section_design' );
        $this->add_field( 'color_accent_1', __( 'Accent Color 1 (Left/Red)', 'laut-fm-sticky-player' ), 'field_color_accent_1', 'lfsp_section_design' );
        $this->add_field( 'color_accent_2', __( 'Accent Color 2 (Right/Blue)', 'laut-fm-sticky-player' ), 'field_color_accent_2', 'lfsp_section_design' );
        $this->add_field( 'color_bg', __( 'Background Color', 'laut-fm-sticky-player' ), 'field_color_bg', 'lfsp_section_design' );
        $this->add_field( 'color_text', __( 'Text Color', 'laut-fm-sticky-player' ), 'field_color_text', 'lfsp_section_design' );

        // === SECTION: Funktionen ===
        add_settings_section(
            'lfsp_section_features',
            __( '⚙️ Features', 'laut-fm-sticky-player' ),
            null,
            'laut-fm-sticky-player'
        );

        $this->add_field( 'show_clock', __( 'Show Clock', 'laut-fm-sticky-player' ), 'field_show_clock', 'lfsp_section_features' );
        $this->add_field( 'show_toggle', __( 'Show Toggle Button', 'laut-fm-sticky-player' ), 'field_show_toggle', 'lfsp_section_features' );
        $this->add_field( 'show_on_mobile', __( 'Show on Mobile', 'laut-fm-sticky-player' ), 'field_show_mobile', 'lfsp_section_features' );
        $this->add_field( 'default_closed', __( 'Start Closed', 'laut-fm-sticky-player' ), 'field_default_closed', 'lfsp_section_features' );
        $this->add_field( 'stream_link_label', __( 'Stream Link Label', 'laut-fm-sticky-player' ), 'field_stream_label', 'lfsp_section_features' );

        // === SECTION: soundnode.de ===
        add_settings_section(
            'lfsp_section_soundnode',
            __( '🌐 soundnode.de Integration', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . sprintf(
                    /* translators: %s: Link to soundnode.de */
                    esc_html__( 'Show a link to %s – a radio aggregator listing all laut.fm stations.', 'laut-fm-sticky-player' ),
                    '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
                ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'show_soundnode', __( 'Show soundnode.de Link', 'laut-fm-sticky-player' ), 'field_show_soundnode', 'lfsp_section_soundnode' );
    }

    /**
     * Helper: Feld registrieren
     */
    private function add_field( $id, $title, $callback, $section ) {
        add_settings_field( $id, $title, array( $this, $callback ), 'laut-fm-sticky-player', $section );
    }

    /**
     * Sanitize alle Settings
     */
    public function sanitize_settings( $input ) {
        $s = array();

        $s['station_name']      = sanitize_key( $input['station_name'] ?? '' );
        $s['station_slogan']    = sanitize_text_field( $input['station_slogan'] ?? '' );
        $s['autoplay']          = ! empty( $input['autoplay'] );
        $s['player_position']   = in_array( $input['player_position'] ?? '', array( 'top', 'bottom' ), true )
            ? $input['player_position'] : 'bottom';
        $s['player_height']     = absint( $input['player_height'] ?? 90 );
        $s['player_height']     = max( 50, min( 150, $s['player_height'] ) ); // Clamp 50-150
        $s['color_accent_1']    = sanitize_hex_color( $input['color_accent_1'] ?? '#ff003c' ) ?: '#ff003c';
        $s['color_accent_2']    = sanitize_hex_color( $input['color_accent_2'] ?? '#00f0ff' ) ?: '#00f0ff';
        $s['color_bg']          = sanitize_hex_color( $input['color_bg'] ?? '#101010' ) ?: '#101010';
        $s['color_text']        = sanitize_hex_color( $input['color_text'] ?? '#ffffff' ) ?: '#ffffff';
        $s['show_clock']        = ! empty( $input['show_clock'] );
        $s['show_toggle']       = ! empty( $input['show_toggle'] );
        $s['show_on_mobile']    = ! empty( $input['show_on_mobile'] );
        $s['show_soundnode']    = ! empty( $input['show_soundnode'] );
        $s['default_closed']    = ! empty( $input['default_closed'] );
        $s['stream_link_label'] = sanitize_text_field( $input['stream_link_label'] ?? 'STREAM' );

        // Station validieren
        if ( ! empty( $s['station_name'] ) && ! LFSP_Lautfm_API::station_exists( $s['station_name'] ) ) {
            add_settings_error(
                'lfsp_settings',
                'lfsp_invalid_station',
                sprintf(
                    /* translators: %s: Station name */
                    __( 'Station "%s" was not found on laut.fm. Please check the name.', 'laut-fm-sticky-player' ),
                    esc_html( $s['station_name'] )
                ),
                'error'
            );
        }

        // Transients löschen bei Stationsänderung
        $old = get_option( 'lfsp_settings', array() );
        if ( ( $old['station_name'] ?? '' ) !== $s['station_name'] ) {
            delete_transient( 'lfsp_station_' . md5( $old['station_name'] ?? '' ) );
            delete_transient( 'lfsp_lastsongs_' . md5( $old['station_name'] ?? '' ) );
            delete_transient( 'lfsp_song_' . md5( $old['station_name'] ?? '' ) );
        }

        return $s;
    }

    // =============================================
    // FIELD CALLBACKS
    // =============================================

    public function field_station_name() {
        $v = $this->get_setting( 'station_name' );
        echo '<input type="text" name="lfsp_settings[station_name]" value="' . esc_attr( $v ) . '" class="regular-text" placeholder="frankfurt-beats">';
        echo '<p class="description">' . esc_html__( 'The station name from laut.fm URL. Example: "frankfurt-beats" for laut.fm/frankfurt-beats', 'laut-fm-sticky-player' ) . '</p>';

        // Live-Vorschau wenn Station existiert
        if ( ! empty( $v ) ) {
            $info = LFSP_Lautfm_API::get_station_info( $v );
            if ( $info ) {
                echo '<p style="color: #46b450; margin-top: 5px;">✅ <strong>' . esc_html( $info['display_name'] ?? $info['name'] ?? $v ) . '</strong>';
                if ( ! empty( $info['description'] ) ) {
                    echo ' – ' . esc_html( wp_trim_words( $info['description'], 15 ) );
                }
                echo '</p>';
            }
        }
    }

    public function field_station_slogan() {
        $v = $this->get_setting( 'station_slogan' );
        echo '<input type="text" name="lfsp_settings[station_slogan]" value="' . esc_attr( $v ) . '" class="regular-text" placeholder="' . esc_attr__( 'Your station slogan', 'laut-fm-sticky-player' ) . '">';
        echo '<p class="description">' . esc_html__( 'Displayed below the song title with a gradient effect. Leave empty to hide.', 'laut-fm-sticky-player' ) . '</p>';
    }

    public function field_autoplay() {
        $this->render_checkbox( 'autoplay', __( 'Auto-play on page load (may be blocked by browsers)', 'laut-fm-sticky-player' ) );
    }

    public function field_position() {
        $v = $this->get_setting( 'player_position', 'bottom' );
        echo '<select name="lfsp_settings[player_position]">';
        echo '<option value="bottom" ' . selected( $v, 'bottom', false ) . '>' . esc_html__( 'Bottom (recommended)', 'laut-fm-sticky-player' ) . '</option>';
        echo '<option value="top" ' . selected( $v, 'top', false ) . '>' . esc_html__( 'Top', 'laut-fm-sticky-player' ) . '</option>';
        echo '</select>';
    }

    public function field_height() {
        $v = $this->get_setting( 'player_height', 90 );
        echo '<input type="number" name="lfsp_settings[player_height]" value="' . esc_attr( $v ) . '" min="50" max="150" step="5" style="width:80px;"> px';
    }

    public function field_color_accent_1() {
        $this->render_color_picker( 'color_accent_1', '#ff003c' );
    }

    public function field_color_accent_2() {
        $this->render_color_picker( 'color_accent_2', '#00f0ff' );
    }

    public function field_color_bg() {
        $this->render_color_picker( 'color_bg', '#101010' );
    }

    public function field_color_text() {
        $this->render_color_picker( 'color_text', '#ffffff' );
    }

    public function field_show_clock() {
        $this->render_checkbox( 'show_clock', __( 'Display a live clock in the player', 'laut-fm-sticky-player' ) );
    }

    public function field_show_toggle() {
        $this->render_checkbox( 'show_toggle', __( 'Allow users to collapse/expand the player', 'laut-fm-sticky-player' ) );
    }

    public function field_show_mobile() {
        $this->render_checkbox( 'show_on_mobile', __( 'Show the player on mobile devices', 'laut-fm-sticky-player' ) );
    }

    public function field_default_closed() {
        $this->render_checkbox( 'default_closed', __( 'Player starts collapsed (user can still open it)', 'laut-fm-sticky-player' ) );
    }

    public function field_stream_label() {
        $v = $this->get_setting( 'stream_link_label', 'STREAM' );
        echo '<input type="text" name="lfsp_settings[stream_link_label]" value="' . esc_attr( $v ) . '" class="small-text" placeholder="STREAM">';
    }

    public function field_show_soundnode() {
        $this->render_checkbox( 'show_soundnode', __( 'Show a small "soundnode.de" link in the player', 'laut-fm-sticky-player' ) );
        echo '<p class="description">' . sprintf(
            /* translators: %s: Link to soundnode.de */
            esc_html__( '%s lists all laut.fm radio stations as an aggregator.', 'laut-fm-sticky-player' ),
            '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
        ) . '</p>';
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    private function get_setting( $key, $default = '' ) {
        $settings = get_option( 'lfsp_settings', array() );
        return $settings[ $key ] ?? $default;
    }

    private function render_checkbox( $key, $label ) {
        $checked = ! empty( $this->get_setting( $key ) ) ? 'checked' : '';
        echo '<label>';
        echo '<input type="checkbox" name="lfsp_settings[' . esc_attr( $key ) . ']" value="1" ' . $checked . '> ';
        echo esc_html( $label );
        echo '</label>';
    }

    private function render_color_picker( $key, $default ) {
        $v = $this->get_setting( $key, $default );
        echo '<input type="text" name="lfsp_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $v ) . '" class="lfsp-color-picker" data-default-color="' . esc_attr( $default ) . '">';
    }

    /**
     * Settings-Seite rendern
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>
                <?php echo esc_html( get_admin_page_title() ); ?>
                <span style="font-size: 14px; color: #666; margin-left: 10px;">v<?php echo esc_html( LFSP_VERSION ); ?></span>
            </h1>

            <?php settings_errors(); ?>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'lfsp_options_group' );
                do_settings_sections( 'laut-fm-sticky-player' );
                submit_button( __( 'Save Settings', 'laut-fm-sticky-player' ) );
                ?>
            </form>

            <hr>
            <p style="color: #666; font-size: 12px;">
                <?php
                printf(
                    /* translators: 1: Plugin name, 2: soundnode.de link */
                    esc_html__( '%1$s | Discover all laut.fm stations on %2$s', 'laut-fm-sticky-player' ),
                    '<strong>Laut.fm Sticky Player</strong>',
                    '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
