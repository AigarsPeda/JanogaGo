<?php
/** Attach a GLB to the existing native hero Image blocks once, on Local only. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_ends_with( wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	exit( 'This setup runs only through WP-CLI on a .local WordPress site.' );
}
$source = $args[0] ?? '';
if ( ! is_readable( $source ) || strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) !== 'glb' ) { WP_CLI::error( 'Supply the corrected GLB file.' ); }
$pages = get_option( 'jg_seeded_pages', array() );
$pending = array();
foreach ( array( 'lv', 'en' ) as $language ) {
	$id = absint( $pages[ $language ] ?? 0 );
	$post = get_post( $id );
	if ( ! $post ) { WP_CLI::error( "Missing $language homepage." ); }
	if ( get_post_meta( $id, '_jg_hero_model_v1', true ) ) { continue; }
	$pending[ $language ] = array( 'id' => $id, 'content' => $post->post_content );
}
if ( ! $pending ) { WP_CLI::success( 'Already applied; later edits and removals are preserved.' ); return; }
$backup = '/tmp/janogago-before-hero-model-' . gmdate( 'Ymd-His' ) . '.json';
if ( ! file_put_contents( $backup, wp_json_encode( $pending, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ) ) { WP_CLI::error( 'Cannot save backup.' ); }
WP_CLI::log( "Backup: $backup" );
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$hash = hash_file( 'sha256', $source );
$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_jg_model_source_sha256', 'meta_value' => $hash, 'fields' => 'ids', 'posts_per_page' => 1 ) );
$model_id = $existing[0] ?? 0;
if ( ! $model_id ) {
	$tmp = wp_tempnam( $source );
	if ( ! $tmp || ! copy( $source, $tmp ) ) { WP_CLI::error( 'Cannot stage the model.' ); }
	$model_id = media_handle_sideload( array( 'name' => 'janoga-smart-fridge.glb', 'tmp_name' => $tmp ), 0, 'Jāņoga Smart Fridge 3D prototype' );
	if ( is_wp_error( $model_id ) ) { if ( file_exists( $tmp ) ) { unlink( $tmp ); } WP_CLI::error( $model_id->get_error_message() ); }
	update_post_meta( $model_id, '_jg_model_source_sha256', $hash );
	update_post_meta( $model_id, '_jg_model_dimensions_mm', array( 'height' => 1930, 'width' => 1105, 'depth' => 760, 'status' => 'Supplier-published, exact customer variant unconfirmed' ) );
	wp_update_post( array( 'ID' => $model_id, 'post_content' => 'Visual prototype. Provisional H1930 × W1105 × D760 mm, source https://vendmaster.co.uk/product/boost-smart-fridge/. Exact customer variant unconfirmed. Photo-based food interior, inferred rear construction. Supplied SF24 side artwork uses equal texel scale to preserve logo proportions.' ) );
}
function jghm_attach( &$blocks, $language, $model_id, &$count, $in_hero = false ) {
	foreach ( $blocks as &$block ) {
		$hero = $in_hero || str_contains( $block['attrs']['className'] ?? '', 'jg-block-hero-layout' );
		if ( $hero && $block['blockName'] === 'core/image' ) {
			$block['attrs']['jgModelId'] = (int) $model_id;
			$block['attrs']['jgModelHint'] = $language === 'en' ? 'Drag to rotate' : 'Pavelciet, lai pagrieztu';
			$block['attrs']['jgModelPause'] = $language === 'en' ? 'Pause rotation' : 'Apturēt rotāciju';
			$block['attrs']['jgModelResume'] = $language === 'en' ? 'Resume rotation' : 'Turpināt rotāciju';
			$count++;
		}
		jghm_attach( $block['innerBlocks'], $language, $model_id, $count, $hero );
	}
}
$updates = array();
foreach ( $pending as $language => $page ) {
	$blocks = parse_blocks( $page['content'] );
	$count = 0;
	jghm_attach( $blocks, $language, $model_id, $count );
	if ( $count !== 1 ) { WP_CLI::error( "Expected exactly one $language hero Image; nothing written to pages." ); }
	$updates[ $language ] = serialize_blocks( $blocks );
}
foreach ( $pending as $language => $page ) {
	$result = wp_update_post( wp_slash( array( 'ID' => $page['id'], 'post_content' => $updates[ $language ] ) ), true );
	if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
	update_post_meta( $page['id'], '_jg_hero_model_v1', 1 );
}
WP_CLI::success( "Model attachment $model_id is selected on both native hero Image blocks." );
