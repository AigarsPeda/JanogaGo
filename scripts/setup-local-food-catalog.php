<?php
/** Run once with WP-CLI against janogago.local after copying the theme. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! str_ends_with( wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) {
	exit( 'This setup runs only through WP-CLI on a .local WordPress site.' );
}
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$source_dir = '/Users/aigarspeda/Desktop/JanogaGo-doc/food-normal-cut-out';
$products = array(
	array( '05c5d0b7', 'Gaļa ar biezeni', 'Meat with mash', 'mains', true ),
	array( 'bbc6dfa4', 'Cēzara salāti', 'Caesar salad', 'salads', true ),
	array( '907939c8', 'Svaigais sendvičs', 'Fresh sandwich', 'sandwiches', true ),
	array( '92db92a3', 'Ogu deserts', 'Berry dessert', 'desserts', true ),
	array( '766dfb41', 'Makaronu salāti', 'Pasta salad', 'salads', false ),
	array( '5ba0025b', 'Panēta fileja', 'Breaded fillet', 'mains', false ),
	array( '6e90767e', 'Sacepums', 'Baked dish', 'mains', false ),
	array( '84b68ed2', 'Sendvičs', 'Sandwich', 'sandwiches', false ),
	array( 'e9c6449c', 'Sendvičs ar tomātu', 'Tomato sandwich', 'sandwiches', false ),
	array( 'bd21672b', 'Pankūkas', 'Pancakes', 'desserts', false ),
	array( 'cb05f7c1', 'Biezpiena plācenīši', 'Cheese pancakes', 'desserts', false ),
);
$categories = array(
	'mains' => array( 'Pamatēdieni', 'Main courses' ),
	'salads' => array( 'Salāti', 'Salads' ),
	'sandwiches' => array( 'Sendviči', 'Sandwiches' ),
	'snacks' => array( 'Uzkodas', 'Snacks' ),
	'desserts' => array( 'Deserti', 'Desserts' ),
	'drinks' => array( 'Dzērieni', 'Drinks' ),
);
foreach ( $categories as $slug => $names ) {
	$term = get_term_by( 'slug', $slug, 'jg_food_category' );
	if ( ! $term ) {
		$result = wp_insert_term( $names[0], 'jg_food_category', array( 'slug' => $slug ) );
		if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); }
		$term_id = $result['term_id'];
	} else { $term_id = $term->term_id; }
	if ( ! get_term_meta( $term_id, 'jg_name_en', true ) ) { update_term_meta( $term_id, 'jg_name_en', $names[1] ); }
}

foreach ( $products as $index => $item ) {
	list( $asset, $lv, $en, $category, $featured ) = $item;
	$matches = glob( $source_dir . '/exec-' . $asset . '-*.png' );
	if ( count( $matches ) !== 1 ) { WP_CLI::error( "Missing or ambiguous source image for $asset" ); }
	$existing = get_posts( array( 'post_type' => 'jg_product', 'post_status' => 'any', 'meta_key' => '_jg_source_asset', 'meta_value' => $asset, 'posts_per_page' => 1, 'suppress_filters' => true ) );
	if ( $existing ) { WP_CLI::log( "Preserved product: $lv" ); continue; }
	$tmp = wp_tempnam( $matches[0] );
	if ( ! $tmp || ! copy( $matches[0], $tmp ) ) { WP_CLI::error( "Cannot prepare $asset" ); }
	$attachment_id = media_handle_sideload( array( 'name' => 'janogago-' . $asset . '.png', 'tmp_name' => $tmp ), 0, $lv );
	if ( is_wp_error( $attachment_id ) ) { @unlink( $tmp ); WP_CLI::error( $attachment_id->get_error_message() ); }
	update_post_meta( $attachment_id, '_jg_source_asset', $asset );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $lv );
	$post_id = wp_insert_post( array( 'post_type' => 'jg_product', 'post_status' => 'publish', 'post_title' => $lv, 'menu_order' => $index + 1 ), true );
	if ( is_wp_error( $post_id ) ) { WP_CLI::error( $post_id->get_error_message() ); }
	set_post_thumbnail( $post_id, $attachment_id );
	wp_set_object_terms( $post_id, $category, 'jg_food_category' );
	update_post_meta( $post_id, '_jg_source_asset', $asset );
	update_post_meta( $post_id, '_jg_name_en', $en );
	update_post_meta( $post_id, '_jg_on_home', $featured ? '1' : '0' );
	update_post_meta( $post_id, '_jg_vegan', '0' );
	update_post_meta( $post_id, '_jg_vegetarian', '0' );
	update_post_meta( $post_id, '_jg_gluten_free', '0' );
	WP_CLI::log( "Added product $post_id: $lv" );
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
WP_CLI::success( 'Local food catalog setup complete.' );
