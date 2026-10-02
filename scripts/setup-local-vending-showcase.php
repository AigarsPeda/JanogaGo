<?php
/** Add the requested native machine display once. Later edits belong in Gutenberg. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_ends_with( wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	exit( 'This setup runs only through WP-CLI on a .local WordPress site.' );
}

$source_dir = $args[0] ?? '';
$photos = array(
	array( 'exec-24a56d4e-9d43-47f5-a392-b32dcccf8578.png', 'front', 'Jāņoga dzērienu un uzkodu automāts, skats no priekšpuses', 'Jāņoga drinks and snacks vending machine, front view' ),
	array( 'exec-366ea16a-f5ed-4c0a-a310-9b5724fd82fa.png', 'side', 'Jāņoga dzērienu un uzkodu automāts ar skārienekrānu, skats no sāniem', 'Jāņoga drinks and snacks vending machine with a touchscreen, side view' ),
	array( 'exec-96c00f7a-78a1-48e6-952e-e4ddad134b62.png', 'double', 'Jāņoga divdaļīgs ēdienu un dzērienu automāts', 'Jāņoga double food and drinks vending machine' ),
);
$pages = get_option( 'jg_seeded_pages', array() );
$pending = array();
foreach ( array( 'lv', 'en' ) as $language ) {
	$id = absint( $pages[ $language ] ?? 0 );
	$page = get_post( $id );
	if ( ! $page || $page->post_type !== 'page' ) { WP_CLI::error( "Missing $language homepage." ); }
	if ( get_post_meta( $id, '_jg_vending_showcase_v1', true ) ) { continue; }
	if ( str_contains( $page->post_content, 'jg-block-vending-showcase' ) ) { WP_CLI::error( "The $language display already exists without a setup marker; review it manually." ); }
	$count = preg_match_all( '/<!-- wp:group \{[^\n]*"anchor":"klienti"[^\n]* -->/', $page->post_content, $matches );
	if ( $count !== 1 ) { WP_CLI::error( "Unexpected $language homepage structure; nothing changed." ); }
	$anchor = $matches[0][0];
	$pending[ $language ] = array( 'id' => $id, 'content' => $page->post_content, 'anchor' => $anchor );
}
if ( ! $pending ) { WP_CLI::success( 'Already applied; existing edits and deletions are preserved.' ); return; }
foreach ( $photos as $photo ) {
	if ( ! is_readable( trailingslashit( $source_dir ) . $photo[0] ) ) { WP_CLI::error( "Missing source photo: {$photo[0]}" ); }
}
$backup = '/tmp/janogago-before-vending-showcase-' . gmdate( 'Ymd-His' ) . '.json';
if ( ! file_put_contents( $backup, wp_json_encode( $pending, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ) ) { WP_CLI::error( 'Could not save the homepage backup.' ); }
WP_CLI::log( "Homepage backup: $backup" );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$attachments = array();
foreach ( $photos as $photo ) {
	$source = trailingslashit( $source_dir ) . $photo[0];
	$key = hash_file( 'sha256', $source );
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_jg_vending_photo_source', 'meta_value' => $key, 'fields' => 'ids', 'posts_per_page' => 1 ) );
	$id = $existing[0] ?? 0;
	if ( ! $id ) {
		$tmp = wp_tempnam( $source );
		if ( ! $tmp || ! copy( $source, $tmp ) ) { WP_CLI::error( 'Could not prepare the photo for import.' ); }
		$id = media_handle_sideload( array( 'name' => 'janoga-vending-' . $photo[1] . '.png', 'tmp_name' => $tmp ), 0, $photo[2] );
		if ( is_wp_error( $id ) ) {
			if ( file_exists( $tmp ) ) { unlink( $tmp ); }
			WP_CLI::error( $id->get_error_message() );
		}
		update_post_meta( $id, '_jg_vending_photo_source', $key );
		update_post_meta( $id, '_wp_attachment_image_alt', $photo[2] );
	}
	$attachments[ $photo[1] ] = $id;
	WP_CLI::log( "Media attachment $id: {$photo[1]}" );
}
foreach ( $pending as $language => $page ) {
	$is_en = $language === 'en';
	$intro = jg_block_group(
		jg_block_heading( $is_en ? 'Vending machines that fit your space.' : 'Automāti, kas iederas jūsu telpās.' ) .
		jg_block_paragraph( $is_en ? 'Modern vending machines with contactless card and mobile payments. Real-time stock monitoring helps us plan restocking.' : 'Moderni ēdienu automāti, kuros var norēķināties ar karti vai tālruni. Krājumu uzskaite reāllaikā palīdz mums plānot sortimenta papildināšanu.', 'jg-section-lede' ),
		'jg-vending-intro', 'div'
	);
	$images = '';
	foreach ( $photos as $photo ) {
		$images .= jg_block_image( $attachments[ $photo[1] ], $photo[ $is_en ? 3 : 2 ], 'jg-vending-photo jg-vending-photo-' . $photo[1] );
	}
	$section = jg_block_group( $intro . jg_block_group( $images, 'jg-vending-display', 'div' ), 'vending-showcase section jg-block-section jg-block-vending-showcase', 'section', 'automati' );
	$content = str_replace( $page['anchor'], $section . $page['anchor'], $page['content'] );
	$result = wp_update_post( wp_slash( array( 'ID' => $page['id'], 'post_content' => $content ) ), true );
	if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
	update_post_meta( $page['id'], '_jg_vending_showcase_v1', 1 );
	WP_CLI::log( "Added $language display to page {$page['id']}." );
}
WP_CLI::success( 'Machine photos and copy are editable through native Gutenberg blocks.' );
