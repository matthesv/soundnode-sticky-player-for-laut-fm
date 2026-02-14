<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LFSP_Lautfm_API {

    const API_BASE    = 'https://api.laut.fm';
    const STREAM_BASE = 'https://stream.laut.fm';

    public static function get_station_info( $station_name ) {
        $station_name = sanitize_key( $station_name );

        $cache_key = 'lfsp_station_' . md5( $station_name );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $url      = self::API_BASE . '/station/' . rawurlencode( $station_name );
        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array( 'Accept' => 'application/json' ),
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $data ) ) {
            return false;
        }

        set_transient( $cache_key, $data, 10 * MINUTE_IN_SECONDS );
        return $data;
    }

    public static function get_current_song( $station_name ) {
        $station_name = sanitize_key( $station_name );

        $cache_key = 'lfsp_song_' . md5( $station_name );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $url      = self::API_BASE . '/station/' . rawurlencode( $station_name ) . '/current_song';
        $response = wp_remote_get( $url, array(
            'timeout' => 5,
            'headers' => array( 'Accept' => 'application/json' ),
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $data ) ) {
            return false;
        }

        $ends_at = strtotime( $data['ends_at'] ?? '' );
        $now     = time();
        $ttl     = ( $ends_at && $ends_at > $now ) ? ( $ends_at - $now + 2 ) : 20;

        set_transient( $cache_key, $data, $ttl );
        return $data;
    }

    public static function get_last_songs( $station_name ) {
        $station_name = sanitize_key( $station_name );

        $cache_key = 'lfsp_lastsongs_' . md5( $station_name );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $url      = self::API_BASE . '/station/' . rawurlencode( $station_name ) . '/last_songs';
        $response = wp_remote_get( $url, array(
            'timeout' => 5,
            'headers' => array( 'Accept' => 'application/json' ),
        ) );

        if ( is_wp_error( $response ) || ! is_array( json_decode( wp_remote_retrieve_body( $response ), true ) ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        set_transient( $cache_key, $data, 20 );
        return $data;
    }

    public static function get_stream_url( $station_name ) {
        return self::STREAM_BASE . '/' . rawurlencode( sanitize_key( $station_name ) );
    }

    public static function station_exists( $station_name ) {
        $info = self::get_station_info( $station_name );
        return ( false !== $info && ! empty( $info['name'] ) );
    }

    public static function get_soundnode_url( $station_name ) {
        return 'https://soundnode.de/sender/' . rawurlencode( sanitize_key( $station_name ) );
    }
}
