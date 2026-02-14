<?php
/**
 * Uninstall handler
 *
 * @package LFSP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Optionen entfernen
delete_option( 'lfsp_settings' );
delete_option( 'lfsp_version' );

// Alle Transients aufräumen
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like( '_transient_lfsp_' ) . '%',
        $wpdb->esc_like( '_transient_timeout_lfsp_' ) . '%'
    )
);
