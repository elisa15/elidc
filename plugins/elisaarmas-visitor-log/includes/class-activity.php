<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EA_VL_Activity {
	const ALLOWED_EVENTS = array(
		'page_view',
		'click_button',
		'click_link',
		'open_contact_modal',
		'close_contact_modal',
		'form_submit_attempt',
		'form_submit_success',
		'form_submit_failed',
		'mailto_click',
		'nav_click',
		'tab_click',
		'scroll_section',
	);

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_ajax_ea_vl_track', array( __CLASS__, 'ajax_track' ) );
		add_action( 'wp_ajax_nopriv_ea_vl_track', array( __CLASS__, 'ajax_track' ) );
	}

	public static function enqueue() {
		if ( is_admin() ) {
			return;
		}
		// Don't pollute logs with your own admin browsing.
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_script(
			'ea-vl-tracker',
			EA_VL_URL . 'assets/js/tracker.js',
			array(),
			EA_VL_VERSION,
			true
		);

		wp_localize_script(
			'ea-vl-tracker',
			'eaVlTrack',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ea_vl_track' ),
				'pageUrl' => home_url( add_query_arg( array() ) ),
			)
		);
	}

	public static function ajax_track() {
		check_ajax_referer( 'ea_vl_track', 'nonce' );

		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			wp_send_json_success( array( 'skipped' => 'admin' ) );
		}

		$ip = EA_VL_Blocker::client_ip();
		if ( ! $ip ) {
			wp_send_json_error( array( 'message' => 'no ip' ), 400 );
		}

		if ( ! self::rate_limit_ok( $ip ) ) {
			wp_send_json_error( array( 'message' => 'rate limited' ), 429 );
		}

		$event_type = isset( $_POST['event_type'] ) ? sanitize_key( wp_unslash( $_POST['event_type'] ) ) : '';
		if ( ! in_array( $event_type, self::ALLOWED_EVENTS, true ) ) {
			wp_send_json_error( array( 'message' => 'invalid event' ), 400 );
		}

		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
		$session_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', $session_id );
		if ( strlen( $session_id ) < 8 ) {
			$session_id = wp_generate_password( 16, false );
		}

		$event_label  = isset( $_POST['event_label'] ) ? sanitize_text_field( wp_unslash( $_POST['event_label'] ) ) : '';
		$element_text = isset( $_POST['element_text'] ) ? sanitize_text_field( wp_unslash( $_POST['element_text'] ) ) : '';
		$element_href = isset( $_POST['element_href'] ) ? esc_url_raw( wp_unslash( $_POST['element_href'] ) ) : '';
		$page_url     = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

		if ( ! $page_url ) {
			$page_url = home_url( '/' );
		}

		$geo = EA_VL_Geo::lookup( $ip );

		$id = EA_VL_DB::insert_activity(
			array(
				'session_id'   => $session_id,
				'ip'           => $ip,
				'event_type'   => $event_type,
				'event_label'  => $event_label,
				'page_url'     => $page_url,
				'element_text' => $element_text,
				'element_href' => $element_href,
				'country'      => $geo['country'],
				'region'       => $geo['region'],
				'city'         => $geo['city'],
				'isp'          => $geo['isp'],
				'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			)
		);

		wp_send_json_success( array( 'id' => $id ) );
	}

	/**
	 * Also record server-side activity (e.g. CF7 success).
	 */
	public static function record( $event_type, $label = '', $extra = array() ) {
		if ( ! in_array( $event_type, self::ALLOWED_EVENTS, true ) ) {
			return 0;
		}
		$ip = EA_VL_Blocker::client_ip();
		if ( ! $ip ) {
			return 0;
		}
		$geo = EA_VL_Geo::lookup( $ip );
		return EA_VL_DB::insert_activity(
			array_merge(
				array(
					'session_id'  => '',
					'ip'          => $ip,
					'event_type'  => $event_type,
					'event_label' => $label,
					'page_url'    => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( (string) wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : home_url( '/' ),
					'country'     => $geo['country'],
					'region'      => $geo['region'],
					'city'        => $geo['city'],
					'isp'         => $geo['isp'],
					'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				),
				$extra
			)
		);
	}

	private static function rate_limit_ok( $ip ) {
		$key   = 'ea_vl_rl_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= 80 ) {
			return false;
		}
		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
		return true;
	}
}
