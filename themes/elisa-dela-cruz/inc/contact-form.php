<?php
/**
 * Contact Form 7 integration: modal styling, honeypot, JS challenge, spam checks.
 *
 * @package elisa-dela-cruz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modal / CF7 field styles so the shortcode matches the mockup form.
 */
function edc_contact_form_styles() {
	if ( is_admin() ) {
		return;
	}

	$css = <<<'CSS'
:is(.modal-box,.contact-form) .wpcf7{margin:0}
:is(.modal-box,.contact-form) .wpcf7 form{display:flex;flex-direction:column;gap:14px;margin:0}
:is(.modal-box,.contact-form) .wpcf7 form p{margin:0}
:is(.modal-box,.contact-form) .wpcf7-form-control-wrap{display:block}
:is(.modal-box,.contact-form) .wpcf7 input[type=text],
:is(.modal-box,.contact-form) .wpcf7 input[type=email],
:is(.modal-box,.contact-form) .wpcf7 textarea{width:100%;background:var(--night-900);border:1px solid var(--line-strong);border-radius:var(--radius-sm);padding:12px 14px;color:var(--paper);font:inherit;font-size:16px;line-height:1.4;transition:border-color var(--dur-ui) var(--ease)}
:is(.modal-box,.contact-form) .wpcf7 textarea{min-height:110px;resize:vertical}
:is(.modal-box,.contact-form) .wpcf7 input::placeholder,
:is(.modal-box,.contact-form) .wpcf7 textarea::placeholder{color:var(--haze)}
:is(.modal-box,.contact-form) .wpcf7 input:focus,
:is(.modal-box,.contact-form) .wpcf7 textarea:focus{border-color:var(--magenta)}
:is(.modal-box,.contact-form) .wpcf7 input.wpcf7-submit{width:100%;min-height:48px;padding:0 22px;border:0;border-radius:var(--radius-md);background:var(--grad-btn);background-size:140% 100%;color:#fff;font:700 15px/1 var(--font-sans);cursor:pointer;transition:background-position var(--dur-panel) var(--ease)}
:is(.modal-box,.contact-form) .wpcf7 input.wpcf7-submit:hover{background-position:100% 0}
:is(.modal-box,.contact-form) .wpcf7 input.wpcf7-submit:focus-visible{outline-color:var(--paper)}
:is(.modal-box,.contact-form) .wpcf7 .wpcf7-not-valid{border-color:#ff5c7a !important}
:is(.modal-box,.contact-form) .wpcf7 .wpcf7-not-valid-tip{display:block;margin-top:6px;font-size:13px;color:#ff8aa0}
:is(.modal-box,.contact-form) .wpcf7 .wpcf7-response-output{margin:4px 0 0;padding:12px 14px;border-radius:var(--radius-sm);font-size:14px;line-height:1.5;border:1px solid var(--line)}
:is(.modal-box,.contact-form) .wpcf7 form.sent .wpcf7-response-output{border-color:rgba(126,226,184,.45);background:rgba(126,226,184,.08);color:var(--mint)}
:is(.modal-box,.contact-form) .wpcf7 form.failed .wpcf7-response-output,
:is(.modal-box,.contact-form) .wpcf7 form.aborted .wpcf7-response-output,
:is(.modal-box,.contact-form) .wpcf7 form.spam .wpcf7-response-output,
:is(.modal-box,.contact-form) .wpcf7 form.invalid .wpcf7-response-output{border-color:rgba(255,92,122,.45);background:rgba(255,92,122,.08);color:#ffb3c0}
/* Honeypot / challenge fields — hidden from humans */
.edc-hp{position:absolute !important;left:-10000px !important;top:auto !important;width:1px !important;height:1px !important;overflow:hidden !important;opacity:0 !important;pointer-events:none !important}
CSS;

	wp_add_inline_style( 'edc-site', $css );
}
add_action( 'wp_enqueue_scripts', 'edc_contact_form_styles', 40 );

/**
 * Shared signing key for the JS form challenge.
 *
 * @return string
 */
function edc_cf7_challenge_key() {
	if ( defined( 'AUTH_KEY' ) && AUTH_KEY && false === strpos( AUTH_KEY, 'put your unique phrase' ) ) {
		return AUTH_KEY;
	}
	return wp_salt( 'auth' );
}

/**
 * Build an HMAC challenge token for a unix timestamp.
 *
 * @param int $ts Timestamp.
 * @return string
 */
function edc_cf7_challenge_token( $ts ) {
	return hash_hmac( 'sha256', (string) (int) $ts, edc_cf7_challenge_key() );
}

/**
 * Inject honeypot + empty JS challenge fields (filled by assets/js/site.js when the modal opens).
 *
 * @param string $html Form HTML.
 * @return string
 */
function edc_cf7_inject_spam_fields( $html ) {
	$honeypot  = '<span class="edc-hp" aria-hidden="true">';
	$honeypot .= '<label for="edc_hp_field">Leave blank</label>';
	$honeypot .= '<input type="text" id="edc_hp_field" name="edc_hp_field" value="" tabindex="-1" autocomplete="new-password" inputmode="none">';
	$honeypot .= '<input type="hidden" name="edc_js_ts" id="edc_js_ts" value="">';
	$honeypot .= '<input type="hidden" name="edc_js_token" id="edc_js_token" value="">';
	$honeypot .= '</span>';

	return $honeypot . $html;
}
add_filter( 'wpcf7_form_elements', 'edc_cf7_inject_spam_fields', 20 );

/**
 * Expose a lightweight challenge endpoint used by the contact modal JS.
 * Avoids baking secrets into cached HTML.
 */
function edc_cf7_register_challenge_route() {
	register_rest_route(
		'edc/v1',
		'/cf7-challenge',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => static function () {
				$ts    = time();
				$token = edc_cf7_challenge_token( $ts );
				return rest_ensure_response(
					array(
						'ts'    => $ts,
						'token' => $token,
					)
				);
			},
		)
	);
}
add_action( 'rest_api_init', 'edc_cf7_register_challenge_route' );

/**
 * Mark submission as spam for honeypot / missing JS challenge / rate limits.
 *
 * @param bool $spam Whether CF7 already flagged spam.
 * @return bool
 */
function edc_cf7_spam_checks( $spam ) {
	if ( $spam ) {
		return $spam;
	}

	// Honeypot filled => bot.
	if ( ! empty( $_POST['edc_hp_field'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return true;
	}
	if ( ! empty( $_POST['edc_website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return true;
	}

	$ts    = isset( $_POST['edc_js_ts'] ) ? (int) wp_unslash( $_POST['edc_js_ts'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$token = isset( $_POST['edc_js_token'] ) ? (string) wp_unslash( $_POST['edc_js_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	// Require JS challenge (blocks direct REST API spam).
	if ( $ts < 1 || $token === '' || ! hash_equals( edc_cf7_challenge_token( $ts ), $token ) ) {
		return true;
	}

	$age = time() - $ts;
	// Too fast (<3s) or too old (>2h) => reject.
	if ( $age < 3 || $age > 7200 ) {
		return true;
	}

	// Rate-limit by IP: max 2 accepted attempts per hour.
	$ip = function_exists( 'edc_client_ip' ) ? edc_client_ip() : ( isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '' );
	if ( $ip !== '' ) {
		$key   = 'edc_cf7_rl_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= 2 ) {
			return true;
		}
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	}

	return $spam;
}
add_filter( 'wpcf7_spam', 'edc_cf7_spam_checks', 20 );

/**
 * Block disposable / obviously fake emails.
 *
 * @param WPCF7_Validation $result Validation result.
 * @param WPCF7_FormTag    $tag    Field tag.
 * @return WPCF7_Validation
 */
function edc_cf7_validate_email_extra( $result, $tag ) {
	$name = $tag->name;
	if ( 'your-email' !== $name ) {
		return $result;
	}

	$value = isset( $_POST[ $name ] ) ? trim( wp_unslash( $_POST[ $name ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( $value === '' || ! is_email( $value ) ) {
		return $result;
	}

	$domain = strtolower( substr( strrchr( $value, '@' ), 1 ) );
	$blocked_domains = array(
		'mailinator.com',
		'guerrillamail.com',
		'10minutemail.com',
		'tempmail.com',
		'trashmail.com',
		'yopmail.com',
		'shamail.com',
		'temp-mail.org',
		'fakeinbox.com',
		'sharklasers.com',
	);

	if ( in_array( $domain, $blocked_domains, true ) ) {
		$result->invalidate( $tag, __( 'Please use a permanent work or personal email address.', 'elisa-dela-cruz' ) );
	}

	return $result;
}
add_filter( 'wpcf7_validate_email*', 'edc_cf7_validate_email_extra', 20, 2 );
add_filter( 'wpcf7_validate_email', 'edc_cf7_validate_email_extra', 20, 2 );

/**
 * Message content spam heuristics — includes active campaign templates hitting this form.
 *
 * @param WPCF7_Validation $result Validation result.
 * @param WPCF7_FormTag    $tag    Field tag.
 * @return WPCF7_Validation
 */
function edc_cf7_validate_message_extra( $result, $tag ) {
	if ( 'your-message' !== $tag->name ) {
		return $result;
	}

	$value = isset( $_POST['your-message'] ) ? trim( wp_unslash( $_POST['your-message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( $value === '' ) {
		return $result;
	}

	$lower = strtolower( $value );
	$lower = preg_replace( '/\s+/', ' ', $lower );

	$spam_needles = array(
		'viagra',
		'casino',
		'crypto airdrop',
		'seo ranking guaranteed',
		'https://bit.ly',
		// Active bot template on elisaarmas.com (Sep 2026):
		'i would like more information. please contact me by email',
		'please contact me by email',
		"i'd like to subscribe to weekly updates",
		'i am happy to receive emails',
		'hours of operation are',
		'confirm what date you open',
		'if you do not wish to receive',
	);
	foreach ( $spam_needles as $needle ) {
		if ( false !== strpos( $lower, $needle ) ) {
			$result->invalidate( $tag, __( 'Your message could not be sent. Please revise and try again.', 'elisa-dela-cruz' ) );
			return $result;
		}
	}

	// Signature spam often appends the site owner name.
	if ( preg_match( '/elisa\s+dela\s+cruz\.?\s*$/i', $value ) && strlen( $value ) < 180 ) {
		$result->invalidate( $tag, __( 'Your message could not be sent. Please revise and try again.', 'elisa-dela-cruz' ) );
		return $result;
	}

	// Too many URLs is a common spam signal.
	if ( preg_match_all( '#https?://#i', $value ) >= 3 ) {
		$result->invalidate( $tag, __( 'Please limit the number of links in your message.', 'elisa-dela-cruz' ) );
	}

	return $result;
}
add_filter( 'wpcf7_validate_textarea*', 'edc_cf7_validate_message_extra', 20, 2 );
add_filter( 'wpcf7_validate_textarea', 'edc_cf7_validate_message_extra', 20, 2 );


/**
 * Force a branded From name/address for CF7 mail (stops generic "WordPress" sender).
 */
function edc_cf7_mail_from_name( $name ) {
	return 'Elisa Dela Cruz';
}
function edc_cf7_mail_from_email( $email ) {
	return 'hello@elisaarmas.com';
}
function edc_cf7_bind_mail_identity() {
	add_filter( 'wp_mail_from_name', 'edc_cf7_mail_from_name', 20 );
	add_filter( 'wp_mail_from', 'edc_cf7_mail_from_email', 20 );
}
function edc_cf7_unbind_mail_identity() {
	remove_filter( 'wp_mail_from_name', 'edc_cf7_mail_from_name', 20 );
	remove_filter( 'wp_mail_from', 'edc_cf7_mail_from_email', 20 );
}
add_action( 'wpcf7_before_send_mail', 'edc_cf7_bind_mail_identity', 1 );
add_action( 'wpcf7_mail_sent', 'edc_cf7_unbind_mail_identity', 999 );
add_action( 'wpcf7_mail_failed', 'edc_cf7_unbind_mail_identity', 999 );
