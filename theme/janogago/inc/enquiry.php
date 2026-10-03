<?php
/** Enquiry feedback and server-side abuse controls. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function jg_enquiry_feedback_defaults( $language ) {
	return $language === 'en' ? array(
		'email_invalid' => 'Enter a valid email address, for example name@example.com.',
		'people_invalid' => 'Enter a whole number greater than zero.',
		'field_required' => 'Complete this field.',
		'consent_required' => 'Tick the consent checkbox before sending.',
		'too_long' => 'Please shorten this entry.',
		'rate_limited' => 'Too many enquiries have been sent. Please wait 10 minutes or contact us by email or phone.',
		'busy' => 'Your enquiry is being processed. Please wait a moment before trying again.',
		'expired' => 'Your form session expired. Please submit again. Your entries have been kept.',
		'network' => 'We could not confirm that your enquiry was received. Please try again. Your entries have been kept.',
	) : array(
		'email_invalid' => 'Ievadiet derīgu e-pasta adresi, piemēram, vards@piemers.lv.',
		'people_invalid' => 'Ievadiet veselu skaitli, kas ir lielāks par nulli.',
		'field_required' => 'Aizpildiet šo lauku.',
		'consent_required' => 'Pirms nosūtīšanas atzīmējiet piekrišanu.',
		'too_long' => 'Lūdzu, saīsiniet šo ierakstu.',
		'rate_limited' => 'Nosūtīts pārāk daudz pieprasījumu. Lūdzu, uzgaidiet 10 minūtes vai sazinieties ar mums pa e-pastu vai tālruni.',
		'busy' => 'Jūsu pieprasījums tiek apstrādāts. Lūdzu, uzgaidiet brīdi, pirms mēģināt vēlreiz.',
		'expired' => 'Veidlapas sesija ir beigusies. Lūdzu, nosūtiet vēlreiz. Ievadītā informācija ir saglabāta.',
		'network' => 'Neizdevās apstiprināt pieprasījuma saņemšanu. Lūdzu, mēģiniet vēlreiz. Ievadītā informācija ir saglabāta.',
	);
}

function jg_enquiry_page_copy( $page_id ) {
	$language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page_id ) : jg_lang();
	$queue = parse_blocks( (string) get_post_field( 'post_content', $page_id ) );
	while ( $queue ) {
		$block = array_shift( $queue );
		if ( in_array( 'jg-enquiry-form', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
			return array_merge( jg_enquiry_feedback_defaults( $language ), jg_form_block_copy( $block ) );
		}
		$queue = array_merge( $block['innerBlocks'] ?? array(), $queue );
	}
	$defaults = jg_defaults( $language );
	$copy = jg_enquiry_feedback_defaults( $language );
	foreach ( array( 'success', 'invalid', 'phone_invalid', 'failed' ) as $key ) { $copy[ $key ] = $defaults[ 'form_' . $key . '_message' ] ?? ''; }
	return $copy;
}

function jg_enquiry_form_attributes( $copy ) {
	return ' data-feedback="' . esc_attr( wp_json_encode( $copy ) ) . '"';
}

function jg_enquiry_trap_markup() {
	return '<div class="jg-form-trap" aria-hidden="true"><label>Website<input type="text" name="jg_website" tabindex="-1" autocomplete="off"></label></div>';
}

function jg_enquiry_fingerprint( $details ) {
	return 'jg_enquiry_duplicate_' . wp_hash( wp_json_encode( $details ) );
}

/** Serialize the short counter update so simultaneous requests cannot bypass it. */
function jg_enquiry_guard( $details ) {
	global $wpdb;
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
	$lock = 'jg_enquiry_' . wp_hash( home_url() );
	if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 1)', $lock ) ) ) { return 'busy'; }
	try {
		$duplicate_key = jg_enquiry_fingerprint( $details );
		$duplicate = get_transient( $duplicate_key );
		if ( $duplicate ) { return $duplicate === 'sent' ? 'sent' : 'busy'; }
		$keys = array( 'jg_enquiry_ip_' . wp_hash( $ip ), 'jg_enquiry_email_' . wp_hash( strtolower( $details['email'] ) ) );
		foreach ( $keys as $key ) {
			if ( (int) get_transient( $key ) >= 5 ) { return 'rate_limited'; }
		}
		foreach ( $keys as $key ) { set_transient( $key, (int) get_transient( $key ) + 1, 10 * MINUTE_IN_SECONDS ); }
		set_transient( $duplicate_key, 'pending', 2 * MINUTE_IN_SECONDS );
		return '';
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
	}
}
