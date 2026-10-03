<?php
/** wp eval-file scripts/tests/enquiry-notifications.php (Local only, all mail intercepted). */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	WP_CLI::error( 'Run notification integration checks on a .local site only.' );
}
require_once ABSPATH . 'wp-admin/includes/template.php';
require_once ABSPATH . 'wp-admin/includes/user.php';
$pages = get_option( 'jg_seeded_pages' );
$fixture = 'JanogaGO notification test ' . wp_generate_uuid4();
$lead_ids = array();
$mail_calls = 0;
$mail_result = true;
$fail_save = false;
$redirect = '';
$last_mail = array();
$recipient = 'routing-first@example.invalid';
$user_id = 0;
$case_index = 0;
$test_email = 'routing-' . wp_generate_uuid4() . '@example.invalid';
$_SERVER['REMOTE_ADDR'] = 'notification-' . wp_generate_uuid4();
$duplicate_keys = array();
$capture_mail = function ( $result, $atts ) use ( &$mail_calls, &$mail_result, &$last_mail ) { ++$mail_calls; $last_mail = $atts; return $mail_result; };
$override_recipient = function () use ( &$recipient ) { return $recipient; };
$capture_lead = function ( $id, $post ) use ( &$lead_ids, $fixture ) {
	if ( $post->post_type === 'janogago_lead' && str_starts_with( $post->post_title, $fixture ) ) { $lead_ids[] = $id; }
};
$reject_save = function ( $empty, $post ) use ( &$fail_save ) { return $fail_save && $post['post_type'] === 'janogago_lead' ? true : $empty; };
$capture_redirect = function ( $url, $status ) use ( &$redirect ) {
	if ( $status !== 303 ) { throw new RuntimeException( 'Expected POST redirect status 303.' ); }
	$redirect = $url;
	throw new RuntimeException( 'notification-test-redirect' );
};
add_filter( 'pre_wp_mail', $capture_mail, 10, 2 );
add_filter( 'pre_option_jg_enquiry_recipient', $override_recipient );
add_action( 'wp_after_insert_post', $capture_lead, 10, 2 );
add_filter( 'wp_insert_post_empty_content', $reject_save, 10, 2 );
add_filter( 'wp_redirect', $capture_redirect, 1, 2 );
try {
	foreach ( array(
		array( 'lv', 'sent', true, false, false, 1, 1 ),
		array( 'en', 'sent', true, false, false, 1, 1 ),
		array( 'lv', 'sent', false, false, false, 1, 1 ),
		array( 'lv', 'failed', true, true, false, 0, 0 ),
		array( 'lv', 'invalid', true, false, true, 0, 0 ),
		array( 'en', 'sent', true, false, false, 1, 1, 'routing-second@example.invalid' ),
	) as $case ) {
		list( $lang, $expected, $mail_result, $fail_save, $invalid, $expected_mail, $expected_leads ) = $case;
		++$case_index;
		$recipient = $case[7] ?? 'routing-first@example.invalid';
		$before_leads = count( $lead_ids );
		$mail_calls = 0;
		$redirect = '';
		$_POST = array(
			'jg_enquiry_nonce' => wp_create_nonce( 'jg_submit_enquiry' ), 'page_id' => $pages[ $lang ],
			'company' => $fixture . ' ' . $case_index, 'name' => 'Test contact', 'email' => $test_email,
			'phone' => '+37120000000', 'location' => 'Test location', 'people' => '10',
			'message' => '', 'privacy_consent' => $invalid ? '' : '1', 'service_interest' => 'full-service',
		);
		$duplicate_keys[] = jg_enquiry_fingerprint( array( 'company' => $_POST['company'], 'name' => $_POST['name'], 'email' => $test_email, 'phone' => $_POST['phone'], 'location' => $_POST['location'], 'people' => $_POST['people'], 'message' => '', 'interest' => 'full-service' ) );
		try { jg_submit_enquiry(); } catch ( RuntimeException $error ) {
			if ( $error->getMessage() !== 'notification-test-redirect' ) { throw $error; }
		}
		$expected_url = add_query_arg( 'enquiry', $expected, get_permalink( $pages[ $lang ] ) ) . ( $expected === 'sent' ? '' : '#pieteikties' );
		if ( $redirect !== $expected_url || $mail_calls !== $expected_mail || count( $lead_ids ) - $before_leads !== $expected_leads ) {
			throw new RuntimeException( "Unexpected $lang $expected submission outcome." );
		}
		if ( $expected_leads && (int) get_post_meta( end( $lead_ids ), '_jg_notification_sent', true ) !== (int) $mail_result ) {
			throw new RuntimeException( 'Internal email outcome was not recorded.' );
		}
		if ( $expected_mail && ( $last_mail['to'] !== $recipient || $last_mail['headers'] !== array( 'Reply-To: Test contact <' . $test_email . '>' ) ) ) {
			throw new RuntimeException( 'Enquiry recipient or visitor Reply-To was incorrect.' );
		}
		$_GET['enquiry'] = $expected;
		$html = do_blocks( get_post_field( 'post_content', $pages[ $lang ], 'raw' ) );
		$role = $expected === 'sent' ? 'status' : 'alert';
		$notice = strpos( $html, 'id="jg-enquiry-result"' );
		$first_field = strpos( $html, 'name="company"' );
		if ( $notice === false || $notice > $first_field || ! str_contains( $html, 'role="' . $role . '" tabindex="-1"' ) ) {
			throw new RuntimeException( "Missing accessible $lang $expected notice above the form fields." );
		}
		WP_CLI::log( "$lang $expected: correct redirect, notice, lead and mail outcome (mail " . ( $mail_result ? 'accepted' : 'failed' ) . ').' );
	}
	$_GET['enquiry'] = 'unknown';
	if ( ! str_contains( do_blocks( get_post_field( 'post_content', $pages['lv'], 'raw' ) ), 'tabindex="-1" hidden' ) ) {
		throw new RuntimeException( 'Unknown status should not display a notice.' );
	}
	if ( jg_sanitize_enquiry_recipient( 'invalid-address' ) !== $recipient || jg_sanitize_enquiry_recipient( ' changed@example.invalid ' ) !== 'changed@example.invalid' ) {
		throw new RuntimeException( 'Recipient setting must accept valid addresses and preserve the saved address on invalid input.' );
	}
	$user_login = 'jg-routing-' . wp_generate_uuid4();
	$reset_email = $user_login . '@example.invalid';
	$user_id = wp_insert_user( array( 'user_login' => $user_login, 'user_email' => $reset_email, 'user_pass' => wp_generate_password() ) );
	if ( is_wp_error( $user_id ) ) { throw new RuntimeException( $user_id->get_error_message() ); }
	$mail_calls = 0;
	$mail_result = true;
	$reset_result = retrieve_password( $user_login );
	if ( is_wp_error( $reset_result ) || $mail_calls !== 1 || $last_mail['to'] !== $reset_email ) {
		throw new RuntimeException( 'Password resets must go to the account email, not the enquiry recipient.' );
	}
	WP_CLI::log( 'Recipient changes, visitor Reply-To and account password-reset routing passed.' );
	WP_CLI::success( 'Notification checks passed. All mail intercepted; no external email sent.' );
} finally {
	remove_filter( 'pre_wp_mail', $capture_mail );
	remove_filter( 'pre_option_jg_enquiry_recipient', $override_recipient );
	remove_action( 'wp_after_insert_post', $capture_lead, 10 );
	remove_filter( 'wp_insert_post_empty_content', $reject_save, 10 );
	remove_filter( 'wp_redirect', $capture_redirect, 1 );
	foreach ( $lead_ids as $id ) { wp_delete_post( $id, true ); }
	if ( $user_id && ! is_wp_error( $user_id ) ) { wp_delete_user( $user_id ); }
	foreach ( $duplicate_keys as $key ) { delete_transient( $key ); }
	delete_transient( 'jg_enquiry_ip_' . wp_hash( $_SERVER['REMOTE_ADDR'] ) );
	delete_transient( 'jg_enquiry_email_' . wp_hash( $test_email ) );
	$_POST = array();
	unset( $_GET['enquiry'] );
}
