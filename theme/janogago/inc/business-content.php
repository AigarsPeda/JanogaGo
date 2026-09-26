<?php
/** Initial business copy. After the migration, page blocks own all content. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jg_business_defaults( $language ) {
	return $language === 'en' ? array(
		'hero_title' => 'Meals on site, without running a canteen.',
		'hero_text' => 'JāņogaGO provides food vending for locations where employees or visitors have few convenient meal options. Our fully managed service includes installation, meal deliveries, restocking and equipment maintenance.',
		'hero_cta' => 'Request a proposal',
		'process_title' => 'How do we get started?',
		'step_one' => 'Tell us about your location',
		'step_one_text' => 'Complete the enquiry form or contact us. Tell us the city, the approximate number of employees or visitors, and when people need access to meals.',
		'step_two' => 'We prepare a proposal',
		'step_two_text' => 'We discuss your needs and assess installation options. Your proposal includes suitable equipment, a food selection, service terms and costs.',
		'step_three' => 'We install and manage the service',
		'step_three_text' => 'Once agreed, we install the machine and make the first food delivery. We then take care of restocking and equipment maintenance.',
		'contact_title' => 'Let’s discuss meals at your location.',
		'contact_text' => 'Tell us where you need food vending, how many people might use it, and at what times. We will contact you to assess suitability and prepare a proposal.',
		'form_name_label' => 'Contact name',
		'form_location_label' => 'Location or city',
		'form_people_label' => 'Approximate daily users',
		'form_message_label' => 'Opening hours and additional information',
		'form_submit_label' => 'Request a proposal',
		'form_success_message' => 'Thank you! We have received your enquiry and will be in touch.',
		'form_invalid_message' => 'Please complete the required fields and consent checkbox.',
		'form_failed_message' => 'Your enquiry could not be saved. Please try again or contact us by email or phone.',
		'form_dismiss_label' => 'Close notification',
	) : array(
		'hero_title' => 'Maltītes uz vietas, bez savas ēdnīcas.',
		'hero_text' => 'JāņogaGO piedāvā ēdienu automātus vietām, kur darbiniekiem vai apmeklētājiem nav ērtu iespēju paēst. Pilna servisa risinājumā mēs nodrošinām automāta uzstādīšanu, maltīšu piegādi, krājumu papildināšanu un tehnisko apkopi.',
		'hero_cta' => 'Saņemt piedāvājumu',
		'process_title' => 'Kā sākt sadarbību?',
		'step_one' => 'Pastāstiet par savu atrašanās vietu',
		'step_one_text' => 'Aizpildiet pieteikumu vai sazinieties ar mums. Norādiet pilsētu, aptuveno darbinieku vai apmeklētāju skaitu un laiku, kad nepieciešama iespēja paēst.',
		'step_two' => 'Sagatavojam piedāvājumu',
		'step_two_text' => 'Pārrunājam jūsu vajadzības un izvērtējam uzstādīšanas iespējas. Piedāvājumā iekļaujam piemērotu automātu, sortimentu, servisa nosacījumus un izmaksas.',
		'step_three' => 'Uzstādām un apkalpojam',
		'step_three_text' => 'Pēc vienošanās uzstādām automātu un nodrošinām pirmo pārtikas piegādi. Turpmāk rūpējamies par krājumu papildināšanu un automāta apkopi.',
		'contact_title' => 'Pārrunāsim ēdināšanu jūsu atrašanās vietā.',
		'contact_text' => 'Pastāstiet, kur nepieciešams ēdienu automāts, cik cilvēku varētu to izmantot un kādā laikā. Sazināsimies, lai pārrunātu piemērotību un sagatavotu piedāvājumu.',
		'form_name_label' => 'Kontaktpersonas vārds',
		'form_location_label' => 'Atrašanās vieta vai pilsēta',
		'form_people_label' => 'Aptuvenais lietotāju skaits dienā',
		'form_message_label' => 'Darba laiks un papildu informācija',
		'form_submit_label' => 'Pieprasīt piedāvājumu',
		'form_success_message' => 'Paldies! Esam saņēmuši jūsu pieprasījumu. Sazināsimies ar jums.',
		'form_invalid_message' => 'Lūdzu, aizpildiet obligātos laukus un atzīmējiet piekrišanu.',
		'form_failed_message' => 'Neizdevās saglabāt pieprasījumu. Lūdzu, mēģiniet vēlreiz vai sazinieties ar mums pa e-pastu vai tālruni.',
		'form_dismiss_label' => 'Aizvērt paziņojumu',
	);
}

function jg_business_food_blocks( $language ) {
	$is_en = $language === 'en';
	$items = $is_en ? array(
		array( 'Mains and salads', 'A proper meal for your lunch break.', 'anh-nguyen-kcA-c3f_3FE-unsplash.jpg', 'Illustrative salad bowl' ),
		array( 'Sandwiches and snacks', 'Pastries for a short break.', 'leyli-sadeqian-wSmhn8taZpc-unsplash.jpg', 'Illustrative hot dog and fries in takeaway trays' ),
		array( 'Desserts and drinks', 'For a sweet break.', 'fidel-fernando-KY5ZBCIE18E-unsplash.jpg', 'Illustrative glazed doughnuts in a box' ),
	) : array(
		array( 'Pamatēdieni un salāti', 'Pilnvērtīga maltīte pusdienu pauzei.', 'anh-nguyen-kcA-c3f_3FE-unsplash.jpg', 'Ilustratīva salātu bļoda' ),
		array( 'Sendviči un uzkodas', 'Arī konditoreja nelielai pauzei.', 'leyli-sadeqian-wSmhn8taZpc-unsplash.jpg', 'Ilustratīvs hotdogs un frī kartupeļi līdzņemšanas iepakojumos' ),
		array( 'Deserti un dzērieni', 'Saldai pauzei.', 'fidel-fernando-KY5ZBCIE18E-unsplash.jpg', 'Ilustratīvi glazēti virtuļi kastītē' ),
	);
	$cards = '';
	foreach ( $items as $item ) {
		$cards .= jg_block_column( jg_block_group( jg_block_image( jg_seed_attachment( $item[2] ), $item[3] ) . jg_block_heading( $item[0], 3 ) . jg_block_paragraph( $item[1] ), 'jg-food-card', 'article' ) );
	}
	return jg_block_group(
		jg_block_heading( $is_en ? 'What can people buy at your machine?' : 'Ko varēs iegādāties jūsu automātā?' ) .
		jg_block_paragraph( $is_en ? 'Meals, snacks and drinks for everyday mealtimes and breaks. We select the range around the needs of your employees and visitors.' : 'Ēdieni, uzkodas un dzērieni ikdienas maltītēm un pauzēm. Sortimentu izvēlamies atbilstoši jūsu darbinieku un apmeklētāju vajadzībām.', 'jg-section-lede' ) .
		jg_block_columns( $cards, 'jg-food-grid' ) .
		jg_block_paragraph( $is_en ? 'Images illustrate food categories. The actual selection may differ.' : 'Attēli ilustrē ēdienu kategorijas. Faktiskais sortiments var atšķirties.', 'jg-image-note' ),
		'food-range section jg-block-section jg-block-food-range', 'section', 'sortiments'
	);
}

function jg_business_experience_blocks( $language ) {
	$is_en = $language === 'en';
	$stats = $is_en ? array( array( '20 years', 'Experience in catering' ), array( '250+', 'Companies served over the years' ), array( '650+', 'People eat in our cafés every day' ) ) : array( array( '20 gadi', 'Pieredze ēdināšanā' ), array( '250+', 'Uzņēmumu apkalpoti šo gadu laikā' ), array( '650+', 'Cilvēku ik dienu paēd mūsu kafejnīcās' ) );
	$numbers = '';
	foreach ( $stats as $stat ) {
		$numbers .= jg_block_group( jg_block_paragraph( $stat[0], 'jg-experience-number' ) . jg_block_paragraph( $stat[1], 'jg-experience-label' ), 'jg-experience-stat', 'div' );
	}
	$copy = jg_block_heading( $is_en ? 'Janoga’s experience preparing meals.' : 'Jāņogas pieredze maltīšu gatavošanā.' ) . jg_block_paragraph( $is_en ? 'Every day, we prepare and serve meals for company employees and visitors to our cafés. With JāņogaGO, we bring that experience to locations without a café or convenient lunch options nearby.' : 'Ikdienā gatavojam un pasniedzam maltītes uzņēmumu darbiniekiem un mūsu kafejnīcu apmeklētājiem. Ar JāņogaGO šo pieredzi izmantojam, lai nodrošinātu maltītes arī vietās, kur nav kafejnīcas vai tuvumā pieejamu pusdienu iespēju.' );
	return jg_block_group( jg_block_columns( jg_block_column( $copy ) . jg_block_column( jg_block_group( $numbers, 'jg-experience-stats', 'div' ) ), 'jg-experience-layout' ), 'experience section jg-block-section jg-block-experience', 'section', 'pieredze' );
}

function jg_business_service_blocks( $language ) {
	$is_en = $language === 'en';
	$ours = $is_en ? array( 'Machine installation', 'Meal deliveries and restocking', 'Machine cleaning and maintenance' ) : array( 'Automāta uzstādīšanu', 'Maltīšu piegādi un krājumu papildināšanu', 'Automāta tīrīšanu un tehnisko apkopi' );
	$yours = $is_en ? array( 'A suitable space for the machine', 'An electricity supply', 'Access for deliveries and maintenance' ) : array( 'Piemērotu vietu automātam', 'Elektrības pieslēgumu', 'Piekļuvi piegādēm un apkopei' );
	$columns = '';
	foreach ( array( array( $is_en ? 'We provide' : 'Mēs nodrošinām', $ours ), array( $is_en ? 'You provide' : 'Jūs nodrošināt', $yours ) ) as $side ) {
		$items = jg_block_heading( $side[0], 3 );
		foreach ( $side[1] as $item ) {
			$items .= jg_block_paragraph( $item, 'jg-responsibility-item' );
		}
		$columns .= jg_block_column( $items );
	}
	$content = jg_block_heading( $is_en ? 'What’s included, and what do you provide?' : 'Kas ietilpst servisā, un kas jānodrošina jums?' ) .
		jg_block_paragraph( $is_en ? 'JāņogaGO handles installation, meal deliveries, restocking, cleaning and equipment maintenance. We agree on the food selection and service arrangements to suit your location.' : 'JāņogaGO rūpējas par automāta uzstādīšanu, maltīšu piegādi, krājumu papildināšanu, tīrīšanu un tehnisko apkopi. Vienojamies par sortimentu un apkalpošanas kārtību, lai ēdienu piedāvājums atbilstu jūsu atrašanās vietas vajadzībām.', 'jg-section-lede' ) .
		jg_block_columns( $columns, 'jg-responsibility-grid' ) .
		jg_block_paragraph( $is_en ? 'We confirm installation requirements, responsibilities and costs in your proposal before the service starts. Your employees do not need to organise meal deliveries or restock the machine.' : 'Uzstādīšanas prasības, pienākumu sadalījumu un izmaksas precizējam piedāvājumā pirms sadarbības sākuma. Jūsu darbiniekiem nav jāorganizē maltīšu piegādes vai jāpapildina automāta krājumi.', 'jg-service-note' );
	return jg_block_group( $content, 'services section jg-block-section jg-block-services', 'section', 'risinajumi' );
}

function jg_business_suitability_blocks( $language ) {
	$is_en = $language === 'en';
	$content = jg_block_heading( $is_en ? 'Is food vending right for your location?' : 'Vai ēdienu automāts derēs jūsu atrašanās vietai?' ) .
		jg_block_paragraph( $is_en ? 'If people work at or visit your location every day and have few convenient meal options, food vending may be a suitable solution. It can serve production sites, warehouses, office buildings and locations with regular visitors, including evening and shift work.' : 'Ja jūsu atrašanās vietā ikdienā strādā vai uzturas cilvēki, kuriem nav ērtu iespēju paēst, ēdienu automāts var būt piemērots risinājums. Tas var noderēt ražotnēs, noliktavās, biroju ēkās un vietās ar regulāru apmeklētāju plūsmu, arī tad, ja cilvēki tur uzturas vakaros vai strādā maiņās.' ) .
		jg_block_paragraph( $is_en ? 'Before preparing a proposal, we assess foot traffic, expected demand, opening hours and space for the machine. We also check whether we can provide deliveries and regular servicing.' : 'Pirms piedāvājuma sagatavošanas izvērtējam cilvēku plūsmu, paredzamo pieprasījumu, darba laiku un vietu automātam. Pārbaudām arī iespējas nodrošināt piegādes un regulāru apkalpošanu.' ) .
		jg_block_button( $is_en ? 'Discuss your location' : 'Pārrunāt iespējas', '#pieteikties', 'button' );
	return jg_block_group( $content, 'suitability section jg-block-section jg-block-suitability', 'section', 'piemerotiba' );
}

function jg_business_process_blocks( $language ) {
	$copy = jg_defaults( $language );
	$steps = '';
	foreach ( array( 'one', 'two', 'three' ) as $index => $step ) {
		$steps .= jg_block_group( jg_block_paragraph( sprintf( '%02d', $index + 1 ), 'jg-step-number' ) . jg_block_heading( $copy[ 'step_' . $step ], 3 ) . jg_block_paragraph( $copy[ 'step_' . $step . '_text' ] ), 'jg-process-step', 'div' );
	}
	return jg_block_group( jg_block_columns( jg_block_column( jg_block_heading( $copy['process_title'] ) ) . jg_block_column( jg_block_group( $steps, 'jg-process-steps', 'div' ) ), 'jg-block-process-layout' ), 'process section jg-block-section jg-block-process', 'section', 'ka-tas-notiek' );
}

function jg_business_faq_blocks( $language ) {
	$is_en = $language === 'en';
	$questions = $is_en ? array(
		'How much does the service cost?' => 'Costs depend on the location, equipment and service arrangements. Your proposal sets out installation and service costs before you decide.',
		'How many users do we need?' => 'We assess expected demand, foot traffic and opening hours for each location. Tell us how many people work at or visit the site each day.',
		'What space and connections are needed?' => 'You provide a suitable space, electricity and access for deliveries and maintenance. We confirm dimensions and any other connection requirements for the selected equipment.',
		'Who looks after the equipment?' => 'JāņogaGO handles restocking, cleaning and technical maintenance as agreed in the service proposal.',
		'What food will be available?' => 'The proposed range includes lunch meals, salads, bowls and desserts. We agree on the selection for your location. Storage and any reheating arrangements are confirmed in the proposal.',
	) : array(
		'Kādas ir pakalpojuma izmaksas?' => 'Izmaksas ir atkarīgas no atrašanās vietas, automāta un apkalpošanas nosacījumiem. Piedāvājumā norādām uzstādīšanas un servisa izmaksas, lai varat tās izvērtēt pirms sadarbības sākuma.',
		'Cik cilvēku nepieciešams automāta uzstādīšanai?' => 'Katrai vietai izvērtējam paredzamo pieprasījumu, cilvēku plūsmu un darba laiku. Pastāstiet, cik cilvēku ikdienā strādā vai uzturas jūsu atrašanās vietā.',
		'Kāda vieta un pieslēgumi nepieciešami?' => 'Jums jānodrošina piemērota vieta, elektrība un piekļuve piegādēm un apkopei. Izvēlētā automāta izmērus un citas pieslēgumu prasības precizējam piedāvājumā.',
		'Kas rūpējas par automāta apkalpošanu?' => 'JāņogaGO rūpējas par krājumu papildināšanu, tīrīšanu un tehnisko apkopi atbilstoši servisa piedāvājumā noteiktajam.',
		'Kāds ēdiens būs pieejams?' => 'Piedāvātajā sortimentā ir maltītes pusdienām, salāti, bļodas un deserti. Konkrēto izvēli saskaņojam jūsu atrašanās vietai. Uzglabāšanu un iespējamo maltīšu uzsildīšanu precizējam piedāvājumā.',
	);
	$items = '';
	foreach ( $questions as $question => $answer ) {
		$items .= jg_block_faq_item( $question, $answer );
	}
	return jg_block_group( jg_block_heading( $is_en ? 'Frequently asked questions.' : 'Biežāk uzdotie jautājumi.' ) . jg_block_group( $items, 'jg-faq-list', 'div' ), 'faq section jg-block-section jg-block-faq', 'section', 'biezi-uzdotie-jautajumi' );
}

/** Each label and message is a normal Paragraph block, editable on the canvas. */
function jg_business_form_blocks( $language ) {
	$copy = jg_defaults( $language );
	$fields = array( 'company', 'name', 'email', 'phone', 'location', 'people', 'message', 'privacy' );
	$content = '';
	foreach ( $fields as $field ) {
		$key = $field === 'privacy' ? 'form_privacy_label' : 'form_' . $field . '_label';
		$content .= jg_block_paragraph( $copy[ $key ], 'jg-form-field jg-field-' . $field );
	}
	$content .= jg_block_button( $copy['form_submit_label'], '#pieteikties', 'button button-dark jg-form-submit' );
	foreach ( array( 'success', 'invalid', 'phone_invalid', 'failed' ) as $message ) {
		$content .= jg_block_paragraph( $copy[ 'form_' . $message . '_message' ], 'jg-form-status jg-status-' . $message );
	}
	$content .= jg_block_paragraph( $copy['form_dismiss_label'], 'jg-form-status jg-status-dismiss' );
	return jg_block_group( $content, 'jg-enquiry-form', 'div' );
}

