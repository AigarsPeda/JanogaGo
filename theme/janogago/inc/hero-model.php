<?php
/** An optional Media Library GLB on a native Image block; the image is the fallback. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function jg_model_image_attributes( $args, $name ) {
	if ( $name !== 'core/image' ) { return $args; }
	$args['attributes']['jgModelId'] = array( 'type' => 'number', 'default' => 0 );
	$args['attributes']['jgModelPosterId'] = array( 'type' => 'number', 'default' => 0 );
	foreach ( array( 'jgModelPause', 'jgModelResume', 'jgModelHint' ) as $key ) {
		$args['attributes'][ $key ] = array( 'type' => 'string', 'default' => '' );
	}
	return $args;
}
add_filter( 'register_block_type_args', 'jg_model_image_attributes', 10, 2 );

function jg_model_upload_mime( $mimes ) {
	$mimes['glb'] = 'model/gltf-binary';
	return $mimes;
}
add_filter( 'upload_mimes', 'jg_model_upload_mime' );

function jg_model_check_upload( $data, $file, $filename, $mimes ) {
	$mimes = $mimes ?? get_allowed_mime_types();
	if ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) !== 'glb' || ! isset( $mimes['glb'] ) ) { return $data; }
	$stream = fopen( $file, 'rb' );
	$header = $stream ? fread( $stream, 12 ) : '';
	if ( $stream ) { fclose( $stream ); }
	$valid = strlen( $header ) === 12 && substr( $header, 0, 4 ) === 'glTF';
	if ( $valid ) {
		$fields = unpack( 'Vversion/Vlength', substr( $header, 4 ) );
		$valid = $fields['version'] === 2 && $fields['length'] === filesize( $file );
	}
	return array( 'ext' => $valid ? 'glb' : false, 'type' => $valid ? 'model/gltf-binary' : false, 'proper_filename' => false );
}
add_filter( 'wp_check_filetype_and_ext', 'jg_model_check_upload', 10, 4 );

function jg_model_editor_assets() {
	wp_enqueue_script( 'janogago-model-editor', get_template_directory_uri() . '/assets/js/hero-model-editor.js', array( 'wp-blocks', 'wp-hooks', 'wp-compose', 'wp-element', 'wp-components', 'wp-block-editor' ), wp_get_theme()->get( 'Version' ), true );
}
add_action( 'enqueue_block_editor_assets', 'jg_model_editor_assets' );

function jg_render_model_image( $content, $block ) {
	$id = absint( $block['attrs']['jgModelId'] ?? 0 );
	if ( ! $id || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || get_post_mime_type( $id ) !== 'model/gltf-binary' ) { return $content; }
	$url = wp_get_attachment_url( $id );
	if ( ! $url ) { return $content; }
	$tags = new WP_HTML_Tag_Processor( $content );
	if ( ! $tags->next_tag( 'IMG' ) ) { return $content; }
	$alt = $tags->get_attribute( 'alt' ) ?: get_the_title( $id );
	$attrs = $block['attrs'];
	$poster_id = absint( $attrs['jgModelPosterId'] ?? 0 );
	$poster = $poster_id && wp_attachment_is_image( $poster_id ) && is_file( get_attached_file( $poster_id ) )
		? wp_get_attachment_image_src( $poster_id, 'full' ) : false;
	if ( $poster ) {
		// The native Image block remains the original photo in Gutenberg.
		// Save its rendered attributes so a failed poster/model can restore it.
		foreach ( array( 'src', 'srcset', 'sizes', 'width', 'height', 'class', 'loading', 'fetchpriority' ) as $key ) {
			$tags->set_attribute( 'data-model-photo-' . $key, $tags->get_attribute( $key ) ?? '' );
		}
		$tags->set_attribute( 'src', $poster[0] );
		$tags->set_attribute( 'srcset', wp_get_attachment_image_srcset( $poster_id, 'full' ) ?: '' );
		$tags->set_attribute( 'sizes', wp_get_attachment_image_sizes( $poster_id, 'full' ) ?: '' );
		$tags->set_attribute( 'width', $poster[1] );
		$tags->set_attribute( 'height', $poster[2] );
		$tags->set_attribute( 'loading', 'eager' );
		$tags->set_attribute( 'fetchpriority', 'high' );
		$tags->add_class( 'jg-model-poster-image' );
		$content = $tags->get_updated_html();
	}
	$en = jg_lang() === 'en';
	$pause = $attrs['jgModelPause'] ?: ( $en ? 'Pause rotation' : 'Apturēt rotāciju' );
	$resume = $attrs['jgModelResume'] ?: ( $en ? 'Resume rotation' : 'Turpināt rotāciju' );
	wp_enqueue_script_module( 'janogago-model-viewer', get_template_directory_uri() . '/assets/js/vendor/model-viewer-4.2.0.min.js', array(), '4.2.0' );
	wp_enqueue_script( 'janogago-hero-model', get_template_directory_uri() . '/assets/js/hero-model.js', array(), wp_get_theme()->get( 'Version' ), true );
	return '<div class="jg-hero-model" data-model-state="loading"' . ( $poster ? ' data-model-poster="true"' : '' ) . '>' .
		'<div class="jg-model-fallback">' . $content . '</div>' .
		'<model-viewer class="jg-model-viewer" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" camera-controls disable-zoom disable-pan disable-tap touch-action="pan-y" interaction-prompt="none" camera-orbit="0deg 90deg 4.74m" camera-target="0m .965m 0m" field-of-view="30deg" min-camera-orbit="auto 65deg auto" max-camera-orbit="auto 100deg auto" exposure="1" tone-mapping="neutral" loading="eager" aria-hidden="true"></model-viewer>' .
		'<div class="jg-model-controls" hidden>' .
		'<button type="button" class="jg-model-motion" data-motion="paused" data-pause="' . esc_attr( $pause ) . '" data-resume="' . esc_attr( $resume ) . '" aria-label="' . esc_attr( $resume ) . '" title="' . esc_attr( $resume ) . '">' .
		'<svg class="jg-model-icon-pause" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5v14M15 5v14"/></svg>' .
		'<svg class="jg-model-icon-play" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m8 5 11 7-11 7Z"/></svg>' .
		'</button></div></div>';
}
add_filter( 'render_block_core/image', 'jg_render_model_image', 20, 2 );
