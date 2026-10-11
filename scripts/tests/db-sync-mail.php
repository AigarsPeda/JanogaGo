<?php
/** Local-only SQL integration checks using temporary tables, without sending mail. */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	WP_CLI::error( 'Mail-preservation tests may run only on a .local WordPress site.' );
}
define( 'JGDS_LIBRARY_ONLY', true );
require dirname( __DIR__ ) . '/wordpress-db-sync.php';
function jgds_assert( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
global $wpdb;
$table = $wpdb->prefix . 'jgds_test_options';
$directory = sys_get_temp_dir() . '/janogago-db-sync-test-' . wp_generate_uuid4();
mkdir( $directory, 0700 );
$before = hash( 'sha256', serialize( $wpdb->get_results( "SELECT * FROM `$wpdb->options` ORDER BY option_name", ARRAY_A ) ) );
$rules = jgds_mail_option_rules();
$protected = $rules['exact'];
$protected[] = 'wp_mail_smtp';
foreach ( $rules['prefix'] as $prefix ) { $protected[] = $prefix . '_fixture'; }
try {
	jgds_assert( $wpdb->query( "CREATE TEMPORARY TABLE `$table` LIKE `$wpdb->options`" ) !== false, 'Cannot create temporary options table.' );
	foreach ( array( array( 'http://janogago.local', 'https://janogago.lv' ), array( 'https://janogago.lv', 'http://janogago.local' ) ) as $direction ) {
		$wpdb->query( "DELETE FROM `$table`" );
		$source = array();
		$destination = array();
		foreach ( $protected as $name ) {
			$row = array( 'option_name' => $name, 'option_value' => 'destination-secret-' . $direction[0], 'autoload' => 'off' );
			$source_value = 'source-secret-' . $direction[0];
			if ( $name === 'wp_mail_smtp' ) {
				$credentials = array( 'client_id', 'client_secret', 'access_token', 'refresh_token' );
				$row['option_value'] = serialize( array( 'gmail' => array_fill_keys( $credentials, $row['option_value'] ) ) );
				$source_value = serialize( array( 'gmail' => array_fill_keys( $credentials, $source_value ) ) );
			}
			$destination[$name] = $row;
			$wpdb->insert( $table, $row );
			$source[] = array( 'option_name' => $name, 'option_value' => $source_value, 'autoload' => 'on' );
		}
		$recipients = array( 'admin_email', 'jg_enquiry_recipient' );
		foreach ( $recipients as $name ) {
			$wpdb->insert( $table, array( 'option_name' => $name, 'option_value' => 'destination@example.test', 'autoload' => 'off' ) );
			$source[] = array( 'option_name' => $name, 'option_value' => 'source@example.test', 'autoload' => 'on' );
		}
		$wpdb->insert( $table, array( 'option_name' => 'destination_only', 'option_value' => 'remove', 'autoload' => 'yes' ) );
		$payload = array( 'url' => $direction[0] . '/ēdieni/', 'text' => "Quotes ' and \\ and \n" );
		$source[] = array( 'option_name' => 'content_setting', 'option_value' => serialize( $payload ), 'autoload' => 'auto' );
		$source[] = array( 'option_name' => 'empty_setting', 'option_value' => '', 'autoload' => 'off' );
		$sql = jgds_options_sql( $table, $source, new \WP_CLI\SearchReplacer( $direction[0], $direction[1], true ) );
		jgds_assert( ! str_contains( $sql, bin2hex( 'source-secret-' ) ), 'Source mail secrets entered the transfer.' );
		foreach ( explode( ";\n", trim( $sql ) ) as $statement ) {
			if ( trim( $statement ) !== '' ) { jgds_assert( $wpdb->query( $statement ) !== false, 'SQL merge failed.' ); }
		}
		$actual = array();
		foreach ( $wpdb->get_results( "SELECT option_name, option_value, autoload FROM `$table`", ARRAY_A ) as $row ) { $actual[$row['option_name']] = $row; }
		foreach ( $destination as $name => $row ) { jgds_assert( $actual[$name] === $row, 'Destination mail option or autoload changed.' ); }
		foreach ( $recipients as $name ) { jgds_assert( $actual[$name]['option_value'] === 'source@example.test' && $actual[$name]['autoload'] === 'on', 'Recipient setting did not synchronize.' ); }
		jgds_assert( ! isset( $actual['destination_only'] ), 'Stale content setting survived the replacement.' );
		$payload['url'] = $direction[1] . '/ēdieni/';
		jgds_assert( $actual['content_setting']['option_value'] === serialize( $payload ), 'Serialized URL or text was damaged.' );
		jgds_assert( $actual['content_setting']['autoload'] === 'auto' && $actual['empty_setting']['option_value'] === '', 'Autoload or empty value was damaged.' );
		// An unconfigured destination must not acquire the source's connection.
		$wpdb->query( "DELETE FROM `$table`" );
		foreach ( explode( ";\n", trim( $sql ) ) as $statement ) { if ( trim( $statement ) !== '' ) { $wpdb->query( $statement ); } }
		jgds_assert( (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$table` WHERE " . jgds_protected_options_sql() ) === 0, 'Source mail settings appeared on an unconfigured destination.' );
		foreach ( $recipients as $name ) { jgds_assert( $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM `$table` WHERE option_name = %s", $name ) ) === 'source@example.test', 'Recipient setting was not copied to an unconfigured destination.' ); }
	}
	$tables = array( 'jg_options', 'jg_posts', 'jg_wpmailsmtp_emails_queue', 'jg_wp_mail_smtp_logs', 'jg_actionscheduler_actions', 'other_options' );
	jgds_assert( jgds_excluded_tables( 'jg_', $tables ) === array( 'jg_options', 'jg_wpmailsmtp_emails_queue', 'jg_wp_mail_smtp_logs', 'jg_actionscheduler_actions' ), 'Table exclusions did not respect the site prefix.' );
	$tables = $wpdb->get_col( 'SHOW TABLES' );
	$excluded = jgds_excluded_tables( $wpdb->prefix, $tables );
	$included = array_filter( array_diff( $tables, $excluded ), function ( $name ) use ( $wpdb ) { return strpos( $name, $wpdb->prefix ) === 0; } );
	// WP-CLI ignores exclude_tables when tables is supplied. Check the actual
	// push whitelist and pull exclusion flags separately, using read-only exports.
	foreach ( array( 'push' => '--tables=' . escapeshellarg( implode( ',', $included ) ), 'pull' => '--exclude_tables=' . escapeshellarg( implode( ',', $excluded ) ) ) as $direction => $flags ) {
		$file = "$directory/$direction.sql";
		$result = WP_CLI::runcommand( 'db export ' . escapeshellarg( $file ) . ' ' . $flags . ' --quiet', array( 'return' => 'all', 'exit_error' => false ) );
		jgds_assert( $result->return_code === 0, 'Read-only database export failed.' );
		preg_match_all( '/CREATE TABLE `([^`]+)`/', file_get_contents( $file ), $matches );
		jgds_assert( in_array( $wpdb->prefix . 'posts', $matches[1], true ), 'Ordinary content tables are missing from the export.' );
		jgds_assert( ! array_intersect( $excluded, $matches[1] ), 'Protected options or mail/runtime tables entered the export.' );
		unlink( $file );
	}
	jgds_assert( $before === hash( 'sha256', serialize( $wpdb->get_results( "SELECT * FROM `$wpdb->options` ORDER BY option_name", ARRAY_A ) ) ), 'Real WordPress options changed during tests.' );
	WP_CLI::success( 'Both directions synchronize recipients, preserve SMTP/OAuth credentials, exclude source secrets and queues, and correctly replace serialized content settings. Real site options are unchanged.' );
} finally {
	$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS `$table`" );
	foreach ( glob( "$directory/*.sql" ) as $file ) { unlink( $file ); }
	rmdir( $directory );
}
