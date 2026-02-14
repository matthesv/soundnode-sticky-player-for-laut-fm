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
            array( 'jquery', 'wp-color-picker' ),
            LFSP_VERSION,
            true
        );
    }

    public function add_menu() {
        add_options_page(
            __( 'Laut.fm Sticky Player', 'laut-fm-sticky-player' ),
            __( 'Laut.fm Sticky Player', 'laut-fm-sticky-player' ),
            'manage_options',
            'laut-fm-sticky-player',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting( 'lfsp_options_group', 'lfsp_settings', array( $this, 'sanitize_settings' ) );

        add_settings_section(
            'lfsp_section_station',
            __( 'Station', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . esc_html__( 'Configure the laut.fm station and optional custom stream URL.', 'laut-fm-sticky-player' ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'station_name', __( 'Station Name', 'laut-fm-sticky-player' ), 'field_station_name', 'lfsp_section_station' );
        $this->add_field( 'station_slogan', __( 'Station Slogan', 'laut-fm-sticky-player' ), 'field_station_slogan', 'lfsp_section_station' );
        $this->add_field( 'custom_stream_url', __( 'Custom Stream URL (Optional)', 'laut-fm-sticky-player' ), 'field_custom_stream_url', 'lfsp_section_station' );

        add_settings_section(
            'lfsp_section_design',
            __( 'Design', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . esc_html__( 'Customize colors and layout.', 'laut-fm-sticky-player' ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'player_position', __( 'Player Position', 'laut-fm-sticky-player' ), 'field_player_position', 'lfsp_section_design' );
        $this->add_field( 'player_height', __( 'Player Height (px)', 'laut-fm-sticky-player' ), 'field_player_height', 'lfsp_section_design' );
        $this->add_field( 'color_accent_1', __( 'Accent Color 1', 'laut-fm-sticky-player' ), 'field_color_accent_1', 'lfsp_section_design' );
        $this->add_field( 'color_accent_2', __( 'Accent Color 2', 'laut-fm-sticky-player' ), 'field_color_accent_2', 'lfsp_section_design' );
        $this->add_field( 'color_bg', __( 'Background Color', 'laut-fm-sticky-player' ), 'field_color_bg', 'lfsp_section_design' );
        $this->add_field( 'color_text', __( 'Text Color', 'laut-fm-sticky-player' ), 'field_color_text', 'lfsp_section_design' );

        add_settings_section(
            'lfsp_section_features',
            __( 'Features', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . esc_html__( 'Toggle player features.', 'laut-fm-sticky-player' ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'autoplay', __( 'Autoplay', 'laut-fm-sticky-player' ), 'field_autoplay', 'lfsp_section_features' );
        $this->add_field( 'show_clock', __( 'Show Clock', 'laut-fm-sticky-player' ), 'field_show_clock', 'lfsp_section_features' );
        $this->add_field( 'show_toggle', __( 'Show Toggle Button', 'laut-fm-sticky-player' ), 'field_show_toggle', 'lfsp_section_features' );
        $this->add_field( 'show_on_mobile', __( 'Show on Mobile', 'laut-fm-sticky-player' ), 'field_show_mobile', 'lfsp_section_features' );
        $this->add_field( 'default_closed', __( 'Start Closed', 'laut-fm-sticky-player' ), 'field_default_closed', 'lfsp_section_features' );
        $this->add_field( 'stream_link_label', __( 'Stream Link Label', 'laut-fm-sticky-player' ), 'field_stream_label', 'lfsp_section_features' );

        add_settings_section(
            'lfsp_section_soundnode',
            __( '🅢 soundnode.de Integration', 'laut-fm-sticky-player' ),
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

        add_settings_section(
            'lfsp_section_playback',
            __( 'Playback', 'laut-fm-sticky-player' ),
            function () {
                echo '<p>' . esc_html__( 'Choose how playback should work (inline, popup, etc.).', 'laut-fm-sticky-player' ) . '</p>';
            },
            'laut-fm-sticky-player'
        );

        $this->add_field( 'playback_mode', __( 'Playback Mode', 'laut-fm-sticky-player' ), 'field_playback_mode', 'lfsp_section_playback' );
    }

    private function add_field( $key, $label, $callback, $section ) {
        add_settings_field(
            $key,
            $label,
            array( $this, $callback ),
            'laut-fm-sticky-player',
            $section
        );
    }

    public function sanitize_settings( $input ) {
        $s = array();

        $s['station_name']      = sanitize_key( $input['station_name'] ?? '' );
        $s['station_slogan']    = sanitize_text_field( $input['station_slogan'] ?? '' );
        $s['player_position']   = in_array( ( $input['player_position'] ?? 'bottom' ), array( 'bottom', 'top' ), true ) ? $input['player_position'] : 'bottom';
        $s['color_accent_1']    = sanitize_hex_color( $input['color_accent_1'] ?? '#ff003c' ) ?: '#ff003c';
        $s['color_accent_2']    = sanitize_hex_color( $input['color_accent_2'] ?? '#00f0ff' ) ?: '#00f0ff';
        $s['color_bg']          = sanitize_hex_color( $input['color_bg'] ?? '#101010' ) ?: '#101010';
        $s['color_text']        = sanitize_hex_color( $input['color_text'] ?? '#ffffff' ) ?: '#ffffff';
        $s['player_height']     = absint( $input['player_height'] ?? 90 );
        $s['autoplay']          = ! empty( $input['autoplay'] );
        $s['show_clock']        = ! empty( $input['show_clock'] );
        $s['show_toggle']       = ! empty( $input['show_toggle'] );
        $s['show_on_mobile']    = ! empty( $input['show_on_mobile'] );
        $s['show_soundnode']    = ! empty( $input['show_soundnode'] );
        $s['default_closed']    = ! empty( $input['default_closed'] );
        $s['stream_link_label'] = sanitize_text_field( $input['stream_link_label'] ?? 'STREAM' );

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

        $old = get_option( 'lfsp_settings', array() );
        if ( ( $old['station_name'] ?? '' ) !== $s['station_name'] ) {
            delete_transient( 'lfsp_station_' . md5( $old['station_name'] ?? '' ) );
            delete_transient( 'lfsp_lastsongs_' . md5( $old['station_name'] ?? '' ) );
            delete_transient( 'lfsp_song_' . md5( $old['station_name'] ?? '' ) );
        }

        $s['playback_mode']     = sanitize_key( $input['playback_mode'] ?? 'popup_website' );
        $s['custom_stream_url'] = esc_url_raw( $input['custom_stream_url'] ?? '' );

        return $s;
    }

    private function get_setting( $key, $default = '' ) {
        $settings = get_option( 'lfsp_settings', array() );
        return $settings[ $key ] ?? $default;
    }

    public function field_station_name() {
        $v = $this->get_setting( 'station_name', '' );
        echo '<input type="text" name="lfsp_settings[station_name]" value="' . esc_attr( $v ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Example: "youfm" (laut.fm station slug)', 'laut-fm-sticky-player' ) . '</p>';
    }

    public function field_station_slogan() {
        $v = $this->get_setting( 'station_slogan', '' );
        echo '<input type="text" name="lfsp_settings[station_slogan]" value="' . esc_attr( $v ) . '" class="regular-text" />';
    }

    public function field_custom_stream_url() {
        $v = $this->get_setting( 'custom_stream_url', '' );
        echo '<input type="url" name="lfsp_settings[custom_stream_url]" value="' . esc_attr( $v ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'If set, this URL will be used instead of the default laut.fm stream URL.', 'laut-fm-sticky-player' ) . '</p>';
    }

    public function field_player_position() {
        $v = $this->get_setting( 'player_position', 'bottom' );
        echo '<select name="lfsp_settings[player_position]">';
        echo '<option value="bottom"' . selected( $v, 'bottom', false ) . '>' . esc_html__( 'Bottom', 'laut-fm-sticky-player' ) . '</option>';
        echo '<option value="top"' . selected( $v, 'top', false ) . '>' . esc_html__( 'Top', 'laut-fm-sticky-player' ) . '</option>';
        echo '</select>';
    }

    public function field_player_height() {
        $v = (int) $this->get_setting( 'player_height', 90 );
        echo '<input type="number" min="60" max="200" step="1" name="lfsp_settings[player_height]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_accent_1() {
        $v = $this->get_setting( 'color_accent_1', '#ff003c' );
        echo '<input type="text" class="lfsp-color" name="lfsp_settings[color_accent_1]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_accent_2() {
        $v = $this->get_setting( 'color_accent_2', '#00f0ff' );
        echo '<input type="text" class="lfsp-color" name="lfsp_settings[color_accent_2]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_bg() {
        $v = $this->get_setting( 'color_bg', '#101010' );
        echo '<input type="text" class="lfsp-color" name="lfsp_settings[color_bg]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_text() {
        $v = $this->get_setting( 'color_text', '#ffffff' );
        echo '<input type="text" class="lfsp-color" name="lfsp_settings[color_text]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_autoplay() {
        $this->render_checkbox( 'autoplay', __( 'Start playing automatically (may be blocked by browsers)', 'laut-fm-sticky-player' ) );
    }

    public function field_show_clock() {
        $this->render_checkbox( 'show_clock', __( 'Show a live clock in the player', 'laut-fm-sticky-player' ) );
    }

    public function field_show_toggle() {
        $this->render_checkbox( 'show_toggle', __( 'Show a toggle button to collapse/expand the player', 'laut-fm-sticky-player' ) );
    }

    public function field_show_mobile() {
        $this->render_checkbox( 'show_on_mobile', __( 'Display the player on mobile devices', 'laut-fm-sticky-player' ) );
    }

    public function field_default_closed() {
        $this->render_checkbox( 'default_closed', __( 'Player starts collapsed (user can still open it)', 'laut-fm-sticky-player' ) );
    }

    public function field_stream_label() {
        $v = $this->get_setting( 'stream_link_label', 'STREAM' );
        echo '<input type="text" name="lfsp_settings[stream_link_label]" value="' . esc_attr( $v ) . '" class="regular-text" />';
    }

    public function field_show_soundnode() {
        $this->render_checkbox( 'show_soundnode', __( 'Show a small "soundnode.de" link in the player', 'laut-fm-sticky-player' ) );
        echo '<p class="description">' . sprintf(
            /* translators: %s: Link to soundnode.de */
            esc_html__( '%s lists all laut.fm radio stations as an aggregator.', 'laut-fm-sticky-player' ),
            '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
        ) . '</p>';
    }

    public function field_playback_mode() {
        $v = $this->get_setting( 'playback_mode', 'popup_website' );
        echo '<select name="lfsp_settings[playback_mode]">';
        echo '<option value="popup_website"' . selected( $v, 'popup_website', false ) . '>' . esc_html__( 'Popup: Open station website', 'laut-fm-sticky-player' ) . '</option>';
        echo '<option value="inline"' . selected( $v, 'inline', false ) . '>' . esc_html__( 'Inline: Play in sticky player', 'laut-fm-sticky-player' ) . '</option>';
        echo '<option value="custom_url"' . selected( $v, 'custom_url', false ) . '>' . esc_html__( 'Custom URL: Use custom stream URL', 'laut-fm-sticky-player' ) . '</option>';
        echo '</select>';
    }

    private function render_checkbox( $key, $label ) {
        echo '<label>';
        echo '<input type="checkbox" name="lfsp_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( ! empty( $this->get_setting( $key ) ), true, false ) . '> ';
        echo esc_html( $label );
        echo '</label>';
    }

    public function render_page() {
        ?>
        <div class="wrap lfsp-wrap">
            <h1><?php echo esc_html__( 'Laut.fm Sticky Player Settings', 'laut-fm-sticky-player' ); ?></h1>

            <form method="post" action="options.php">
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
                    /* translators: 1: Plugin name, 2: Link to soundnode.de */
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