function jg_business_contact_blocks( $language, $page_id ) {
	$copy = jg_defaults( $language );
	$details = jg_existing_contact_blocks( parse_blocks( (string) get_post_field( 'post_content', $page_id ) ) );
	$intro = jg_block_heading( $copy['contact_title'] ) . jg_block_paragraph( $copy['contact_text'] ) . ( $details ?: jg_contact_detail_blocks( $copy ) );
	return jg_block_group( jg_block_columns( jg_block_column( $intro ) . jg_block_column( jg_business_form_blocks( $language ) ), 'jg-block-contact-layout' ), 'contact jg-block-section jg-block-contact', 'section', 'pieteikties' );
}

function jg_existing_contact_blocks( $blocks ) {
	$content = '';
	foreach ( $blocks as $block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'jg-contact-detail' ) ) {
			$content .= serialize_block( $block );
		} else {
			$content .= jg_existing_contact_blocks( $block['innerBlocks'] ?? array() );
		}
	}
	return $content;
}

function jg_business_home_blocks( $language, $page_id ) {
	$is_en = $language === 'en';
	$copy = jg_defaults( $language );
	$hero_copy = jg_block_heading( $copy['hero_title'], 1 ) . jg_block_paragraph( $copy['hero_text'], 'lede' ) . jg_block_button( $copy['hero_cta'], '#pieteikties', 'button button-light' );
	$hero_visual = jg_block_image( jg_seed_attachment( 'leyli-sadeqian-wSmhn8taZpc-unsplash.jpg' ), $is_en ? 'Hot dog and fries in takeaway trays' : 'Hotdogs un frī kartupeļi līdzņemšanas iepakojumos' );
	$hero = jg_block_group( jg_block_columns( jg_block_column( $hero_copy, 'hero-copy', 'center' ) . jg_block_column( $hero_visual, 'hero-visual', 'center' ), 'jg-block-hero-layout', 'center' ), 'hero jg-block-section jg-block-hero' );
	$clients = strtr( jg_clients_section_blocks( $language ), $is_en ? array( 'Trusted by teams who keep moving.' => 'Clients who have trusted us.', 'From production and retail to logistics.' => 'Companies we have served through our catering services.' ) : array( 'Mūs jau novērtē.' => 'Klienti, kuri mums uzticējušies.', 'No ražotnēm līdz mazumtirdzniecībai un loģistikai.' => 'Uzņēmumi, kuriem esam nodrošinājuši ēdināšanu.' ) );
	$footer = jg_block_group( jg_block_paragraph( $copy['contact_address'], 'jg-footer-address' ) . jg_block_paragraph( '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ), 'jg-footer-copyright' ), 'jg-block-footer-content', 'div' );
	return $hero . jg_business_food_blocks( $language ) . jg_business_experience_blocks( $language ) . $clients . jg_business_service_blocks( $language ) . jg_business_suitability_blocks( $language ) . jg_business_process_blocks( $language ) . jg_business_faq_blocks( $language ) . jg_business_contact_blocks( $language, $page_id ) . $footer;
}

/** Runs once; later editor changes or deletion are never regenerated. */
function jg_migrate_business_content() {
	if ( get_option( 'jg_business_content_v1' ) ) {
		return;
	}
	$pages = get_option( 'jg_seeded_pages', array() );
	$complete = true;
	foreach ( array( 'lv', 'en' ) as $language ) {
		$page_id = absint( $pages[ $language ] ?? 0 );
		$page = $page_id ? get_post( $page_id ) : null;
		if ( ! $page ) {
			$complete = false;
			continue;
		}
		if ( get_post_meta( $page_id, '_jg_business_content_v1', true ) ) {
			continue;
		}
		$content = jg_business_home_blocks( $language, $page_id );
		// Preserve any additional root blocks outside the known homepage sections.
		foreach ( parse_blocks( $page->post_content ) as $block ) {
			if ( trim( $block['innerHTML'] ?? '' ) && ! str_contains( $block['attrs']['className'] ?? '', 'jg-block-section' ) ) {
				$content .= serialize_block( $block );
			}
		}
		$result = wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ), true );
		if ( is_wp_error( $result ) ) {
			$complete = false;
			continue;
		}
		update_post_meta( $page_id, '_jg_business_content_v1', 1 );
		update_post_meta( $page_id, '_jg_gutenberg_seeded', 1 );
	}
	if ( $complete ) {
		update_option( 'jg_business_content_v1', 1, false );
	}
}
add_action( 'init', 'jg_migrate_business_content', 30 );

