<?php
/** One product record supplies the homepage and both language catalogs. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function jg_register_products() {
	register_post_type( 'jg_product', array(
		'labels' => array( 'name' => 'Ēdieni / Products', 'singular_name' => 'Ēdiens / Product', 'add_new_item' => 'Pievienot ēdienu / Add product', 'edit_item' => 'Rediģēt ēdienu / Edit product' ),
		'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-carrot',
		'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
	) );
	register_taxonomy( 'jg_food_category', 'jg_product', array(
		'labels' => array( 'name' => 'Kategorijas / Categories', 'singular_name' => 'Kategorija / Category' ),
		'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'hierarchical' => true,
	) );
}
add_action( 'init', 'jg_register_products' );

function jg_product_fields() {
	return array(
		'jg_name_en' => 'English name',
		'jg_description_lv' => 'Apraksts latviski',
		'jg_description_en' => 'English description',
	);
}

function jg_product_meta_box( $post ) {
	wp_nonce_field( 'jg_save_product', 'jg_product_nonce' );
	echo '<p>Virsraksts ir nosaukums latviski. Izvēlieties attēlu sadaļā “Featured image” un kategoriju sānu panelī.</p>';
	foreach ( jg_product_fields() as $key => $label ) {
		$value = get_post_meta( $post->ID, '_' . $key, true );
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( str_contains( $key, 'description' ) ) {
			echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="2" style="width:100%">' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="text" value="' . esc_attr( $value ) . '" style="width:100%">';
		}
		echo '</p>';
	}
	foreach ( array( 'jg_on_home' => 'Rādīt sākumlapā / Show on homepage', 'jg_vegan' => 'Vegāns / Vegan', 'jg_vegetarian' => 'Veģetārs / Vegetarian', 'jg_gluten_free' => 'Bezglutēna recepte / Gluten-free recipe' ) as $key => $label ) {
		echo '<p><label><input type="checkbox" name="' . esc_attr( $key ) . '" value="1" ' . checked( get_post_meta( $post->ID, '_' . $key, true ), '1', false ) . '> ' . esc_html( $label ) . '</label></p>';
	}
	echo '<p><em>Dietary labels should be checked against the recipe before publishing.</em></p>';
}
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'jg_product_details', 'Produkta informācija / Product details', 'jg_product_meta_box', 'jg_product', 'normal', 'high' );
} );

function jg_save_product( $post_id ) {
	if ( get_post_type( $post_id ) !== 'jg_product' || ! isset( $_POST['jg_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jg_product_nonce'] ) ), 'jg_save_product' ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	foreach ( jg_product_fields() as $key => $label ) {
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		update_post_meta( $post_id, '_' . $key, str_contains( $key, 'description' ) ? sanitize_textarea_field( $value ) : sanitize_text_field( $value ) );
	}
	foreach ( array( 'jg_on_home', 'jg_vegan', 'jg_vegetarian', 'jg_gluten_free' ) as $key ) {
		update_post_meta( $post_id, '_' . $key, isset( $_POST[ $key ] ) ? '1' : '0' );
	}
}
add_action( 'save_post_jg_product', 'jg_save_product' );

function jg_category_translation_field( $term = null ) {
	$value = $term ? get_term_meta( $term->term_id, 'jg_name_en', true ) : '';
	if ( $term ) {
		echo '<tr class="form-field"><th><label for="jg_name_en">English name</label></th><td><input name="jg_name_en" id="jg_name_en" value="' . esc_attr( $value ) . '" type="text"></td></tr>';
	} else {
		echo '<div class="form-field"><label for="jg_name_en">English name</label><input name="jg_name_en" id="jg_name_en" type="text"></div>';
	}
}
add_action( 'jg_food_category_add_form_fields', 'jg_category_translation_field' );
add_action( 'jg_food_category_edit_form_fields', 'jg_category_translation_field' );
function jg_save_category_translation( $term_id ) {
	if ( isset( $_POST['jg_name_en'] ) && current_user_can( 'manage_categories' ) ) {
		update_term_meta( $term_id, 'jg_name_en', sanitize_text_field( wp_unslash( $_POST['jg_name_en'] ) ) );
	}
}
add_action( 'created_jg_food_category', 'jg_save_category_translation' );
add_action( 'edited_jg_food_category', 'jg_save_category_translation' );

function jg_catalog_labels( $lang ) {
	$defaults = $lang === 'en' ? array(
		'filter' => 'Filter food', 'category' => 'Category', 'all' => 'All food', 'diet' => 'Dietary', 'vegan' => 'Vegan only', 'vegan_tag' => 'Vegan', 'vegetarian' => 'Vegetarian', 'gluten_free' => 'Gluten-free recipe',
		'apply' => 'Apply filters', 'clear' => 'Clear filters',
		'empty' => 'No dishes match these filters.', 'home_empty' => 'More dishes are coming soon.', 'other' => 'Other food',
	) : array(
		'filter' => 'Atlasīt ēdienus', 'category' => 'Kategorija', 'all' => 'Visi ēdieni', 'diet' => 'Uzturs', 'vegan' => 'Tikai vegāni', 'vegan_tag' => 'Vegāns', 'vegetarian' => 'Veģetārs', 'gluten_free' => 'Bezglutēna recepte',
		'apply' => 'Piemērot filtrus', 'clear' => 'Notīrīt filtrus',
		'empty' => 'Šiem filtriem neatbilst neviens ēdiens.', 'home_empty' => 'Drīzumā pievienosim jaunus ēdienus.', 'other' => 'Citi ēdieni',
	);
	return array_replace( $defaults, array_intersect_key( (array) get_option( 'jg_catalog_labels_' . $lang, array() ), $defaults ) );
}

function jg_catalog_labels_page() {
	add_submenu_page( 'edit.php?post_type=jg_product', 'Catalog labels', 'Catalog labels', 'manage_options', 'jg-catalog-labels', 'jg_render_catalog_labels_page' );
}
add_action( 'admin_menu', 'jg_catalog_labels_page' );
function jg_render_catalog_labels_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( isset( $_POST['jg_catalog_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jg_catalog_nonce'] ) ), 'jg_catalog_labels' ) ) {
		foreach ( array( 'lv', 'en' ) as $lang ) {
			$submitted = isset( $_POST['labels'][ $lang ] ) && is_array( $_POST['labels'][ $lang ] ) ? wp_unslash( $_POST['labels'][ $lang ] ) : array();
			$clean = array();
			foreach ( jg_catalog_labels( $lang ) as $key => $value ) {
				$clean[ $key ] = sanitize_text_field( $submitted[ $key ] ?? '' );
			}
			update_option( 'jg_catalog_labels_' . $lang, $clean );
		}
		echo '<div class="notice notice-success"><p>Saved.</p></div>';
	}
	echo '<div class="wrap"><h1>Catalog labels / Kataloga teksti</h1><form method="post">';
	wp_nonce_field( 'jg_catalog_labels', 'jg_catalog_nonce' );
	foreach ( array( 'lv' => 'Latviski', 'en' => 'English' ) as $lang => $heading ) {
		echo '<h2>' . esc_html( $heading ) . '</h2><table class="form-table"><tbody>';
		foreach ( jg_catalog_labels( $lang ) as $key => $value ) {
			echo '<tr><th><label for="label-' . esc_attr( $lang . '-' . $key ) . '">' . esc_html( $key ) . '</label></th><td><input class="regular-text" id="label-' . esc_attr( $lang . '-' . $key ) . '" name="labels[' . esc_attr( $lang ) . '][' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '"></td></tr>';
		}
		echo '</tbody></table>';
	}
	submit_button();
	echo '</form></div>';
}

function jg_product_category_name( $term, $lang ) {
	$en = get_term_meta( $term->term_id, 'jg_name_en', true );
	return $lang === 'en' && $en ? $en : $term->name;
}

function jg_sorted_food_terms() {
	$terms = get_terms( array( 'taxonomy' => 'jg_food_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) { return array(); }
	$order = array( 'mains', 'salads', 'sandwiches', 'snacks', 'desserts', 'drinks' );
	usort( $terms, function ( $a, $b ) use ( $order ) {
		$left = array_search( $a->slug, $order, true );
		$right = array_search( $b->slug, $order, true );
		$left = $left === false ? 99 : $left;
		$right = $right === false ? 99 : $right;
		return $left <=> $right ?: strcmp( $a->name, $b->name );
	} );
	return $terms;
}

function jg_product_diet_badge( $label, $symbol, $class ) {
	return '<button class="jg-diet-badge ' . esc_attr( $class ) . '" type="button" aria-label="' . esc_attr( $label ) . '" aria-expanded="false"><span aria-hidden="true">' . esc_html( $symbol ) . '</span><span class="jg-diet-tooltip" aria-hidden="true">' . esc_html( $label ) . '</span></button>';
}

function jg_product_card( $post_id, $lang, $catalog = false ) {
	$name = $lang === 'en' ? get_post_meta( $post_id, '_jg_name_en', true ) : '';
	$name = $name ?: get_the_title( $post_id );
	$description = get_post_meta( $post_id, '_jg_description_' . $lang, true );
	$image_id = get_post_thumbnail_id( $post_id );
	$image = $image_id ? wp_get_attachment_image( $image_id, 'large', false, array( 'alt' => $name, 'loading' => 'lazy' ) ) : '';
	$badges = '';
	if ( $catalog ) {
		$copy = jg_catalog_labels( $lang );
		$vegan = get_post_meta( $post_id, '_jg_vegan', true ) === '1';
		if ( $vegan ) { $badges .= jg_product_diet_badge( $copy['vegan_tag'], 'V', 'jg-diet-vegan' ); }
		if ( $vegan || get_post_meta( $post_id, '_jg_vegetarian', true ) === '1' ) { $badges .= jg_product_diet_badge( $copy['vegetarian'], 'VG', 'jg-diet-vegetarian' ); }
		if ( get_post_meta( $post_id, '_jg_gluten_free', true ) === '1' ) { $badges .= jg_product_diet_badge( $copy['gluten_free'], 'GF', 'jg-diet-gluten-free' ); }
	}
	$out = '<article class="jg-product-card"><div class="jg-product-image">' . $image . ( $badges ? '<div class="jg-diet-badges">' . $badges . '</div>' : '' ) . '</div><div class="jg-product-info">';
	$out .= '<h3 title="' . esc_attr( $name ) . '">' . esc_html( $name ) . '</h3>';
	if ( $description ) { $out .= '<p>' . esc_html( $description ) . '</p>'; }
	return $out . '</div></article>';
}

function jg_products_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'featured' => '0' ), $attributes, 'jg_products' );
	$featured = $attributes['featured'] === '1';
	$lang = jg_lang();
	$copy = jg_catalog_labels( $lang );
	$requested = isset( $_GET['food_category'] ) && is_array( $_GET['food_category'] ) ? wp_unslash( $_GET['food_category'] ) : array();
	$selected = array_map( 'sanitize_key', array_filter( $requested, 'is_string' ) );
	$terms = jg_sorted_food_terms();
	$selected = array_values( array_intersect( $selected, wp_list_pluck( $terms, 'slug' ) ) );
	$vegan = ! empty( $_GET['vegan'] ) && $_GET['vegan'] === '1';
	$vegetarian = ! empty( $_GET['vegetarian'] ) && $_GET['vegetarian'] === '1';
	$gluten_free = ! empty( $_GET['gluten_free'] ) && $_GET['gluten_free'] === '1';
	$page = $featured ? 1 : max( 1, absint( $_GET['food_page'] ?? 1 ) );
	$query = array( 'post_type' => 'jg_product', 'post_status' => 'publish', 'suppress_filters' => true, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ), 'posts_per_page' => -1 );
	if ( $featured ) {
		$query['meta_query'] = array( array( 'key' => '_jg_on_home', 'value' => '1' ) );
	} else {
		if ( $selected ) { $query['tax_query'] = array( array( 'taxonomy' => 'jg_food_category', 'field' => 'slug', 'terms' => $selected ) ); }
		$diet_query = array( 'relation' => 'AND' );
		if ( $vegan ) { $diet_query[] = array( 'key' => '_jg_vegan', 'value' => '1' ); }
		if ( $vegetarian ) {
			$diet_query[] = array( 'relation' => 'OR', array( 'key' => '_jg_vegetarian', 'value' => '1' ), array( 'key' => '_jg_vegan', 'value' => '1' ) );
		}
		if ( $gluten_free ) { $diet_query[] = array( 'key' => '_jg_gluten_free', 'value' => '1' ); }
		if ( count( $diet_query ) > 1 ) { $query['meta_query'] = $diet_query; }
	}
	$products = new WP_Query( $query );
	$cards = '';
	if ( $featured ) {
		foreach ( $products->posts as $product ) { $cards .= jg_product_card( $product->ID, $lang ); }
		return $cards ? '<div class="jg-products-grid jg-products-featured">' . $cards . '</div>' : '<p>' . esc_html( $copy['home_empty'] ) . '</p>';
	}
	$groups = array();
	foreach ( $terms as $term ) { $groups[ $term->slug ] = array( 'name' => jg_product_category_name( $term, $lang ), 'posts' => array() ); }
	$groups['other'] = array( 'name' => $copy['other'], 'posts' => array() );
	foreach ( $products->posts as $product ) {
		$assigned = get_the_terms( $product->ID, 'jg_food_category' );
		$slug = 'other';
		foreach ( $terms as $term ) {
			if ( ( ! $selected || in_array( $term->slug, $selected, true ) ) && is_array( $assigned ) && in_array( $term->term_id, wp_list_pluck( $assigned, 'term_id' ), true ) ) { $slug = $term->slug; break; }
		}
		$groups[ $slug ]['posts'][] = $product;
	}
	$ordered = array();
	foreach ( $groups as $slug => $group ) {
		foreach ( $group['posts'] as $product ) { $ordered[] = array( 'slug' => $slug, 'name' => $group['name'], 'post' => $product ); }
	}
	$total = count( $ordered );
	$total_pages = max( 1, (int) ceil( $total / 12 ) );
	$page = min( $page, $total_pages );
	$visible = array_slice( $ordered, ( $page - 1 ) * 12, 12 );
	$previous_group = '';
	foreach ( $visible as $item ) {
		if ( $previous_group !== $item['slug'] ) {
			if ( $previous_group ) { $cards .= '</div></section>'; }
			$cards .= '<section class="jg-product-group"><h2>' . esc_html( $item['name'] ) . '</h2><div class="jg-products-grid">';
			$previous_group = $item['slug'];
		}
		$cards .= jg_product_card( $item['post']->ID, $lang, true );
	}
	if ( $previous_group ) { $cards .= '</div></section>'; }
	$all_current = ! $selected && ! $vegan && ! $vegetarian && ! $gluten_free;
	$filters = '<form class="jg-product-filters" method="get"><fieldset><legend class="jg-visually-hidden">' . esc_html( $copy['category'] ) . '</legend>';
	$filters .= '<a class="jg-filter-option jg-filter-all' . ( $all_current ? ' is-current' : '' ) . '" href="' . esc_url( get_permalink( get_queried_object_id() ) ) . '"' . ( $all_current ? ' aria-current="true"' : '' ) . '><span class="jg-filter-check" aria-hidden="true"></span><span>' . esc_html( $copy['all'] ) . '</span></a>';
	foreach ( $terms as $term ) {
		$empty = ! $term->count;
		$filters .= '<label class="jg-filter-option' . ( $empty ? ' is-empty' : '' ) . '"><input type="checkbox" name="food_category[]" value="' . esc_attr( $term->slug ) . '" ' . checked( in_array( $term->slug, $selected, true ), true, false ) . ( $empty ? ' disabled' : '' ) . '><span>' . esc_html( jg_product_category_name( $term, $lang ) ) . '</span></label>';
	}
	$filters .= '</fieldset><fieldset class="jg-diet-filter"><legend class="jg-visually-hidden">' . esc_html( $copy['diet'] ) . '</legend>';
	foreach ( array( 'vegan' => $vegan, 'vegetarian' => $vegetarian, 'gluten_free' => $gluten_free ) as $key => $checked ) {
		$filters .= '<label class="jg-filter-option"><input type="checkbox" name="' . esc_attr( $key ) . '" value="1" ' . checked( $checked, true, false ) . '><span>' . esc_html( $copy[ $key ] ) . '</span></label>';
	}
	$filters .= '</fieldset>';
	$filters .= '<button type="submit">' . esc_html( $copy['apply'] ) . '</button><a class="jg-filter-clear" href="' . esc_url( get_permalink( get_queried_object_id() ) ) . '">' . esc_html( $copy['clear'] ) . '</a></form>';
	$out = '<div class="jg-catalog-layout"><aside class="jg-catalog-sidebar"><h2>' . esc_html( $copy['filter'] ) . '</h2>';
	$out .= '<button class="jg-filter-toggle" type="button" aria-expanded="true" aria-controls="jg-filter-panel" hidden><span class="jg-filter-toggle-label">' . esc_html( $copy['filter'] ) . '</span><span class="jg-filter-count" aria-hidden="true" hidden></span><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 9 7 7 7-7"/></svg></button>';
	$out .= '<div class="jg-filter-panel" id="jg-filter-panel">' . $filters . '</div></aside><div class="jg-catalog-results">';
	$out .= $cards ?: '<p class="jg-products-empty">' . esc_html( $copy['empty'] ) . '</p>';
	if ( $total_pages > 1 ) {
		$out .= '<nav class="jg-product-pages" aria-label="' . esc_attr( $lang === 'en' ? 'Product pages' : 'Ēdienu lapas' ) . '"><ul>';
		for ( $number = 1; $number <= $total_pages; $number++ ) {
			$url = add_query_arg( array( 'food_page' => $number === 1 ? null : $number, 'food_category' => $selected ?: null, 'vegan' => $vegan ? '1' : null, 'vegetarian' => $vegetarian ? '1' : null, 'gluten_free' => $gluten_free ? '1' : null ), get_permalink( get_queried_object_id() ) );
			$out .= '<li><a href="' . esc_url( $url ) . '"' . ( $number === $page ? ' class="current" aria-current="page"' : '' ) . '>' . (int) $number . '</a></li>';
		}
		$out .= '</ul></nav>';
	}
	return $out . '</div></div>';
}
add_shortcode( 'jg_products', 'jg_products_shortcode' );
