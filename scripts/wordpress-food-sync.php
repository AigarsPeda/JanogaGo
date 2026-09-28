<?php
/** WP-CLI helper for the selective food catalog release. Keep outside the theme. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit( 1 ); }

function jgfs_fail( $message ) { WP_CLI::error( $message ); }
function jgfs_json( $path, $data ) {
	if ( false === file_put_contents( $path, wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ) ) { jgfs_fail( 'Cannot write release JSON.' ); }
	chmod( $path, 0600 );
}
function jgfs_meta_keys() {
	return array( '_jg_source_asset', '_jg_sync_key', '_jg_name_en', '_jg_description_lv', '_jg_description_en', '_jg_on_home', '_jg_vegan', '_jg_vegetarian', '_jg_gluten_free' );
}
function jgfs_export( $directory ) {
	if ( ! is_dir( $directory ) || ! mkdir( "$directory/media", 0700 ) ) { jgfs_fail( 'Cannot stage media.' ); }
	$release = array( 'schema' => 1, 'source_url' => home_url(), 'pages' => array(), 'terms' => array(), 'products' => array(), 'media' => array(), 'labels' => array() );
	$pages = (array) get_option( 'jg_food_pages', array() );
	foreach ( array( 'lv', 'en' ) as $language ) {
		$post = get_post( absint( $pages[$language] ?? 0 ) );
		if ( ! $post || $post->post_type !== 'page' || $post->post_status !== 'publish' || ! function_exists( 'pll_get_post_language' ) || pll_get_post_language( $post->ID ) !== $language ) { jgfs_fail( "Invalid $language food page." ); }
		$release['pages'][$language] = array( 'title' => $post->post_title, 'slug' => $post->post_name, 'content' => $post->post_content, 'template' => get_page_template_slug( $post->ID ) );
		$release['labels'][$language] = get_option( 'jg_catalog_labels_' . $language, array() );
	}
	$terms = get_terms( array( 'taxonomy' => 'jg_food_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) { jgfs_fail( $terms->get_error_message() ); }
	foreach ( $terms as $term ) { $release['terms'][] = array( 'slug' => $term->slug, 'name' => $term->name, 'en' => get_term_meta( $term->term_id, 'jg_name_en', true ) ); }
	$posts = get_posts( array( 'post_type' => 'jg_product', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ), 'suppress_filters' => true ) );
	foreach ( $posts as $post ) {
		$meta = array();
		foreach ( jgfs_meta_keys() as $key ) { $meta[$key] = get_post_meta( $post->ID, $key, true ); }
		$key = $meta['_jg_sync_key'] ?: ( $meta['_jg_source_asset'] ?: 'jg-' . substr( hash( 'sha256', $post->guid ), 0, 24 ) );
		if ( ! preg_match( '/^(?:[a-f0-9]{8}|jg-[a-f0-9]{24})$/', $key ) ) { jgfs_fail( 'Invalid product sync key: ' . $post->ID ); }
		$meta['_jg_sync_key'] = $key;
		$image_id = get_post_thumbnail_id( $post->ID );
		$image = $image_id ? get_post( $image_id ) : null;
		$original = $image_id ? wp_get_original_image_path( $image_id ) : '';
		if ( ! $image || ! $original || ! is_file( $original ) ) { jgfs_fail( 'Missing product photo: ' . $post->ID ); }
		$filename = 'product-' . $key . '.' . pathinfo( $original, PATHINFO_EXTENSION );
		if ( ! copy( $original, "$directory/media/$filename" ) ) { jgfs_fail( 'Cannot stage photo: ' . $filename ); }
		$release['media'][$key] = array( 'staged' => $filename, 'filename' => basename( $original ), 'sha256' => hash_file( 'sha256', $original ), 'alt' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
		$assigned = get_the_terms( $post->ID, 'jg_food_category' );
		$release['products'][] = array( 'title' => $post->post_title, 'menu_order' => $post->menu_order, 'meta' => $meta, 'categories' => is_array( $assigned ) ? wp_list_pluck( $assigned, 'slug' ) : array() );
	}
	jgfs_json( "$directory/release.json", $release );
	WP_CLI::success( 'Exported ' . count( $release['products'] ) . ' products, ' . count( $release['terms'] ) . ' categories, and two food pages.' );
}
function jgfs_matches( $key, $asset = '' ) {
	$matches = get_posts( array( 'post_type' => 'jg_product', 'post_status' => 'any', 'meta_key' => '_jg_sync_key', 'meta_value' => $key, 'numberposts' => 2, 'suppress_filters' => true ) );
	if ( ! $matches && preg_match( '/^[a-f0-9]{8}$/', $asset ) ) {
		$matches = get_posts( array( 'post_type' => 'jg_product', 'post_status' => 'any', 'meta_key' => '_jg_source_asset', 'meta_value' => $asset, 'numberposts' => 2, 'suppress_filters' => true ) );
	}
	return $matches;
}
function jgfs_import( $directory, $apply ) {
	$path = "$directory/release.json";
	if ( ! is_file( $path ) ) { jgfs_fail( 'Missing release JSON.' ); }
	$release = json_decode( file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
	if ( ( $release['schema'] ?? 0 ) !== 1 || count( $release['pages'] ?? array() ) !== 2 || ! isset( $release['pages']['lv'], $release['pages']['en'] ) ) { jgfs_fail( 'Invalid release schema or language pages.' ); }
	if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) { jgfs_fail( 'Polylang is required.' ); }
	if ( ! post_type_exists( 'jg_product' ) || ! taxonomy_exists( 'jg_food_category' ) ) { jgfs_fail( 'Deploy the catalog theme first.' ); }
	$keys = array();
	foreach ( $release['terms'] as $term ) {
		if ( ! preg_match( '/^[a-z0-9-]+$/', $term['slug'] ) ) { jgfs_fail( 'Invalid category slug.' ); }
	}
	foreach ( $release['products'] as $product ) {
		$key = $product['meta']['_jg_sync_key'] ?? '';
		$asset = $product['meta']['_jg_source_asset'] ?? '';
		if ( ! preg_match( '/^(?:[a-f0-9]{8}|jg-[a-f0-9]{24})$/', $key ) || isset( $keys[$key] ) || ! isset( $release['media'][$key] ) ) { jgfs_fail( 'Invalid or duplicate product key.' ); }
		$keys[$key] = true;
		$media = $release['media'][$key];
		if ( basename( $media['staged'] ) !== $media['staged'] || basename( $media['filename'] ) !== $media['filename'] || ! is_file( "$directory/media/{$media['staged']}" ) || hash_file( 'sha256', "$directory/media/{$media['staged']}" ) !== $media['sha256'] ) { jgfs_fail( 'Photo checksum mismatch: ' . $key ); }
		if ( count( jgfs_matches( $key, $asset ) ) > 1 ) { jgfs_fail( 'Duplicate live product key: ' . $key ); }
		foreach ( $product['categories'] as $slug ) { if ( ! in_array( $slug, array_column( $release['terms'], 'slug' ), true ) ) { jgfs_fail( 'Unknown product category: ' . $slug ); } }
	}
	foreach ( $release['pages'] as $language => $page ) {
		if ( $page['template'] !== 'page-food.php' || ! in_array( $language, array( 'lv', 'en' ), true ) || ! preg_match( '/^[a-z-]+$/', $page['slug'] ) ) { jgfs_fail( 'Unexpected food page.' ); }
		if ( preg_match( '/<!-- wp:image|wp-image-\d+/', $page['content'] ) ) { jgfs_fail( 'Food page has unmapped image blocks.' ); }
		$existing = get_page_by_path( $page['slug'] );
		if ( $existing && ( $existing->post_type !== 'page' || ( pll_get_post_language( $existing->ID ) && pll_get_post_language( $existing->ID ) !== $language ) ) ) { jgfs_fail( 'Food page slug collision: ' . $page['slug'] ); }
	}
	WP_CLI::log( 'Validated ' . count( $release['products'] ) . ' products and 2 language pages.' );
	if ( ! $apply ) { WP_CLI::success( 'Dry run passed; no live content changed.' ); return; }
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	foreach ( $release['terms'] as $term ) {
		$existing = get_term_by( 'slug', $term['slug'], 'jg_food_category' );
		if ( $existing ) { $term_id = $existing->term_id; wp_update_term( $term_id, 'jg_food_category', array( 'name' => $term['name'] ) ); }
		else { $result = wp_insert_term( $term['name'], 'jg_food_category', array( 'slug' => $term['slug'] ) ); if ( is_wp_error( $result ) ) { jgfs_fail( $result->get_error_message() ); } $term_id = $result['term_id']; }
		update_term_meta( $term_id, 'jg_name_en', $term['en'] );
	}
	$live_images = array();
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1 ) ) as $image ) {
		$original = wp_get_original_image_path( $image->ID );
		if ( $original && is_file( $original ) ) { $live_images[hash_file( 'sha256', $original )][] = $image->ID; }
	}
	foreach ( $release['products'] as $product ) {
		$key = $product['meta']['_jg_sync_key'];
		$asset = $product['meta']['_jg_source_asset'];
		$media = $release['media'][$key];
		$existing_image = $live_images[$media['sha256']] ?? array();
		if ( count( $existing_image ) > 1 ) { jgfs_fail( 'Ambiguous existing photo: ' . $key ); }
		$image_id = $existing_image[0] ?? 0;
		if ( ! $image_id ) {
			$tmp = wp_tempnam( $media['filename'] );
			if ( ! $tmp || ! copy( "$directory/media/{$media['staged']}", $tmp ) ) { jgfs_fail( 'Cannot prepare image: ' . $key ); }
			$image_id = media_handle_sideload( array( 'name' => $media['filename'], 'tmp_name' => $tmp ), 0, $product['title'] );
			if ( is_wp_error( $image_id ) ) { @unlink( $tmp ); jgfs_fail( $image_id->get_error_message() ); }
			$live_images[$media['sha256']] = array( $image_id );
		}
		if ( $asset ) { update_post_meta( $image_id, '_jg_source_asset', $asset ); }
		update_post_meta( $image_id, '_wp_attachment_image_alt', $media['alt'] );
		$matches = jgfs_matches( $key, $asset );
		$post_data = array( 'post_type' => 'jg_product', 'post_status' => 'publish', 'post_title' => $product['title'], 'menu_order' => (int) $product['menu_order'] );
		if ( $matches ) { $post_data['ID'] = $matches[0]->ID; $id = wp_update_post( wp_slash( $post_data ), true ); }
		else { $id = wp_insert_post( wp_slash( $post_data ), true ); }
		if ( is_wp_error( $id ) ) { jgfs_fail( $id->get_error_message() ); }
		foreach ( jgfs_meta_keys() as $key ) { update_post_meta( $id, $key, wp_slash( $product['meta'][$key] ?? '' ) ); }
		set_post_thumbnail( $id, $image_id );
		$result = wp_set_object_terms( $id, $product['categories'], 'jg_food_category' );
		if ( is_wp_error( $result ) ) { jgfs_fail( $result->get_error_message() ); }
		WP_CLI::log( "Product $key → $id; image $image_id" );
	}
	$food_pages = array();
	foreach ( $release['pages'] as $language => $page ) {
		$existing = get_page_by_path( $page['slug'] );
		$content = str_replace( rtrim( $release['source_url'], '/' ), rtrim( home_url(), '/' ), $page['content'] );
		$post_data = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page['title'], 'post_name' => $page['slug'], 'post_content' => $content );
		if ( $existing ) { $post_data['ID'] = $existing->ID; $id = wp_update_post( wp_slash( $post_data ), true ); }
		else { $id = wp_insert_post( wp_slash( $post_data ), true ); }
		if ( is_wp_error( $id ) ) { jgfs_fail( $id->get_error_message() ); }
		update_post_meta( $id, '_wp_page_template', 'page-food.php' );
		pll_set_post_language( $id, $language );
		$food_pages[$language] = $id;
		if ( is_array( $release['labels'][$language] ) ) { update_option( 'jg_catalog_labels_' . $language, $release['labels'][$language], false ); }
	}
	pll_save_post_translations( $food_pages );
	update_option( 'jg_food_pages', $food_pages, false );
	WP_CLI::success( 'Food catalog imported: ' . wp_json_encode( $food_pages ) );
}

if ( ( $args[0] ?? '' ) === 'export' ) { jgfs_export( $args[1] ?? '' ); }
elseif ( in_array( $args[0] ?? '', array( 'check', 'apply' ), true ) ) { jgfs_import( $args[1] ?? '', $args[0] === 'apply' ); }
else { jgfs_fail( 'Usage: wp eval-file wordpress-food-sync.php export|check|apply DIRECTORY' ); }
