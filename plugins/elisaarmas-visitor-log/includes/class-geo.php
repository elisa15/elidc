<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EA_VL_Geo {
	/**
	 * Lookup location for an IP (cached 7 days). Uses ip-api.com (HTTP, free, no key).
	 */
	public static function lookup( $ip ) {
		$empty = array(
			'country' => '',
			'region'  => '',
			'city'    => '',
			'isp'     => '',
		);

		$ip = trim( (string) $ip );
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $empty;
		}
		if ( self::is_private( $ip ) ) {
			return array_merge( $empty, array( 'country' => 'Private/local' ) );
		}

		$cache_key = 'ea_vl_geo_' . md5( $ip );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$url      = 'http://ip-api.com/json/' . rawurlencode( $ip ) . '?fields=status,message,country,regionName,city,isp,query';
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 2,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $empty;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 200 !== (int) $code || ! is_array( $body ) || ( $body['status'] ?? '' ) !== 'success' ) {
			return $empty;
		}

		$result = array(
			'country' => sanitize_text_field( (string) ( $body['country'] ?? '' ) ),
			'region'  => sanitize_text_field( (string) ( $body['regionName'] ?? '' ) ),
			'city'    => sanitize_text_field( (string) ( $body['city'] ?? '' ) ),
			'isp'     => sanitize_text_field( (string) ( $body['isp'] ?? '' ) ),
		);

		set_transient( $cache_key, $result, WEEK_IN_SECONDS );
		return $result;
	}

	private static function is_private( $ip ) {
		return ! filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}
}
