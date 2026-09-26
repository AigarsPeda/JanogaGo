<?php
/** Run with wp eval-file to update notification blocks without replacing other page content. */
$pages = get_option( 'jg_seeded_pages', array() );
$updates = array();
$before = array();

function jg_find_form_status_block( $blocks, $class ) {
	foreach ( $blocks as $block ) {
		if ( in_array( 'jg-enquiry-form', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
			foreach ( $block['innerBlocks'] as $child ) {
				if ( in_array( $class, explode( ' ', $child['attrs']['className'] ?? '' ), true ) ) {
					return serialize_block( $child );
				}
			}
		}
		$found = jg_find_form_status_block( $block['innerBlocks'] ?? array(), $class );
		if ( $found ) {
			return $found;
		}
	}
	return '';
}

foreach ( array( 'lv', 'en' ) as $language ) {
	$id = $pages[ $language ] ?? 0;
	$content = get_post_field( 'post_content', $id, 'raw' );
	if ( ! $id || ! $content ) {
		WP_CLI::error( "Missing $language homepage; no content changed." );
	}

	$before[ $id ] = $content;
	$copy = jg_business_defaults( $language );
	$retired = jg_find_form_status_block( parse_blocks( $content ), 'jg-status-mail_failed' );
	if ( $retired ) { $content = str_replace( $retired, '', $content ); }
	$old_success = $language === 'en'
		? 'Thank you! We have received your enquiry and will contact you to discuss your location and the next steps.'
		: 'Paldies! Esam saņēmuši jūsu pieprasījumu. Sazināsimies ar jums, lai pārrunātu atrašanās vietu un nākamos soļus.';
	$success = jg_find_form_status_block( parse_blocks( $content ), 'jg-status-success' );
	if ( $success && str_contains( $success, $old_success ) ) {
		$content = str_replace( $success, str_replace( $old_success, $copy['form_success_message'], $success ), $content );
	}
	if ( ! str_contains( $content, 'jg-status-dismiss' ) ) {
		$anchor = jg_find_form_status_block( parse_blocks( $content ), 'jg-status-failed' );
		if ( ! $anchor || substr_count( $content, $anchor ) !== 1 ) {
			WP_CLI::error( "Unexpected $language form blocks; no content changed." );
		}
		$addition = jg_block_paragraph( $copy['form_dismiss_label'], 'jg-form-status jg-status-dismiss' );
		$content = str_replace( $anchor, $anchor . $addition, $content );
	}
	if ( $content !== $before[ $id ] ) { $updates[ $id ] = $content; }
	else { unset( $before[ $id ] ); WP_CLI::log( "$language notification copy already updated; preserved." ); }

}
if ( $before ) {
	$snapshot = tempnam( sys_get_temp_dir(), 'janogago-before-notification-copy-' );
	file_put_contents( $snapshot, wp_json_encode( $before, JSON_UNESCAPED_UNICODE ) );
	WP_CLI::log( "Local content snapshot: $snapshot" );
}
foreach ( $updates as $id => $content ) {
	$result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true );
	if ( is_wp_error( $result ) || get_post_field( 'post_content', $id, 'raw' ) !== $content ) {
		WP_CLI::error( 'Could not save the editable notification copy.' );
	}
}
WP_CLI::success( 'Notification copy blocks are available in LV and EN.' );
