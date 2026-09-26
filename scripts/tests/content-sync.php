<?php
/** Run with Local WP-CLI --skip-themes eval-file; refuses production hosts. */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	WP_CLI::error( 'Content-sync integration tests may run only on a .local WordPress site.' );
}
require dirname( __DIR__ ) . '/wordpress-content-sync.php';
function jgcs_assert( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
$root = sys_get_temp_dir() . '/janogago-content-test-' . wp_generate_uuid4();
mkdir( $root, 0700 );
mkdir( "$root/media", 0700 );
$pages = array();
$media_ids = array();
$migration = get_option( 'jg_business_content_v1' );
$home_hashes = array();
foreach ( get_option( 'jg_seeded_pages' ) as $id ) { $home_hashes[$id] = hash( 'sha256', get_post_field( 'post_content', $id, 'raw' ) ); }
$failure = null;
try {
	foreach ( array( 'lv', 'en' ) as $language ) {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => "Content-sync test $language", 'post_content' => 'Before test' ), true );
		jgcs_assert( ! is_wp_error( $id ), 'Cannot create test page.' );
		$pages[$language] = $id;
		pll_set_post_language( $id, $language );
	}
	// A small valid PNG fixture, staged outside the theme and Media Library.
	$png = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a0f8AAAAASUVORK5CYII=' );
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image/png', 'post_status' => 'inherit', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) )[0];
	$existing_path = get_attached_file( $existing->ID );
	$existing_hash = hash_file( 'sha256', $existing_path );
	// Deliberately collide with an existing filename. WordPress must create a new name.
	$name = basename( $existing_path );
	file_put_contents( "$root/media/fixture.png", $png );
	$url = 'http://source.test/wp-content/uploads/2026/09/' . $name;
	$content = serialize_blocks( array( array(
		'blockName' => 'core/group', 'attrs' => array(), 'innerHTML' => '<div class="wp-block-group"></div>',
		'innerContent' => array( '<div class="wp-block-group">', null, '</div>' ),
		'innerBlocks' => array( array( 'blockName' => 'core/image', 'attrs' => array( 'id' => 900000 ), 'innerBlocks' => array(),
			'innerHTML' => '<figure class="wp-block-image"><img src="' . $url . '" alt="Fixture" class="wp-image-900000"/></figure>',
			'innerContent' => array( '<figure class="wp-block-image"><img src="' . $url . '" alt="Fixture" class="wp-image-900000"/></figure>' ),
		) ),
	) ) );
	$release = array( 'schema' => 1, 'local_url' => 'http://source.test', 'pages' => array(), 'media' => array( 900000 => array(
		'filename' => $name, 'staged' => 'fixture.png', 'sha256' => hash( 'sha256', $png ),
		'url' => $url, 'metadata' => array(), 'title' => "Fixture's title", 'caption' => 'Fixture caption',
		'description' => 'Fixture description', 'alt' => 'Fixture alt',
	) ) );
	foreach ( $pages as $language => $id ) { $release['pages'][$language] = array( 'content' => $content, 'url' => "http://source.test/$language/" ); }
	jgcs_write_json( "$root/release.json", $release );
	$attachments_before = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids' ) );
	jgcs_import( $root, false, '', $pages );
	jgcs_assert( get_post_field( 'post_content', $pages['lv'], 'raw' ) === 'Before test', 'Dry run changed a page.' );
	jgcs_assert( $attachments_before === get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids' ) ), 'Dry run created media.' );
	mkdir( "$root/backup1", 0700 );
	jgcs_import( $root, true, "$root/backup1", $pages );
	$state = json_decode( file_get_contents( "$root/backup1/pages-before.json" ), true );
	$media_ids = $state['new_media'];
	jgcs_assert( count( $media_ids ) === 1, 'Expected one native Media Library import.' );
	$attachment = get_post( $media_ids[0] );
	jgcs_assert( $attachment->post_title === "Fixture's title" && $attachment->post_content === 'Fixture description', 'Media text was not preserved.' );
	$metadata = wp_get_attachment_metadata( $attachment->ID );
	jgcs_assert( is_file( get_attached_file( $attachment->ID ) ) && ! empty( $metadata['width'] ), 'Native image file or metadata is missing.' );
	jgcs_assert( basename( get_attached_file( $attachment->ID ) ) !== $name, 'Import did not avoid the existing filename.' );
	foreach ( $pages as $language => $id ) {
		$post = get_post( $id );
		$blocks = parse_blocks( $post->post_content );
		jgcs_assert( $blocks[0]['innerBlocks'][0]['attrs']['id'] === $attachment->ID, 'Nested image ID was not mapped.' );
		jgcs_assert( strpos( $post->post_content, 'source.test' ) === false && strpos( $post->post_content, 'wp-image-' . $attachment->ID ) !== false, 'Image URL or HTML class was not mapped.' );
		jgcs_assert( $post->post_title === "Content-sync test $language" && $post->post_status === 'publish', 'Existing page fields were replaced.' );
	}
	$revisions_before = count( wp_get_post_revisions( $pages['lv'] ) );
	mkdir( "$root/backup2", 0700 );
	jgcs_import( $root, true, "$root/backup2", $pages );
	$state2 = json_decode( file_get_contents( "$root/backup2/pages-before.json" ), true );
	jgcs_assert( ! $state2['new_media'], 'Repeat run created duplicate attachments.' );
	jgcs_assert( count( wp_get_post_revisions( $pages['lv'] ) ) === $revisions_before, 'Unchanged content created another revision.' );
	jgcs_assert( hash_file( 'sha256', $existing_path ) === $existing_hash, 'Existing image file was overwritten.' );
	foreach ( $home_hashes as $id => $hash ) { jgcs_assert( hash( 'sha256', get_post_field( 'post_content', $id, 'raw' ) ) === $hash, 'An unrelated homepage changed.' ); }
} catch ( Throwable $error ) {
	$failure = $error->getMessage();
} finally {
	// Recover IDs even when an assertion fails after a successful import.
	if ( file_exists( "$root/backup1/pages-before.json" ) ) {
		$state = json_decode( file_get_contents( "$root/backup1/pages-before.json" ), true );
		$media_ids = $state['new_media'] ?? $media_ids;
	}
	foreach ( $pages as $id ) { wp_delete_post( $id, true ); }
	foreach ( $media_ids as $id ) { wp_delete_attachment( $id, true ); }
	if ( $migration === false ) { delete_option( 'jg_business_content_v1' ); } else { update_option( 'jg_business_content_v1', $migration ); }
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $files as $file ) { $file->isDir() ? rmdir( $file->getPathname() ) : unlink( $file->getPathname() ); }
	rmdir( $root );
}
if ( $failure ) { WP_CLI::error( $failure ); }
WP_CLI::success( 'Content sync: dry run, native media import, nested ID/URL mapping, repeat run, backups and unrelated-page preservation passed; fixtures removed.' );