/** Reads the editable native form blocks, without falling back to deleted text. */
function jg_form_block_copy( $block ) {
	$copy = array();
	foreach ( $block['innerBlocks'] ?? array() as $child ) {
		$class = $child['attrs']['className'] ?? '';
		if ( preg_match( '/\bjg-(?:field|status)-([a-z_]+)\b/', $class, $match ) ) {
			$copy[ $match[1] ] = trim( wp_strip_all_tags( $child['innerHTML'] ) );
		}
		if ( $child['blockName'] === 'core/buttons' ) {
			foreach ( $child['innerBlocks'] as $button ) {
				if ( str_contains( $button['attrs']['className'] ?? '', 'jg-form-submit' ) ) {
					$copy['submit'] = trim( preg_replace( '/\s*↗\s*$/u', '', wp_strip_all_tags( $button['innerHTML'] ) ) );
				}
			}
		}
	}
	return $copy;
}

function jg_enquiry_result_markup( $copy ) {
	$status = sanitize_key( wp_unslash( $_GET['enquiry'] ?? '' ) );
	$key = array( 'sent' => 'success', 'invalid' => 'invalid', 'failed' => 'failed', 'mail_failed' => 'success' )[ $status ] ?? '';
	if ( ! $key || empty( $copy[ $key ] ) ) {
		return '';
	}
	$success = $key === 'success';
	$icon = $success ? '<path d="m5 12 4 4L19 6"/>' : '<path d="M12 5v8m0 4v1"/>';
	$close = empty( $copy['dismiss'] ) ? '' : '<button type="button" class="jg-notice-close" aria-label="' . esc_attr( $copy['dismiss'] ) . '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="m6 6 12 12M6 18 18 6"/></svg></button>';
	return '<div id="jg-enquiry-result" class="form-message form-message-' . ( $success ? 'success' : 'error' ) . '" role="' . ( $success ? 'status' : 'alert' ) . '" tabindex="-1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">' . $icon . '</svg><p>' . esc_html( $copy[ $key ] ) . '</p>' . $close . '</div>';
}

