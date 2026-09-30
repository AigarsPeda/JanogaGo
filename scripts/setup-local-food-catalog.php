<?php
/** Create the food pages and homepage links once on janogago.local. Manage products in WordPress. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_ends_with( wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	exit( 'This setup runs only through WP-CLI on a .local WordPress site.' );
}

$home_pages = get_option( 'jg_seeded_pages', array() );
foreach ( array( 'lv', 'en' ) as $lang ) {
	if ( empty( $home_pages[ $lang ] ) ) { WP_CLI::error( "Missing $lang homepage" ); }
}
$food_pages = get_option( 'jg_food_pages', array() );
foreach ( array( 'lv', 'en' ) as $lang ) {
	if ( ! empty( $food_pages[ $lang ] ) && get_post_status( $food_pages[ $lang ] ) ) { continue; }
	$is_en = $lang === 'en';
	$home_url = get_permalink( $home_pages[ $lang ] );
	$intro = jg_block_group(
		jg_block_heading( $is_en ? 'What can you find in a JāņogaGO machine?' : 'Ko var atrast JāņogaGO automātā?', 1 ) .
		jg_block_paragraph( $is_en ? 'Here are some of the dishes we offer. We put together a selection for each machine.' : 'Te ir daži no ēdieniem, ko piedāvājam. Katram automātam izvēli veidojam atsevišķi.' ),
		'jg-catalog-intro', 'section'
	);
	$listing = jg_block_group( jg_block( 'shortcode', array(), '<div class="wp-block-shortcode">[jg_products]</div>' ), 'jg-catalog-section', 'section' );
	$contact = jg_block_group(
		jg_block_heading( $is_en ? 'A selection made for your team.' : 'Sortiments, kas atbilst jūsu komandai.' ) .
		jg_block_paragraph( $is_en ? 'Tell us what your team would like, and we’ll plan the food selection together.' : 'Pastāstiet par savas komandas vēlmēm, un kopā izplānosim ēdienu piedāvājumu.' ) .
		jg_block_button( $is_en ? 'Request a proposal' : 'Saņemt piedāvājumu', $home_url . '#pieteikties', 'button' ),
		'jg-catalog-contact', 'section'
	);
	$footer = '';
	foreach ( parse_blocks( get_post_field( 'post_content', $home_pages[ $lang ] ) ) as $block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'jg-block-footer-content' ) ) { $footer = serialize_block( $block ); break; }
	}
	$page_id = wp_insert_post( array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $is_en ? 'Food' : 'Ēdieni',
		'post_name' => $is_en ? 'food' : 'edieni', 'post_content' => $intro . $listing . $contact . $footer,
		'meta_input' => array( '_wp_page_template' => 'page-food.php' ),
	), true );
	if ( is_wp_error( $page_id ) ) { WP_CLI::error( $page_id->get_error_message() ); }
	if ( function_exists( 'pll_set_post_language' ) ) { pll_set_post_language( $page_id, $lang ); }
	$food_pages[ $lang ] = $page_id;
	WP_CLI::log( "Added $lang food page $page_id" );
}
if ( function_exists( 'pll_save_post_translations' ) ) { pll_save_post_translations( $food_pages ); }
update_option( 'jg_food_pages', $food_pages );

$backup = array();
$backup_path = '/tmp/janogago-food-home-backup-' . gmdate( 'Ymd-His' ) . '.json';
foreach ( array( 'lv', 'en' ) as $lang ) {
	$id = $home_pages[ $lang ];
	$old = get_post_field( 'post_content', $id, 'raw' );
	if ( str_contains( $old, '[jg_products featured="1"]' ) ) { continue; }
	$blocks = parse_blocks( $old );
	$replaced = false;
	foreach ( $blocks as $index => $block ) {
		if ( ! str_contains( $block['attrs']['className'] ?? '', 'jg-block-food-range' ) ) { continue; }
		$heading = $block['innerBlocks'][0] ?? null;
		$lede = $block['innerBlocks'][1] ?? null;
		if ( ! $heading || ! $lede || $heading['blockName'] !== 'core/heading' || $lede['blockName'] !== 'core/paragraph' ) { WP_CLI::error( "Unexpected $lang food section; nothing replaced" ); }
		$is_en = $lang === 'en';
		$new = jg_block_group(
			serialize_block( $heading ) . serialize_block( $lede ) .
			jg_block( 'shortcode', array(), '<div class="wp-block-shortcode">[jg_products featured="1"]</div>' ) .
			jg_block_group( jg_block_button( $is_en ? 'Explore all food' : 'Apskatīt visus ēdienus', get_permalink( $food_pages[ $lang ] ), 'button' ), 'jg-food-more', 'div' ) .
			jg_block_paragraph( $is_en ? 'The selection at each location may differ.' : 'Sortiments katrā atrašanās vietā var atšķirties.', 'jg-image-note' ),
			'food-range section jg-block-section jg-block-food-range', 'section', 'sortiments'
		);
		$blocks[ $index ] = parse_blocks( $new )[0];
		$replaced = true;
		break;
	}
	if ( ! $replaced ) { WP_CLI::error( "Missing $lang food section" ); }
	$backup[ $lang ] = array( 'id' => $id, 'content' => $old );
	file_put_contents( $backup_path, wp_json_encode( $backup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	$result = wp_update_post( array( 'ID' => $id, 'post_content' => serialize_blocks( $blocks ) ), true );
	if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
	WP_CLI::log( "Updated $lang homepage food section" );
}
if ( $backup ) {
	WP_CLI::log( "Homepage backup: $backup_path" );
}
WP_CLI::success( 'Local food pages setup complete.' );
