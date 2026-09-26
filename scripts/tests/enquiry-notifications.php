<?php
/** wp eval-file scripts/tests/enquiry-notifications.php (Local only, all mail intercepted). */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	WP_CLI::error( 'Run notification integration checks on a .local site only.' );
}
$pages = get_option( 'jg_seeded_pages' );
$fixture = 'JanogaGO notification test ' . wp_generate_uuid4();
$lead_ids = array();
$mail_calls = 0;
$mail_result = true;
$fail_save = false;
$redirect = '';
$capture_mail = function () use ( &$mail_calls, &$mail_result ) { ++$mail_calls; return $mail_result; };
$capture_lead = function ( $id, $post ) use ( &$lead_ids, $fixture ) {
	if ( $post->post_type === 'janogago_lead' && str_starts_with( $post->post_title, $fixture ) ) { $lead_ids[] = $id; }
};
$reject_save = function ( $empty, $post ) use ( &$fail_save ) { return $fail_save && $post['post_type'] === 'janogago_lead' ? true : $empty; };
$capture_redirect = function ( $url, $status ) use ( &$redirect ) {
	if ( $status !== 303 ) { throw new RuntimeException( 'Expected POST redirect status 303.' ); }
	$redirect = $url;
	throw new RuntimeException( 'notification-test-redirect' );
};
add_filter( 'pre_wp_mail', $capture_mail );
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
	) as $case ) {
		list( $lang, $expected, $mail_result, $fail_save, $invalid, $expected_mail, $expected_leads ) = $case;
		$before_leads = count( $lead_ids );
		$mail_calls = 0;
		$redirect = '';
		$_POST = array(
			'jg_enquiry_nonce' => wp_create_nonce( 'jg_submit_enquiry' ), 'page_id' => $pages[ $lang ],
			'company' => $fixture, 'name' => 'Test contact', 'email' => 'test@example.invalid',
			'phone' => '+37120000000', 'location' => 'Test location', 'people' => '10',
			'message' => '', 'privacy_consent' => $invalid ? '' : '1', 'service_interest' => 'full-service',
		);
		try { jg_submit_enquiry(); } catch ( RuntimeException $error ) {
			if ( $error->getMessage() !== 'notification-test-redirect' ) { throw $error; }
		}
		$expected_url = add_query_arg( 'enquiry', $expected, get_permalink( $pages[ $lang ] ) ) . '#pieteikties';
		if ( $redirect !== $expected_url || $mail_calls !== $expected_mail || count( $lead_ids ) - $before_leads !== $expected_leads ) {
			throw new RuntimeException( "Unexpected $lang $expected submission outcome." );
		}
		if ( $expected_leads && (int) get_post_meta( end( $lead_ids ), '_jg_notification_sent', true ) !== (int) $mail_result ) {
			throw new RuntimeException( 'Internal email outcome was not recorded.' );
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
	if ( str_contains( do_blocks( get_post_field( 'post_content', $pages['lv'], 'raw' ) ), 'id="jg-enquiry-result"' ) ) {
		throw new RuntimeException( 'Unknown status should not display a notice.' );
	}
	WP_CLI::success( 'Notification checks passed. All mail intercepted; no external email sent.' );
} finally {
	remove_filter( 'pre_wp_mail', $capture_mail );
	remove_action( 'wp_after_insert_post', $capture_lead, 10 );
	remove_filter( 'wp_insert_post_empty_content', $reject_save, 10 );
	remove_filter( 'wp_redirect', $capture_redirect, 1 );
	foreach ( $lead_ids as $id ) { wp_delete_post( $id, true ); }
	$_POST = array();
	unset( $_GET['enquiry'] );
}
