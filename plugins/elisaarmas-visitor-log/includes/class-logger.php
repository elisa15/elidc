<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EA_VL_Logger {
	public static function init() {
		add_action( 'wpcf7_before_send_mail', array( __CLASS__, 'on_cf7_before_send' ), 10, 3 );
		add_filter( 'wpcf7_submission_result', array( __CLASS__, 'maybe_reject_blocked' ), 10, 2 );
	}

	/**
	 * Log every successful CF7 attempt and stop mail if IP is blocked.
	 *
	 * @param WPCF7_ContactForm $contact_form Form.
	 * @param bool              $abort        Abort flag (by ref).
	 * @param WPCF7_Submission  $submission   Submission.
	 */
	public static function on_cf7_before_send( $contact_form, &$abort, $submission ) {
		if ( ! $submission || ! is_a( $submission, 'WPCF7_Submission' ) ) {
			return;
		}

		$data = $submission->get_posted_data();
		$ip   = EA_VL_Blocker::client_ip();
		$geo  = EA_VL_Geo::lookup( $ip );

		$name    = isset( $data['your-name'] ) ? sanitize_text_field( (string) $data['your-name'] ) : '';
		$email   = isset( $data['your-email'] ) ? sanitize_email( (string) $data['your-email'] ) : '';
		$message = isset( $data['your-message'] ) ? sanitize_textarea_field( (string) $data['your-message'] ) : '';

		$blocked = EA_VL_DB::is_blocked( $ip );
		$status  = $blocked ? 'blocked_attempt' : 'submitted';

		EA_VL_DB::insert(
			array(
				'name'       => $name,
				'email'      => $email,
				'message'    => $message,
				'ip'         => $ip,
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'country'    => $geo['country'],
				'region'     => $geo['region'],
				'city'       => $geo['city'],
				'isp'        => $geo['isp'],
				'page_url'   => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( (string) wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : home_url( '/' ),
				'status'     => $status,
			)
		);

		if ( class_exists( 'EA_VL_Activity' ) ) {
			EA_VL_Activity::record(
				$blocked ? 'form_submit_failed' : 'form_submit_success',
				$blocked ? 'Blocked contact form attempt' : 'Contact form sent (server)',
				array(
					'element_text' => $name ? $name . ' <' . $email . '>' : $email,
				)
			);
		}

		if ( $blocked ) {
			$abort = true;
			$submission->set_status( 'validation_failed' );
			$submission->set_response( __( 'Sorry, your message could not be sent.', 'elisaarmas-visitor-log' ) );
		}
	}

	/**
	 * Extra safety: mark submission failed if IP is blocked.
	 */
	public static function maybe_reject_blocked( $result, $submission ) {
		$ip = EA_VL_Blocker::client_ip();
		if ( $ip && EA_VL_DB::is_blocked( $ip ) ) {
			$result['status']  = 'validation_failed';
			$result['message'] = __( 'Sorry, your message could not be sent.', 'elisaarmas-visitor-log' );
		}
		return $result;
	}
}
