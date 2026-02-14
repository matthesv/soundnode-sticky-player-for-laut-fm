<?php
/**
 * Plugin Name:       Laut.fm Sticky Player
 * Plugin URI:        https://github.com/matthesv/laut-fm-sticky-player
 * Description:       A customizable sticky audio player for any laut.fm radio station. Stream live radio directly on your WordPress site.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Matthes Vogel
 * Author URI:        https://soundnode.de
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       laut-fm-sticky-player
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Konstanten
define( 'LFSP_VERSION', '1.0.0' );
define( 'LFSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LFSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LFSP_BASENAME', plugin_basename( __FILE__ ) );

// ============================================
// AUTO-UPDATE VON GITHUB
// ============================================
require_once LFSP_PLUGIN_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$lfsp_update_checker = PucFactory::buildUpdateChecker(
    'https://github.com/matthesv/laut-fm-sticky-player/',  // GitHub Repo URL
    __FILE__,                                                // Haupt-Plugin-Datei
    'laut-fm-sticky-player'                                  // Plugin-Slug
);

// Optional: Auf einen bestimmten Branch hören (Standard: main)
$lfsp_update_checker->setBranch( 'main' );

// Aktivierung
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
    );
    add_option( 'lfsp_settings', $defaults );
    add_option( 'lfsp_version', LFSP_VERSION );
}

// Deaktivierung
register_deactivation_hook( __FILE__, 'lfsp_deactivate' );
function lfsp_deactivate() {
    // Transients aufräumen
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like( '_transient_lfsp_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_lfsp_' ) . '%'
        )
    );
}

// Klassen laden
require_once LFSP_PLUGIN_PATH . 'includes/class-lautfm-api.php';
require_once LFSP_PLUGIN_PATH . 'includes/class-sticky-player.php';
require_once LFSP_PLUGIN_PATH . 'includes/class-admin-settings.php';

// Initialisierung
function lfsp_init() {
    load_plugin_textdomain(
        'laut-fm-sticky-player',
        false,
        dirname( LFSP_BASENAME ) . '/languages'
    );

    $settings = get_option( 'lfsp_settings', array() );

    // Frontend: Nur laden wenn Station konfiguriert
    if ( ! empty( $settings['station_name'] ) && ! is_admin() ) {
        $player = new LFSP_Sticky_Player( $settings );
        $player->init();
    }

    // Admin
    if ( is_admin() ) {
        new LFSP_Admin_Settings();
    }
}
add_action( 'plugins_loaded', 'lfsp_init' );

// Settings-Link in Plugin-Liste
function lfsp_plugin_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=laut-fm-sticky-player' ) ) . '">'
        . esc_html__( 'Settings', 'laut-fm-sticky-player' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . LFSP_BASENAME, 'lfsp_plugin_action_links' );
