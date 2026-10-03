<?php
/** Local integration checks. No external mail is sent. */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) { WP_CLI::error( 'Local only.' ); }
if ( ! defined( 'DOING_AJAX' ) ) { define( 'DOING_AJAX', true ); }
$fixture = 'JG submission test ' . wp_generate_uuid4();
$email = 'submission-' . wp_generate_uuid4() . '@example.invalid';
$ip = 'submission-' . wp_generate_uuid4();
$pages = get_option( 'jg_seeded_pages' );
$leads = array();
$keys = array();
$mail_count = 0;
$capture_mail = function () use ( &$mail_count ) { ++$mail_count; return true; };
$capture_lead = function ( $id, $post ) use ( &$leads, $fixture ) {
 if ( $post->post_type === 'janogago_lead' && str_starts_with( $post->post_title, $fixture ) ) { $leads[] = $id; }
};
$die_handler = function () { return function () { throw new RuntimeException( 'test-json' ); }; };
add_filter( 'pre_wp_mail', $capture_mail );
add_action( 'wp_after_insert_post', $capture_lead, 10, 2 );
add_filter( 'wp_die_ajax_handler', $die_handler );
$base = array( 'jg_ajax' => '1', 'page_id' => $pages['lv'], 'jg_enquiry_nonce' => wp_create_nonce( 'jg_submit_enquiry' ), 'company' => $fixture, 'name' => 'Test contact', 'email' => $email, 'phone' => '+37120000000', 'location' => 'Riga', 'people' => '10', 'message' => '', 'privacy_consent' => '1', 'service_interest' => 'full-service' );
$request = function ( $overrides = array(), $request_ip = null ) use ( $base, $ip, &$keys ) {
 $_SERVER['REMOTE_ADDR'] = $request_ip ?? $ip;
 $_POST = array_merge( $base, $overrides );
 $keys[] = 'jg_enquiry_ip_' . wp_hash( $_SERVER['REMOTE_ADDR'] );
 $keys[] = 'jg_enquiry_email_' . wp_hash( strtolower( is_string( $_POST['email'] ) ? $_POST['email'] : '' ) );
 $details = array_intersect_key( $_POST, array_flip( array( 'company', 'name', 'email', 'phone', 'location', 'people', 'message' ) ) );
 $details['interest'] = $_POST['service_interest'];
 $keys[] = jg_enquiry_fingerprint( $details );
 ob_start();
 try { jg_submit_enquiry(); } catch ( RuntimeException $e ) { if ( $e->getMessage() !== 'test-json' ) { ob_end_clean(); throw $e; } }
 $result = json_decode( ob_get_clean(), true );
 if ( ! is_array( $result ) || empty( $result['nonce'] ) ) { throw new RuntimeException( 'Expected JSON with a refreshed nonce.' ); }
 return $result;
};
$expect = function ( $result, $status, $field = null ) {
 if ( $result['status'] !== $status || $result['success'] !== ( $status === 'sent' ) || $field && empty( $result['errors'][ $field ] ) ) { throw new RuntimeException( 'Unexpected feedback: ' . wp_json_encode( $result ) ); }
};
try {
 foreach ( array( 'email' => 'tests@tests', 'phone' => 'abc', 'people' => '0', 'location' => '', 'privacy_consent' => '', 'company' => str_repeat( 'a', 121 ) ) as $field => $value ) {
  $expect( $request( array( $field => $value ) ), 'invalid', $field );
 }
 $expect( $request( array( 'email' => '0' ) ), 'invalid', 'email' );
 $expect( $request( array( 'phone' => '0' ) ), 'invalid', 'phone' );
 $expect( $request( array( 'email' => array( 'malformed' ) ) ), 'invalid' );
 $expect( $request( array( 'jg_enquiry_nonce' => 'expired' ) ), 'expired' );
 $honeypot = $request( array( 'jg_website' => 'bot.example' ) );
 $expect( $honeypot, 'sent' );
 if ( $honeypot['lead_created'] ) { throw new RuntimeException( 'Honeypot must not count as an analytics conversion.' ); }
 if ( $mail_count || $leads ) { throw new RuntimeException( 'Invalid requests or honeypot saved/sent an enquiry.' ); }
 $first = $request(); $duplicate = $request();
 $expect( $first, 'sent' ); $expect( $duplicate, 'sent' );
 if ( ! $first['lead_created'] || $duplicate['lead_created'] ) { throw new RuntimeException( 'Only a newly saved lead may count as an analytics conversion.' ); }
 if ( $mail_count !== 1 || count( $leads ) !== 1 ) { throw new RuntimeException( 'An identical retry created a duplicate.' ); }
 $expect( $request( array( 'company' => $fixture . ' second', 'page_id' => $pages['en'] ) ), 'sent' );
 if ( $mail_count !== 2 ) { throw new RuntimeException( 'A second distinct enquiry should be accepted.' ); }
 for ( $n = 3; $n <= 5; ++$n ) { $expect( $request( array( 'company' => $fixture . ' ' . $n ) ), 'sent' ); }
 $expect( $request( array( 'company' => $fixture . ' blocked' ) ), 'rate_limited' );
 $expect( $request( array( 'company' => $fixture . ' email limit' ), $ip . '-other' ), 'rate_limited' );
 $expect( $request( array( 'company' => $fixture . ' IP limit', 'email' => 'other-' . $email ) ), 'rate_limited' );
 $expect( $request(), 'sent' );
 if ( $mail_count !== 5 || count( $leads ) !== 5 ) { throw new RuntimeException( 'Rate-limited requests created extra mail or leads.' ); }
 $pending = array( 'email' => $email . '.pending', 'company' => $fixture . ' pending' );
 $key = jg_enquiry_fingerprint( $pending ); $keys[] = $key;
 set_transient( $key, 'pending', 120 );
 if ( jg_enquiry_guard( $pending ) !== 'busy' ) { throw new RuntimeException( 'In-flight duplicate was not blocked.' ); }
 WP_CLI::success( 'JSON, specific field errors, nonce renewal, honeypot, repeat submissions, duplicate retries and IP/email limits passed. All mail intercepted.' );
} finally {
 remove_filter( 'pre_wp_mail', $capture_mail ); remove_action( 'wp_after_insert_post', $capture_lead, 10 ); remove_filter( 'wp_die_ajax_handler', $die_handler );
 foreach ( $leads as $id ) { wp_delete_post( $id, true ); }
 foreach ( array_unique( $keys ) as $key ) { delete_transient( $key ); }
 $_POST = array();
}
