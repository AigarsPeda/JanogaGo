<?php
/** Consent controls and editable WordPress settings; no tag is printed by PHP. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function jg_privacy_copy_defaults( $language ) {
	return $language === 'en' ? array(
		'title' => 'Your cookie choice',
		'text' => 'We use essential cookies to remember your language and cookie choice. With your permission, Google Analytics helps us understand which pages are useful and improve the website. Analytics is optional; you can change your choice at any time.',
		'accept' => 'Accept analytics', 'reject' => 'Reject analytics',
		'settings' => 'Cookie settings', 'privacy' => 'Privacy & cookies', 'close' => 'Close without changing',
	) : array(
		'title' => 'Jūsu sīkdatņu izvēle',
		'text' => 'Nepieciešamās sīkdatnes saglabā valodas un sīkdatņu izvēli. Ar jūsu atļauju Google Analytics palīdz saprast, kuras lapas ir noderīgas, un uzlabot vietni. Analītika nav obligāta; savu izvēli varat mainīt jebkurā laikā.',
		'accept' => 'Atļaut analītiku', 'reject' => 'Noraidīt analītiku',
		'settings' => 'Sīkdatņu iestatījumi', 'privacy' => 'Privātums un sīkdatnes', 'close' => 'Aizvērt, nemainot izvēli',
	);
}

function jg_privacy_options() {
	return get_option( 'jg_privacy', array() );
}

function jg_privacy_copy() {
	$options = jg_privacy_options();
	$lang = jg_lang() === 'en' ? 'en' : 'lv';
	return array_replace( jg_privacy_copy_defaults( $lang ), $options[ $lang ] ?? array() );
}

function jg_privacy_url() {
	$options = jg_privacy_options();
	$id = absint( $options[ 'page_' . ( jg_lang() === 'en' ? 'en' : 'lv' ) ] ?? 0 );
	return $id && get_post_status( $id ) === 'publish' ? get_permalink( $id ) : '';
}

function jg_privacy_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	$id = strtoupper( sanitize_text_field( $input['measurement_id'] ?? '' ) );
	if ( $id && ! preg_match( '/^G-[A-Z0-9]+$/', $id ) ) {
		add_settings_error( 'jg_privacy', 'measurement_id', 'Enter a valid GA4 measurement ID (G-…). Analytics has been disabled.' );
		$id = '';
	}
	$result = array( 'measurement_id' => $id, 'enabled' => ! empty( $input['enabled'] ) && (bool) $id );
	foreach ( array( 'lv', 'en' ) as $lang ) {
		$result[ 'page_' . $lang ] = absint( $input[ 'page_' . $lang ] ?? 0 );
		foreach ( jg_privacy_copy_defaults( $lang ) as $key => $default ) {
			$result[ $lang ][ $key ] = sanitize_textarea_field( $input[ $lang ][ $key ] ?? $default );
		}
	}
	return $result;
}

add_action( 'admin_init', function () {
	register_setting( 'jg_privacy', 'jg_privacy', array( 'sanitize_callback' => 'jg_privacy_sanitize' ) );
} );
add_action( 'admin_menu', function () {
	add_options_page( 'Privacy & Analytics', 'Privacy & Analytics', 'manage_options', 'jg-privacy', 'jg_privacy_settings_page' );
} );

function jg_privacy_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$options = jg_privacy_options();
	echo '<div class="wrap"><h1>Privacy & Analytics</h1><p>GA4 uses basic consent mode: the Google tag loads only after analytics consent. Advertising stays disabled. Local, staging and logged-in visits are excluded. Review the privacy pages and GA4 property settings before enabling collection.</p><form method="post" action="options.php">';
	settings_fields( 'jg_privacy' );
	echo '<p><label for="jg-measurement">GA4 measurement ID</label><br><input id="jg-measurement" name="jg_privacy[measurement_id]" value="' . esc_attr( $options['measurement_id'] ?? '' ) . '" placeholder="G-…" class="regular-text"></p>';
	echo '<p><label><input type="checkbox" name="jg_privacy[enabled]" value="1" ' . checked( ! empty( $options['enabled'] ), true, false ) . '> Enable analytics on the production website after consent</label></p>';
	foreach ( array( 'lv' => 'Latviešu', 'en' => 'English' ) as $lang => $label ) {
		echo '<h2>' . esc_html( $label ) . '</h2><p><label for="jg-page-' . esc_attr( $lang ) . '">Privacy & cookies page</label><br>';
		wp_dropdown_pages( array( 'name' => 'jg_privacy[page_' . $lang . ']', 'id' => 'jg-page-' . $lang, 'selected' => $options[ 'page_' . $lang ] ?? 0, 'show_option_none' => 'Choose a published page', 'option_none_value' => 0, 'echo' => true ) );
		echo '</p>';
		foreach ( jg_privacy_copy_defaults( $lang ) as $key => $default ) {
			echo '<p><label for="jg-copy-' . esc_attr( $lang . '-' . $key ) . '">' . esc_html( ucfirst( $key ) ) . '</label><br><textarea class="large-text" rows="' . ( $key === 'text' ? '3' : '1' ) . '" id="jg-copy-' . esc_attr( $lang . '-' . $key ) . '" name="jg_privacy[' . esc_attr( $lang ) . '][' . esc_attr( $key ) . ']">' . esc_textarea( $options[ $lang ][ $key ] ?? $default ) . '</textarea></p>';
		}
	}
	submit_button();
	echo '</form></div>';
}

add_action( 'wp_enqueue_scripts', function () {
	$version = wp_get_theme()->get( 'Version' );
	$options = jg_privacy_options();
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$production = wp_get_environment_type() === 'production' && ! preg_match( '/(?:\.local|\.test|\.localhost)$/i', $host ) && ! in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true );
	$id = $options['measurement_id'] ?? '';
	$enabled = ! empty( $options['enabled'] ) && preg_match( '/^G-[A-Z0-9]+$/', $id ) && $production && ! is_user_logged_in();
	wp_enqueue_style( 'janogago-privacy', get_template_directory_uri() . '/assets/css/privacy.css', array( 'janogago-business' ), $version );
	wp_enqueue_script( 'janogago-privacy', get_template_directory_uri() . '/assets/js/privacy.js', array(), $version, true );
	wp_add_inline_script( 'janogago-privacy', 'window.jgPrivacy = ' . wp_json_encode( array( 'measurementId' => $enabled ? $id : '', 'language' => jg_lang(), 'version' => 1 ) ) . ';', 'before' );
} );

function jg_privacy_footer_links() {
	$copy = jg_privacy_copy();
	echo '<div class="jg-privacy-links">';
	if ( jg_privacy_url() ) { echo '<a href="' . esc_url( jg_privacy_url() ) . '">' . esc_html( $copy['privacy'] ) . '</a>'; }
	echo '<button type="button" data-cookie-settings hidden aria-controls="jg-cookie-banner" aria-expanded="false">' . esc_html( $copy['settings'] ) . '</button></div>';
}

add_action( 'wp_footer', function () {
	$copy = jg_privacy_copy();
	echo '<section class="jg-cookie-banner" id="jg-cookie-banner" aria-labelledby="jg-cookie-title" hidden><div class="jg-cookie-copy"><h2 id="jg-cookie-title">' . esc_html( $copy['title'] ) . '</h2><p>' . esc_html( $copy['text'] ) . '</p>';
	if ( jg_privacy_url() ) { echo '<a href="' . esc_url( jg_privacy_url() ) . '">' . esc_html( $copy['privacy'] ) . '</a>'; }
	echo '</div><div class="jg-cookie-actions"><button type="button" data-cookie-choice="false">' . esc_html( $copy['reject'] ) . '</button><button type="button" data-cookie-choice="true">' . esc_html( $copy['accept'] ) . '</button><button type="button" class="jg-cookie-close" data-cookie-close hidden>' . esc_html( $copy['close'] ) . '</button></div></section>';
} );
