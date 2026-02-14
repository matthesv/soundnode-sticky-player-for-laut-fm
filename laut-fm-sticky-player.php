<?php
/**
 * Plugin Name:       Laut.fm Sticky Player
 * Plugin URI:        https://github.com/matthesv/laut-fm-sticky-player
 * Description:       A customizable sticky audio player for any laut.fm radio station.
 * Version:           1.0.7
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

define( 'LFSP_VERSION', '1.0.7' );
define( 'LFSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LFSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LFSP_BASENAME', plugin_basename( __FILE__ ) );

$lfsp_puc_file = LFSP_PLUGIN_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';

if ( file_exists( $lfsp_puc_file ) ) {
    require_once $lfsp_puc_file;

    if ( class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
        $lfsp_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/matthesv/laut-fm-sticky-player/',
            __FILE__,
            'laut-fm-sticky-player'
        );
        $lfsp_update_checker->setBranch( 'main' );
    }
}

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

register_deactivation_hook( __FILE__, 'lfsp_deactivate' );
function lfsp_deactivate() {
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like( '_transient_lfsp_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_lfsp_' ) . '%'
        )
    );
}

require_once LFSP_PLUGIN_PATH . 'includes/class-lautfm-api.php';
require_once LFSP_PLUGIN_PATH . 'includes/class-sticky-player.php';
require_once LFSP_PLUGIN_PATH . 'includes/class-admin-settings.php';

function lfsp_init() {
    load_plugin_textdomain(
        'laut-fm-sticky-player',
        false,
        dirname( LFSP_BASENAME ) . '/languages'
    );

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

function lfsp_render_admin_notice() {
    $url = esc_url( admin_url( 'options-general.php?page=laut-fm-sticky-player' ) );
    echo '<!-- LFSP: No station configured. Visit ' . $url . ' -->';
    echo '<div style="position:fixed;bottom:0;left:0;right:0;background:#1a1a1a;color:#fff;padding:12px 20px;z-index:999999;font-family:sans-serif;font-size:14px;text-align:center;">';
    echo 'Laut.fm Sticky Player: <a href="' . $url . '" style="color:#00f0ff;text-decoration:underline;">Bitte zuerst einen Stationsnamen eingeben &rarr;</a>';
    echo '</div>';
}

function lfsp_plugin_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=laut-fm-sticky-player' ) ) . '">'
        . esc_html__( 'Settings', 'laut-fm-sticky-player' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . LFSP_BASENAME, 'lfsp_plugin_action_links' );
