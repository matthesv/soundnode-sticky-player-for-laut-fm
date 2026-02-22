<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LFSP_Admin_Settings {

    private const PAGE_SLUG       = 'soundnode-sticky-player';
    private const TEXT_DOMAIN     = 'soundnode-sticky-player-for-laut-fm';
    private const OPTION_GROUP    = 'lfsp_options_group';
    private const OPTION_NAME     = 'lfsp_settings';

    private const ALLOWED_POSITIONS = array( 'bottom', 'top', 'left', 'right' );
    private const ALLOWED_PLAYBACK  = array( 'popup_website', 'popup_stream', 'inline' );

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
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
            __( 'SoundNode Sticky Player', self::TEXT_DOMAIN ),
            __( 'SoundNode Sticky Player', self::TEXT_DOMAIN ),
            'manage_options',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting( self::OPTION_GROUP, self::OPTION_NAME, array( $this, 'sanitize_settings' ) );

        $this->register_section_station();
        $this->register_section_design();
        $this->register_section_features();
        $this->register_section_soundnode();
        $this->register_section_playback();
    }

    private function register_section_station() {
        add_settings_section(
            'lfsp_section_station',
            __( 'Station', self::TEXT_DOMAIN ),
            function () {
                echo '<p>' . esc_html__( 'Configure the laut.fm station and optional custom stream URL.', self::TEXT_DOMAIN ) . '</p>';
            },
            self::PAGE_SLUG
        );

        $this->add_field( 'station_name', __( 'Station Name', self::TEXT_DOMAIN ), 'field_station_name', 'lfsp_section_station' );
        $this->add_field( 'station_slogan', __( 'Station Slogan', self::TEXT_DOMAIN ), 'field_station_slogan', 'lfsp_section_station' );
        $this->add_field( 'custom_stream_url', __( 'Custom Stream URL (Optional)', self::TEXT_DOMAIN ), 'field_custom_stream_url', 'lfsp_section_station' );
    }

    private function register_section_design() {
        add_settings_section(
            'lfsp_section_design',
            __( 'Design', self::TEXT_DOMAIN ),
            function () {
                echo '<p>' . esc_html__( 'Customize colors and layout.', self::TEXT_DOMAIN ) . '</p>';
            },
            self::PAGE_SLUG
        );

        $this->add_field( 'player_position', __( 'Player Position', self::TEXT_DOMAIN ), 'field_player_position', 'lfsp_section_design' );
        $this->add_field( 'player_height', __( 'Player Height (px)', self::TEXT_DOMAIN ), 'field_player_height', 'lfsp_section_design' );
        $this->add_field( 'color_accent_1', __( 'Accent Color 1', self::TEXT_DOMAIN ), 'field_color_accent_1', 'lfsp_section_design' );
        $this->add_field( 'color_accent_2', __( 'Accent Color 2', self::TEXT_DOMAIN ), 'field_color_accent_2', 'lfsp_section_design' );
        $this->add_field( 'color_bg', __( 'Background Color', self::TEXT_DOMAIN ), 'field_color_bg', 'lfsp_section_design' );
        $this->add_field( 'color_text', __( 'Text Color', self::TEXT_DOMAIN ), 'field_color_text', 'lfsp_section_design' );
    }

    private function register_section_features() {
        add_settings_section(
            'lfsp_section_features',
            __( 'Features', self::TEXT_DOMAIN ),
            function () {
                echo '<p>' . esc_html__( 'Toggle player features.', self::TEXT_DOMAIN ) . '</p>';
            },
            self::PAGE_SLUG
        );

        $this->add_field( 'autoplay', __( 'Autoplay', self::TEXT_DOMAIN ), 'field_autoplay', 'lfsp_section_features' );
        $this->add_field( 'show_clock', __( 'Show Clock', self::TEXT_DOMAIN ), 'field_show_clock', 'lfsp_section_features' );
        $this->add_field( 'show_toggle', __( 'Show Toggle Button', self::TEXT_DOMAIN ), 'field_show_toggle', 'lfsp_section_features' );
        $this->add_field( 'show_on_mobile', __( 'Show on Mobile', self::TEXT_DOMAIN ), 'field_show_mobile', 'lfsp_section_features' );
        $this->add_field( 'default_closed', __( 'Start Closed', self::TEXT_DOMAIN ), 'field_default_closed', 'lfsp_section_features' );
        $this->add_field( 'stream_link_label', __( 'Stream Link Label', self::TEXT_DOMAIN ), 'field_stream_label', 'lfsp_section_features' );
    }

    private function register_section_soundnode() {
        add_settings_section(
            'lfsp_section_soundnode',
            __( '🅢 soundnode.de Integration', self::TEXT_DOMAIN ),
            function () {
                echo '<p>' . sprintf(
                    esc_html__( 'Show a link to %s — a radio aggregator listing all laut.fm stations.', self::TEXT_DOMAIN ),
                    '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
                ) . '</p>';
            },
            self::PAGE_SLUG
        );

        $this->add_field( 'show_soundnode', __( 'Show soundnode.de Link', self::TEXT_DOMAIN ), 'field_show_soundnode', 'lfsp_section_soundnode' );
    }

    private function register_section_playback() {
        add_settings_section(
            'lfsp_section_playback',
            __( 'Playback', self::TEXT_DOMAIN ),
            function () {
                echo '<p>' . esc_html__( 'Choose how playback should work (inline, popup, etc.).', self::TEXT_DOMAIN ) . '</p>';
            },
            self::PAGE_SLUG
        );

        $this->add_field( 'playback_mode', __( 'Playback Mode', self::TEXT_DOMAIN ), 'field_playback_mode', 'lfsp_section_playback' );
    }

    private function add_field( $key, $label, $callback, $section ) {
        add_settings_field(
            $key,
            $label,
            array( $this, $callback ),
            self::PAGE_SLUG,
            $section
        );
    }

    public function sanitize_settings( $input ) {
        $s = array();

        $s['station_name']      = sanitize_key( $input['station_name'] ?? '' );
        $s['station_slogan']    = sanitize_text_field( $input['station_slogan'] ?? '' );
        $s['custom_stream_url'] = esc_url_raw( $input['custom_stream_url'] ?? '' );

        $position = $input['player_position'] ?? 'bottom';
        $s['player_position'] = in_array( $position, self::ALLOWED_POSITIONS, true ) ? $position : 'bottom';

        $s['color_accent_1'] = sanitize_hex_color( $input['color_accent_1'] ?? '#ff003c' ) ?: '#ff003c';
        $s['color_accent_2'] = sanitize_hex_color( $input['color_accent_2'] ?? '#00f0ff' ) ?: '#00f0ff';
        $s['color_bg']       = sanitize_hex_color( $input['color_bg'] ?? '#101010' ) ?: '#101010';
        $s['color_text']     = sanitize_hex_color( $input['color_text'] ?? '#ffffff' ) ?: '#ffffff';
        $s['player_height']  = absint( $input['player_height'] ?? 90 );

        $s['autoplay']          = ! empty( $input['autoplay'] );
        $s['show_clock']        = ! empty( $input['show_clock'] );
        $s['show_toggle']       = ! empty( $input['show_toggle'] );
        $s['show_on_mobile']    = ! empty( $input['show_on_mobile'] );
        $s['show_soundnode']    = ! empty( $input['show_soundnode'] );
        $s['default_closed']    = ! empty( $input['default_closed'] );
        $s['stream_link_label'] = sanitize_text_field( $input['stream_link_label'] ?? 'STREAM' );

        $mode = sanitize_key( $input['playback_mode'] ?? 'popup_website' );
        $s['playback_mode'] = in_array( $mode, self::ALLOWED_PLAYBACK, true ) ? $mode : 'popup_website';

        if ( ! empty( $s['station_name'] ) && ! LFSP_Lautfm_API::station_exists( $s['station_name'] ) ) {
            add_settings_error(
                self::OPTION_NAME,
                'lfsp_invalid_station',
                sprintf(
                    __( 'Station "%s" was not found on laut.fm. Please check the name.', self::TEXT_DOMAIN ),
                    esc_html( $s['station_name'] )
                ),
                'error'
            );
        }

        $old = get_option( self::OPTION_NAME, array() );
        if ( ( $old['station_name'] ?? '' ) !== $s['station_name'] ) {
            $old_hash = md5( $old['station_name'] ?? '' );
            delete_transient( 'lfsp_station_' . $old_hash );
            delete_transient( 'lfsp_lastsongs_' . $old_hash );
            delete_transient( 'lfsp_song_' . $old_hash );
        }

        return $s;
    }

    private function get_setting( $key, $default = '' ) {
        $settings = get_option( self::OPTION_NAME, array() );
        return $settings[ $key ] ?? $default;
    }

    // --- Field Renderers ---

    public function field_station_name() {
        $v = $this->get_setting( 'station_name', '' );
        echo '<input type="text" name="lfsp_settings[station_name]" value="' . esc_attr( $v ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Example: "youfm" (laut.fm station slug)', self::TEXT_DOMAIN ) . '</p>';
    }

    public function field_station_slogan() {
        $v = $this->get_setting( 'station_slogan', '' );
        echo '<input type="text" name="lfsp_settings[station_slogan]" value="' . esc_attr( $v ) . '" class="regular-text" />';
    }

    public function field_custom_stream_url() {
        $v = $this->get_setting( 'custom_stream_url', '' );
        echo '<input type="url" name="lfsp_settings[custom_stream_url]" value="' . esc_attr( $v ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'If set, this URL will be used instead of the default laut.fm stream URL (forces inline playback).', self::TEXT_DOMAIN ) . '</p>';
    }

    public function field_player_position() {
        $v = $this->get_setting( 'player_position', 'bottom' );
        $options = array(
            'bottom' => __( 'Bottom', self::TEXT_DOMAIN ),
            'top'    => __( 'Top', self::TEXT_DOMAIN ),
            'left'   => __( 'Left', self::TEXT_DOMAIN ),
            'right'  => __( 'Right', self::TEXT_DOMAIN ),
        );
        echo '<select name="lfsp_settings[player_position]">';
        foreach ( $options as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '"' . selected( $v, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
    }

    public function field_player_height() {
        $v = (int) $this->get_setting( 'player_height', 90 );
        echo '<input type="number" min="60" max="200" step="1" name="lfsp_settings[player_height]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_accent_1() {
        $v = $this->get_setting( 'color_accent_1', '#ff003c' );
        echo '<input type="text" class="lfsp-color-picker" name="lfsp_settings[color_accent_1]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_accent_2() {
        $v = $this->get_setting( 'color_accent_2', '#00f0ff' );
        echo '<input type="text" class="lfsp-color-picker" name="lfsp_settings[color_accent_2]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_bg() {
        $v = $this->get_setting( 'color_bg', '#101010' );
        echo '<input type="text" class="lfsp-color-picker" name="lfsp_settings[color_bg]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_color_text() {
        $v = $this->get_setting( 'color_text', '#ffffff' );
        echo '<input type="text" class="lfsp-color-picker" name="lfsp_settings[color_text]" value="' . esc_attr( $v ) . '" />';
    }

    public function field_autoplay() {
        $this->render_checkbox( 'autoplay', __( 'Start playing automatically (may be blocked by browsers)', self::TEXT_DOMAIN ) );
    }

    public function field_show_clock() {
        $this->render_checkbox( 'show_clock', __( 'Show a live clock in the player', self::TEXT_DOMAIN ) );
    }

    public function field_show_toggle() {
        $this->render_checkbox( 'show_toggle', __( 'Show a toggle button to collapse/expand the player', self::TEXT_DOMAIN ) );
    }

    public function field_show_mobile() {
        $this->render_checkbox( 'show_on_mobile', __( 'Display the player on mobile devices', self::TEXT_DOMAIN ) );
    }

    public function field_default_closed() {
        $this->render_checkbox( 'default_closed', __( 'Player starts collapsed (user can still open it)', self::TEXT_DOMAIN ) );
    }

    public function field_stream_label() {
        $v = $this->get_setting( 'stream_link_label', 'STREAM' );
        echo '<input type="text" name="lfsp_settings[stream_link_label]" value="' . esc_attr( $v ) . '" class="regular-text" />';
    }

    public function field_show_soundnode() {
        $this->render_checkbox( 'show_soundnode', __( 'Show a small "soundnode.de" link in the player', self::TEXT_DOMAIN ) );
        echo '<p class="description">' . sprintf(
            esc_html__( '%s lists all laut.fm radio stations as an aggregator.', self::TEXT_DOMAIN ),
            '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
        ) . '</p>';
    }

    public function field_playback_mode() {
        $v = $this->get_setting( 'playback_mode', 'popup_website' );
        $options = array(
            'popup_website' => __( 'Popup: Open station website', self::TEXT_DOMAIN ),
            'popup_stream'  => __( 'Popup: Open stream only', self::TEXT_DOMAIN ),
            'inline'        => __( 'Inline: Play in sticky player', self::TEXT_DOMAIN ),
        );
        echo '<select id="lfsp-playback-mode" name="lfsp_settings[playback_mode]">';
        foreach ( $options as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '"' . selected( $v, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';

        echo '<p class="description lfsp-mode-info" data-mode="popup_website">' . esc_html__( 'Opens the laut.fm station page in a popup window.', self::TEXT_DOMAIN ) . '</p>';
        echo '<p class="description lfsp-mode-info" data-mode="popup_stream">' . esc_html__( 'Opens only the audio stream in a small popup window.', self::TEXT_DOMAIN ) . '</p>';
        echo '<p class="description lfsp-mode-info" data-mode="inline">' . esc_html__( 'Plays the audio stream directly inside the sticky player.', self::TEXT_DOMAIN ) . '</p>';
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
            <h1><?php echo esc_html__( 'SoundNode Sticky Player Settings', self::TEXT_DOMAIN ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::PAGE_SLUG );
                submit_button( __( 'Save Settings', self::TEXT_DOMAIN ) );
                ?>
            </form>

            <hr>

            <p style="color: #666; font-size: 12px;">
                <?php
                printf(
                    esc_html__( '%1$s | Discover all laut.fm stations on %2$s', self::TEXT_DOMAIN ),
                    '<strong>SoundNode Sticky Player</strong>',
                    '<a href="https://soundnode.de" target="_blank" rel="noopener">soundnode.de</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