function jg_render_editable_form( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/group' || ! in_array( 'jg-enquiry-form', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return $content;
	}
	$copy = jg_form_block_copy( $block );
	$page_id = get_queried_object_id() ?: get_the_ID();
	$html = '<form class="jg-enquiry-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" data-required-message="' . esc_attr( $copy['invalid'] ?? '' ) . '" data-phone-invalid-message="' . esc_attr( $copy['phone_invalid'] ?? '' ) . '">';
	$html .= '<input type="hidden" name="action" value="jg_submit_enquiry"><input type="hidden" name="page_id" value="' . absint( $page_id ) . '"><input type="hidden" name="service_interest" value="full-service">' . wp_nonce_field( 'jg_submit_enquiry', 'jg_enquiry_nonce', true, false );
	$html .= jg_enquiry_result_markup( $copy );
	$inputs = array(
		'company' => 'type="text" autocomplete="organization" maxlength="120"',
		'name' => 'type="text" autocomplete="name" maxlength="120"',
		'email' => 'type="email" autocomplete="email" maxlength="254"',
		'phone' => 'type="tel" autocomplete="tel" inputmode="tel" pattern="[0-9+(). -]{7,25}" maxlength="25"',
		'location' => 'type="text" autocomplete="address-level2" maxlength="200"',
		'people' => 'type="number" inputmode="numeric" min="1" step="1"',
	);
	foreach ( $inputs as $name => $attributes ) {
		if ( isset( $copy[ $name ] ) ) {
			$html .= '<label>' . esc_html( $copy[ $name ] ) . '<input required name="' . esc_attr( $name ) . '" ' . $attributes . '></label>';
		}
	}
	if ( isset( $copy['message'] ) ) {
		$html .= '<label class="full">' . esc_html( $copy['message'] ) . '<textarea name="message" rows="3" maxlength="2000"></textarea></label>';
	}
	if ( isset( $copy['privacy'] ) ) {
		$html .= '<label class="full jg-privacy-consent"><input required name="privacy_consent" type="checkbox" value="1"><span>' . esc_html( $copy['privacy'] ) . '</span></label>';
	}
	if ( isset( $copy['submit'] ) ) {
		$html .= '<button class="button button-dark" type="submit">' . esc_html( $copy['submit'] ) . jg_arrow_icon() . '</button>';
	}
	return $html . '</form>';
}
add_filter( 'render_block', 'jg_render_editable_form', 20, 2 );

function jg_hide_footer_content_in_page( $content, $block ) {
	return str_contains( $block['attrs']['className'] ?? '', 'jg-block-footer-content' ) ? '' : $content;
}
add_filter( 'render_block', 'jg_hide_footer_content_in_page', 20, 2 );

function jg_page_footer_content( $page_id ) {
	foreach ( parse_blocks( (string) get_post_field( 'post_content', $page_id ) ) as $block ) {
		if ( str_contains( $block['attrs']['className'] ?? '', 'jg-block-footer-content' ) ) {
			foreach ( $block['innerBlocks'] as $child ) {
				echo render_block( $child );
			}
		}
	}
}
