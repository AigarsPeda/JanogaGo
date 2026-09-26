<?php
/** WP-CLI helper for sync-content-to-droplet.sh; never install in the theme. */

function jgcs_page( $pages, $language ) {
	$id = absint( $pages[$language] ?? 0 );
	$post = get_post( $id );
	if ( ! $post || $post->post_type !== 'page' || $post->post_status !== 'publish'
		|| ! function_exists( 'pll_get_post_language' ) || pll_get_post_language( $id ) !== $language ) {
		WP_CLI::error( "Missing or unexpected $language homepage in jg_seeded_pages." );
	}
	return $post;
}

function jgcs_image_ids( $blocks ) {
	$ids = array();
	foreach ( $blocks as $block ) {
		if ( $block['blockName'] === 'core/image' && ! empty( $block['attrs']['id'] ) ) {
			$ids[] = absint( $block['attrs']['id'] );
		}
		$ids = array_merge( $ids, jgcs_image_ids( $block['innerBlocks'] ) );
	}
	return array_unique( $ids );
}

function jgcs_write_json( $path, $data ) {
	$json = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	if ( $json === false || file_put_contents( $path, $json ) === false ) { WP_CLI::error( 'Cannot write release/backup JSON.' ); }
	chmod( $path, 0600 );
}

function jgcs_export( $package, $languages, $pages ) {
	$release = array( 'schema' => 1, 'local_url' => home_url(), 'pages' => array(), 'media' => array() );
	$ids = array();
	foreach ( $languages as $language ) {
		$post = jgcs_page( $pages, $language );
		$release['pages'][$language] = array( 'content' => $post->post_content, 'url' => get_permalink( $post ) );
		$ids = array_merge( $ids, jgcs_image_ids( parse_blocks( $post->post_content ) ) );
	}
	if ( ! mkdir( "$package/media", 0700 ) ) { WP_CLI::error( 'Cannot create media staging directory.' ); }
	foreach ( array_unique( $ids ) as $id ) {
		$post = get_post( $id );
		$original = wp_get_original_image_path( $id );
		$metadata = wp_get_attachment_metadata( $id );
		if ( ! $post || $post->post_status !== 'inherit' || ! $original || ! is_file( $original ) || ! is_array( $metadata ) ) {
			WP_CLI::error( "Missing Media Library image $id." );
		}
		$staged = $id . '-' . basename( $original );
		if ( ! copy( $original, "$package/media/$staged" ) ) { WP_CLI::error( "Cannot stage image $id." ); }
		$release['media'][$id] = array(
			'filename' => basename( $original ), 'staged' => $staged, 'sha256' => hash_file( 'sha256', $original ),
			'metadata' => $metadata, 'url' => wp_get_attachment_url( $id ), 'title' => $post->post_title,
			'caption' => $post->post_excerpt, 'description' => $post->post_content,
			'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
		);
	}
	jgcs_write_json( "$package/release.json", $release );
	WP_CLI::success( 'Exported ' . count( $release['pages'] ) . ' pages and ' . count( $release['media'] ) . ' referenced images.' );
}

function jgcs_replace( $value, $urls, $map ) {
	if ( is_array( $value ) ) {
		foreach ( $value as &$item ) { $item = jgcs_replace( $item, $urls, $map ); }
		return $value;
	}
	if ( ! is_string( $value ) ) { return $value; }
	return preg_replace_callback( '/\bwp-image-(\d+)\b/', function ( $match ) use ( $map ) {
		return 'wp-image-' . ( $map[$match[1]] ?? $match[1] );
	}, strtr( $value, $urls ) );
}

