<?php
/**
 * Uninstall cleanup for SoundNode Sticky Player for laut.fm.
 *
 * @package LFSP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$lfsp_settings = get_option( 'lfsp_settings', array() );
$lfsp_station  = $lfsp_settings['station_name'] ?? '';

if ( ! empty( $lfsp_station ) ) {
    $lfsp_hash = md5( sanitize_key( $lfsp_station ) );

    delete_transient( 'lfsp_station_' . $lfsp_hash );
    delete_transient( 'lfsp_song_' . $lfsp_hash );
    delete_transient( 'lfsp_lastsongs_' . $lfsp_hash );
}

delete_option( 'lfsp_settings' );
delete_option( 'lfsp_version' );
