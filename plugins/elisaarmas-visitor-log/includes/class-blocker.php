<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EA_VL_Blocker {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_block' ), 0 );
	}

	public static function client_ip() {
		$candidates = array();

		// Prefer direct remote addr; only trust proxy headers if they look like a single IP.
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$candidates[] = (string) wp_unslash( $_SERVER['REMOTE_ADDR'] );
		}
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$candidates[] = (string) wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		}
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', (string) wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			if ( ! empty( $parts[0] ) ) {
				$candidates[] = trim( $parts[0] );
			}
		}

		foreach ( $candidates as $ip ) {
			$ip = trim( $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		return '';
	}

	public static function maybe_block() {
		// Never lock yourself out of wp-admin while logged in.
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return;
		}

		// Allow login page so you can still recover if needed.
		if ( self::is_login_request() ) {
			return;
		}

		$ip = self::client_ip();
		if ( ! $ip || ! EA_VL_DB::is_blocked( $ip ) ) {
			return;
		}

		status_header( 403 );
		nocache_headers();
		wp_die(
			esc_html__( 'Access denied.', 'elisaarmas-visitor-log' ),
			esc_html__( 'Forbidden', 'elisaarmas-visitor-log' ),
			array( 'response' => 403 )
		);
	}

	private static function is_login_request() {
		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return true;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		return ( false !== strpos( $uri, 'wp-login.php' ) );
	}
}