function jgcs_blocks( $blocks, $urls, $map ) {
	foreach ( $blocks as &$block ) {
		$block['attrs'] = jgcs_replace( $block['attrs'], $urls, $map );
		if ( $block['blockName'] === 'core/image' && isset( $block['attrs']['id'] ) ) {
			$local_id = $block['attrs']['id'];
			if ( ! isset( $map[$local_id] ) ) { WP_CLI::error( 'Unmapped image block.' ); }
			$block['attrs']['id'] = $map[$local_id];
		}
		$block['innerHTML'] = jgcs_replace( $block['innerHTML'], $urls, $map );
		$block['innerContent'] = jgcs_replace( $block['innerContent'], $urls, $map );
		$block['innerBlocks'] = jgcs_blocks( $block['innerBlocks'], $urls, $map );
	}
	return $blocks;
}

function jgcs_import( $package, $apply, $backup, $pages ) {
	$release = json_decode( file_get_contents( "$package/release.json" ), true, 512, JSON_THROW_ON_ERROR );
	if ( ( $release['schema'] ?? 0 ) !== 1 || empty( $release['pages'] ) ) { WP_CLI::error( 'Unsupported release package.' ); }
	$before = array( 'pages' => array(), 'media_alt' => array(), 'new_media' => array(), 'migration' => get_option( 'jg_business_content_v1' ) );
	$page_ids = array();
	$urls = array();
	foreach ( $release['pages'] as $language => $page ) {
		if ( ! in_array( $language, array( 'lv', 'en' ), true ) ) { WP_CLI::error( 'Unexpected release language.' ); }
		$post = jgcs_page( $pages, $language );
		$page_ids[$language] = $post->ID;
		$urls[$page['url']] = get_permalink( $post );
		$before['pages'][$post->ID] = array( 'content' => $post->post_content, 'business_marker' => get_post_meta( $post->ID, '_jg_business_content_v1', true ), 'seed_marker' => get_post_meta( $post->ID, '_jg_gutenberg_seeded', true ) );
		foreach ( jgcs_image_ids( parse_blocks( $page['content'] ) ) as $id ) {
			if ( ! isset( $release['media'][$id] ) ) { WP_CLI::error( 'Page refers to an image missing from the package.' ); }
		}
	}
	// Match original file contents, including photos WordPress renamed or scaled.
	$live_images = array();
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1 ) ) as $post ) {
		$original = wp_get_original_image_path( $post->ID );
		if ( $original && is_file( $original ) ) { $live_images[hash_file( 'sha256', $original )][] = $post->ID; }
	}
	$map = array();
	$new_hashes = array();
	foreach ( $release['media'] as $local_id => $media ) {
		if ( basename( $media['staged'] ) !== $media['staged'] || basename( $media['filename'] ) !== $media['filename'] ) { WP_CLI::error( 'Invalid media filename.' ); }
		$file = "$package/media/" . $media['staged'];
		if ( ! is_file( $file ) || hash_file( 'sha256', $file ) !== $media['sha256'] ) { WP_CLI::error( 'Staged media checksum mismatch.' ); }
		$matches = $live_images[$media['sha256']] ?? array();
		if ( count( $matches ) > 1 ) { WP_CLI::error( 'Ambiguous duplicate Media Library records for ' . $media['filename'] . '; resolve before syncing.' ); }
		$map[$local_id] = $matches[0] ?? 0;
		if ( ! $map[$local_id] ) { $new_hashes[$media['sha256']] = true; }
		if ( $map[$local_id] ) {
			if ( ! is_file( get_attached_file( $map[$local_id] ) ) ) { WP_CLI::error( 'Live attachment file is missing.' ); }
			$before['media_alt'][$map[$local_id]] = get_post_meta( $map[$local_id], '_wp_attachment_image_alt', true );
		}
		WP_CLI::log( ( $map[$local_id] ? 'Reuse' : 'Import' ) . ': ' . $media['filename'] );
	}
	if ( ! $apply ) {
		WP_CLI::success( 'Validated ' . count( $page_ids ) . ' homepages; ' . count( array_filter( $map ) ) . ' images reused, ' . count( $new_hashes ) . ' new imports planned.' );
		return;
	}
	if ( ! is_dir( $backup ) || file_exists( "$backup/pages-before.json" ) ) { WP_CLI::error( 'Backup directory is missing or already used.' ); }
	jgcs_write_json( "$backup/pages-before.json", $before );
	copy( "$package/release.json", "$backup/release.json" );
	chmod( "$backup/release.json", 0600 );
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	foreach ( $release['media'] as $local_id => $media ) {
		if ( ! $map[$local_id] && ! empty( $live_images[$media['sha256']] ) ) { $map[$local_id] = $live_images[$media['sha256']][0]; }
		if ( ! $map[$local_id] ) {
			$tmp = wp_tempnam( $media['filename'] );
			if ( ! $tmp || ! copy( "$package/media/" . $media['staged'], $tmp ) ) { WP_CLI::error( 'Cannot prepare image import.' ); }
			$id = media_handle_sideload( array( 'name' => $media['filename'], 'tmp_name' => $tmp ), 0, null, wp_slash( array( 'post_title' => $media['title'], 'post_excerpt' => $media['caption'], 'post_content' => $media['description'] ) ) );
			if ( is_wp_error( $id ) ) { @unlink( $tmp ); WP_CLI::error( $id->get_error_message() ); }
			$map[$local_id] = $id;
			$live_images[$media['sha256']] = array( $id );
			$before['new_media'][] = $id;
			jgcs_write_json( "$backup/pages-before.json", $before );
		}
		$id = $map[$local_id];
		update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $media['alt'] ) );
		$remote_url = wp_get_attachment_url( $id );
		$urls[$media['url']] = $remote_url;
		$local_dir = trailingslashit( dirname( $media['url'] ) );
		$remote_dir = trailingslashit( dirname( $remote_url ) );
		$remote_meta = wp_get_attachment_metadata( $id );
		foreach ( $media['metadata']['sizes'] ?? array() as $size => $details ) {
			$urls[$local_dir . $details['file']] = ! empty( $remote_meta['sizes'][$size]['file'] )
				? $remote_dir . $remote_meta['sizes'][$size]['file'] : $remote_url;
		}
		if ( ! empty( $media['metadata']['original_image'] ) ) {
			$urls[$local_dir . $media['metadata']['original_image']] = wp_get_original_image_url( $id );
		}
	}
	$urls[rtrim( $release['local_url'], '/' )] = rtrim( home_url(), '/' );
	foreach ( $release['pages'] as $language => $page ) {
		$id = $page_ids[$language];
		$html = serialize_blocks( jgcs_blocks( parse_blocks( $page['content'] ), $urls, $map ) );
		if ( home_url() !== $release['local_url'] && strpos( $html, rtrim( $release['local_url'], '/' ) ) !== false ) { WP_CLI::error( 'Local URL left in release content.' ); }
		if ( get_post_field( 'post_content', $id, 'raw' ) !== $html ) {
			$result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $html ) ), true );
			if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
		}
		update_post_meta( $id, '_jg_business_content_v1', 1 );
		update_post_meta( $id, '_jg_gutenberg_seeded', 1 );
		if ( get_post_field( 'post_content', $id, 'raw' ) !== $html ) { WP_CLI::error( 'Saved content verification failed.' ); }
		WP_CLI::log( "$language page $id sha256 " . hash( 'sha256', $html ) );
	}
	// A single-language sync must not suppress migration of the other page.
	if ( get_post_meta( $pages['lv'] ?? 0, '_jg_business_content_v1', true ) && get_post_meta( $pages['en'] ?? 0, '_jg_business_content_v1', true ) ) {
		update_option( 'jg_business_content_v1', 1, false );
	}
	WP_CLI::success( 'Selected homepages saved and verified. Backup: ' . $backup );
}

if ( isset( $args[0] ) ) {
	$pages = get_option( 'jg_seeded_pages', array() );
	if ( $args[0] === 'export' ) {
		jgcs_export( $args[1], explode( ',', $args[2] ), $pages );
	} elseif ( in_array( $args[0], array( 'check', 'apply' ), true ) ) {
		jgcs_import( $args[1], $args[0] === 'apply', $args[2] ?? '', $pages );
	} else {
		WP_CLI::error( 'Unknown content-sync helper command.' );
	}
}
