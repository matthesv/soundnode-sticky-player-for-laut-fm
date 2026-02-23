<?php
/**
 * Plugin Name:       SoundNode Sticky Player for laut.fm
 * Plugin URI:        https://github.com/matthesv/laut-fm-sticky-player
 * Description:       A customizable sticky audio player for any laut.fm radio station.
 * Version:           1.4.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Matthes Vogel
 * Author URI:        https://soundnode.de
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       soundnode-sticky-player-for-laut-fm
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LFSP_VERSION', '1.4.0' );
define( 'LFSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LFSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LFSP_BASENAME', plugin_basename( __FILE__ ) );

register_activation_hook( __FILE__, 'lfsp_activate' );
function lfsp_activate() {
    $defaults = array(
        'station_name'      => '',
        'station_slogan'    => '',
        'player_position'   => 'bottom',
        'color_accent_1'    => '#ff003c',
        'color_accent_2'    => '#00f0ff',
        'color_bg'          => '#101010',
        'color_text'        => '#ffffff',
        'show_clock'        => true,
        'show_toggle'       => true,
        'show_on_mobile'    => true,
        'show_soundnode'    => true,
        'player_height'     => 90,
        'autoplay'          => false,
        'stream_link_label' => 'STREAM',
        'default_closed'    => false,
        'playback_mode'     => 'popup_website',
        'custom_stream_url' => '',
    );
    add_option( 'lfsp_settings', $defaults );
    add_option( 'lfsp_version', LFSP_VERSION );
}

register_deactivation_hook( __FILE__, 'lfsp_deactivate' );
function lfsp_deactivate() {
    $settings = get_option( 'lfsp_settings', array() );
    $station  = $settings['station_name'] ?? '';

    if ( ! empty( $station ) ) {
        $hash = md5( sanitize_key( $station ) );
        delete_transient( 'lfsp_station_' . $hash );
        delete_transient( 'lfsp_song_' . $hash );
        delete_transient( 'lfsp_lastsongs_' . $hash );
    }
}

require_once LFSP_PLUGIN_PATH . 'includes/class-lautfm-api.php';
require_once LFSP_PLUGIN_PATH . 'includes/class-sticky-player.php';
require_once LFSP_PLUGIN_PATH . 'includes/class-admin-settings.php';

function lfsp_init() {
    lfsp_maybe_migrate_settings();

    $settings = get_option( 'lfsp_settings', array() );
    $station  = $settings['station_name'] ?? '';

    if ( ! empty( $station ) ) {
        $player = new LFSP_Sticky_Player( $settings );
        $player->register_ajax();

        if ( ! is_admin() ) {
            $player->init_frontend();
        }
    } elseif ( ! is_admin() && current_user_can( 'manage_options' ) ) {
        add_action( 'wp_footer', 'lfsp_render_admin_notice' );
    }

    if ( is_admin() ) {
        new LFSP_Admin_Settings();
    }
}
add_action( 'plugins_loaded', 'lfsp_init' );

function lfsp_maybe_migrate_settings() {
    $settings = get_option( 'lfsp_settings', array() );

    if ( isset( $settings['inline_playback'] ) && ! isset( $settings['playback_mode'] ) ) {
        $settings['playback_mode'] = ! empty( $settings['inline_playback'] ) ? 'inline' : 'popup_website';
        unset( $settings['inline_playback'] );
        update_option( 'lfsp_settings', $settings );
    }
}

function lfsp_render_admin_notice() {
    $url = admin_url( 'options-general.php?page=soundnode-sticky-player' );

    echo '<div style="position:fixed;bottom:0;left:0;right:0;background:#1a1a1a;color:#fff;padding:12px 20px;z-index:999999;font-family:sans-serif;font-size:14px;text-align:center;">';
    echo 'SoundNode Sticky Player: <a href="' . esc_url( $url ) . '" style="color:#00f0ff;text-decoration:underline;">';
    echo esc_html__( 'Please enter a station name first', 'soundnode-sticky-player-for-laut-fm' );
    echo ' &rarr;</a></div>';
}

function lfsp_plugin_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=soundnode-sticky-player' ) ) . '">'
        . esc_html__( 'Settings', 'soundnode-sticky-player-for-laut-fm' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . LFSP_BASENAME, 'lfsp_plugin_action_links' );
