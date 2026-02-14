<?php
/**
 * Uninstall cleanup for Laut.fm Sticky Player.
 *
 * @package LFSP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$settings = get_option( 'lfsp_settings', array() );
$station  = $settings['station_name'] ?? '';

if ( ! empty( $station ) ) {
    $hash = md5( sanitize_key( $station ) );
    delete_transient( 'lfsp_station_' . $hash );
    delete_transient( 'lfsp_song_' . $hash );
    delete_transient( 'lfsp_lastsongs_' . $hash );
}

delete_option( 'lfsp_settings' );
delete_option( 'lfsp_version' );
