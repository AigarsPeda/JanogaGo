<?php
/** One-time local preview setup. Never seed or overwrite live/authored pages. */
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.local' ) ) { WP_CLI::error( 'Local only.' ); }
$options = jg_privacy_options();
$content = array(
	'lv' => array(
		'title' => 'Privātums un sīkdatnes', 'slug' => 'privatums',
		'sections' => array(
			'Lokāls melnraksts' => 'Pirms publicēšanas apstipriniet pārziņa juridisko nosaukumu, reģistrācijas numuru un adresi, pieprasījumu un servera žurnālu glabāšanas termiņus, pakalpojumu sniedzējus un Google Analytics iestatījumus. Analītika šajā lokālajā vietnē ir izslēgta.',
			'Kas atbild par datiem' => 'JāņogaGO vietnes datu pārzinis: [juridiskais nosaukums, reģistrācijas numurs, juridiskā adrese — jāapstiprina]. Par personas datu jautājumiem rakstiet uz info@janoga.lv.',
			'Jūsu pieprasījumi' => 'Ja aizpildāt pieteikuma formu, mēs saņemam uzņēmuma nosaukumu, kontaktpersonas vārdu, e-pastu, tālruni, atrašanās vietu, darbinieku skaitu un jūsu ziņu. Izmantojam tos, lai atbildētu, sagatavotu piedāvājumu un sazinātos par pieprasīto pakalpojumu. Apstrādes pamats ir darbības pirms līguma noslēgšanas pēc jūsu pieprasījuma vai leģitīma interese atbildēt uzņēmuma pārstāvja pieprasījumam. Šie dati netiek nosūtīti Google Analytics. Formas nosūtīšana neprasa piekrišanu analītikai.',
			'Vietnes darbība un drošība' => 'Vietnes darbībai un drošībai serveris var apstrādāt IP adresi, pieprasījuma laiku, adresi un pārlūka informāciju. Pieteikumu forma izmanto īslaicīgus aizsardzības skaitītājus; to atslēgas tiek veidotas no IP adreses un e-pasta jaucējvērtībām. Apstrādes pamats ir leģitīma interese nodrošināt vietnes darbību un novērst ļaunprātīgu izmantošanu.',
			'Google Analytics, tikai ar atļauju' => 'Plānojam izmantot Google Analytics 4, lai novērtētu lapu apmeklējumu, iesaisti, veiksmīgi nosūtītus pieteikumus un klikšķus uz saziņas saitēm. Ar jūsu piekrišanu Google var apstrādāt sīkdatņu identifikatorus, apmeklētās lapas, ierīces un pārlūka informāciju, aptuvenu atrašanās vietu un mijiedarbības notikumus. Mūsu notikumi nesatur formas laukus, e-pastus vai tālruņu numurus; lapu adresēm noņemam vaicājuma parametrus un fragmentus. Piekrišanas pamats: VDAR 6. panta 1. punkta a) apakšpunkts. Analītikas tags netiek ielādēts pirms atļaujas. Reklāmas personalizācija un Google signāli šajā integrācijā ir izslēgti.',
			'Sīkdatnes un izvēles maiņa' => 'pll_language saglabā izvēlēto valodu līdz vienam gadam. jg_consent saglabā jūsu analītikas izvēli, tās laiku un paziņojuma versiju 180 dienas; tā ir nepieciešama jūsu izvēles ievērošanai. Pēc analītikas atļaujas _ga un _ga_* identificē pārlūku un sesiju; šajā integrācijā to termiņš nepārsniedz 180 dienas. Vietējās krātuves atslēga jg_consent_changed īslaicīgi paziņo citām atvērtām cilnēm par izmaiņām un uzreiz tiek dzēsta. Analītiku varat atļaut vai noraidīt paziņojumā un jebkurā laikā mainīt lapas apakšā, izvēloties “Sīkdatņu iestatījumi”. Atsaukšana aptur turpmāku analītiku un dzēš šīs vietnes Analytics sīkdatnes; tā pati par sevi nedzēš jau nosūtītos datus. Ja JavaScript ir izslēgts, mūsu Analytics integrācija nedarbojas.',
			'Kam dati pieejami un cik ilgi' => array(
				'Jūsu pieteikumu saglabājam vietnes WordPress datubāzē un nosūtām uz info@janoga.lv. Datiem piekļūst pilnvaroti JāņogaGO darbinieki, kuri izskata pieprasījumu un sazinās ar jums. Publiskā vietne janogago.lv tiek mitināta DigitalOcean serverī, bet pieteikumu e-pasta nosūtīšanai tajā izmantojam Google Gmail pakalpojumu. Šie pakalpojumu sniedzēji var apstrādāt datus, lai nodrošinātu vietnes darbību un e-pasta piegādi.',
				'Pieprasījumu datu glabāšanas ilgumu nosaka laiks, kas vajadzīgs pieprasījuma izskatīšanai un ar to saistītajai saziņai. Ja noslēdzam līgumu, ar sadarbību saistītos datus var būt nepieciešams glabāt arī līguma izpildei un piemērojamo tiesisko pienākumu izpildei. Tehniskie žurnāli un rezerves kopijas tiek izmantoti vietnes drošībai un atjaunošanai. Par savu datu glabāšanu vai dzēšanu varat jautāt, rakstot uz info@janoga.lv.',
				'Google Analytics pašlaik ir izslēgts, un analītikas dati netiek vākti. Pēc tā aktivizēšanas analītikas datus apstrādāsim tikai ar jūsu piekrišanu. Google Analytics pakalpojumu nodrošina Google Ireland Limited; apstrādē var iesaistīties arī Google LLC un serveri ārpus Eiropas Ekonomikas zonas. Plašāka informācija ir pieejama Google datu apstrādes noteikumos un skaidrojumā par informācijas izmantošanu vietnēs, kurās tiek lietoti Google pakalpojumi.',
			),
			'Jūsu tiesības' => 'Atbilstoši piemērojamajiem nosacījumiem varat pieprasīt piekļuvi saviem datiem, labošanu, dzēšanu, apstrādes ierobežošanu un pārnesamību, iebilst pret apstrādi leģitīmu interešu dēļ un atsaukt piekrišanu. Atsaukšana neietekmē iepriekšējās apstrādes tiesiskumu. Rakstiet uz info@janoga.lv. Varat iesniegt sūdzību Datu valsts inspekcijā: https://www.dvi.gov.lv/.',
		),
	),
	'en' => array(
		'title' => 'Privacy & cookies', 'slug' => 'privacy',
		'sections' => array(
			'Local draft' => 'Before publishing, confirm the controller’s legal name, registration number and address, enquiry and server-log retention, service providers and Google Analytics settings. Analytics is disabled on this local website.',
			'Who is responsible for your data' => 'Controller for the JāņogaGO website: [legal company name, registration number and registered address — to be confirmed]. For personal data questions, contact info@janoga.lv.',
			'Your enquiries' => 'When you submit an enquiry, we receive your company name, contact name, email, phone number, location, headcount and message. We use these to respond, prepare a proposal and discuss the requested service. The legal basis is taking steps at your request before entering a contract, or our legitimate interest in responding to a company representative. We do not send these details to Google Analytics. Submitting the form does not require analytics consent.',
			'Website operation and security' => 'For operation and security, the server may process your IP address, request time, URL and browser information. The enquiry form uses short-lived abuse-prevention counters keyed by hashes derived from the IP address and email. The legal basis is our legitimate interest in keeping the website working and preventing abuse.',
			'Google Analytics, with your permission' => 'We plan to use Google Analytics 4 to measure page visits, engagement, successful enquiries and clicks on contact links. With your consent, Google may process cookie identifiers, pages visited, device and browser information, approximate location and interaction events. Our events contain no form entries, email addresses or phone numbers; we strip query strings and fragments from page URLs. The legal basis is consent under GDPR Article 6(1)(a). The analytics tag does not load before permission. Advertising personalisation and Google signals are disabled in this integration.',
			'Cookies and changing your choice' => 'pll_language remembers your language for up to one year. jg_consent stores your analytics choice, its time and the notice version for 180 days; it is necessary to respect your choice. After analytics acceptance, _ga and _ga_* identify the browser and session; this integration limits their lifetime to 180 days. The localStorage key jg_consent_changed briefly notifies other open tabs of changes and is immediately removed. Accept or reject analytics in the banner, and change your choice at any time through “Cookie settings” in the footer. Withdrawal stops future analytics and deletes this website’s Analytics cookies; it does not itself delete data already sent. Our Analytics integration does not run without JavaScript.',
			'Recipients and retention' => array(
				'We store your enquiry in the website’s WordPress database and send it to info@janoga.lv. Authorised JāņogaGO staff access this information to review your request and respond to you. The public website janogago.lv is hosted on a DigitalOcean server, and its enquiry emails are sent using Google Gmail. These providers may process data to operate the website and deliver email.',
				'The retention period for enquiry data depends on the time needed to handle your request and the related correspondence. If we enter into a contract, information about our cooperation may also be needed to perform the contract and meet applicable legal obligations. Technical logs and backups support website security and recovery. You can ask about the retention or deletion of your data by emailing info@janoga.lv.',
				'Google Analytics is currently disabled, and we do not collect analytics data. Once it is enabled, we will process analytics data only with your consent. Google Analytics is provided by Google Ireland Limited; Google LLC and servers outside the European Economic Area may also be involved in processing. Further information is available in Google’s data processing terms and its explanation of how it uses information from websites that use Google services.',
			),
			'Your rights' => 'Subject to applicable conditions, you can request access, correction, deletion, restriction and portability of your personal data, object to processing based on legitimate interests, and withdraw consent. Withdrawal does not affect the lawfulness of earlier processing. Contact info@janoga.lv. You may complain to Latvia’s Data State Inspectorate: https://www.dvi.gov.lv/.',
		),
	),
);
$ids = array();
foreach ( $content as $lang => $page ) {
	$id = absint( $options[ 'page_' . $lang ] ?? 0 );
	// A saved selection records author intent, including clearing or deleting a page.
	if ( array_key_exists( 'page_' . $lang, $options ) ) {
		if ( get_post_type( $id ) === 'page' && get_post_status( $id ) !== 'trash' ) { $ids[ $lang ] = $id; }
		continue;
	}
	if ( ! $id || ! get_post( $id ) ) {
		$existing = get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private', 'trash' ), 'meta_key' => '_jg_privacy_language', 'meta_value' => $lang, 'numberposts' => 1 ) );
		if ( $existing ) { $id = $existing[0]->ID; }
		else {
			$blocks = '';
			foreach ( $page['sections'] as $heading => $text ) {
				$blocks .= jg_block_heading( $heading, 2 );
				foreach ( (array) $text as $paragraph_text ) {
					$paragraph = jg_block_paragraph( $paragraph_text );
					$paragraph = str_replace( 'info@janoga.lv', '<a href="mailto:info@janoga.lv">info@janoga.lv</a>', $paragraph );
					$paragraph = str_replace( 'https://www.dvi.gov.lv/', '<a href="https://www.dvi.gov.lv/">https://www.dvi.gov.lv/</a>', $paragraph );
					$blocks .= $paragraph;
				}
				if ( is_array( $text ) ) {
					$label = $lang === 'en' ? 'Google data processing terms' : 'Google datu apstrādes noteikumi';
					$blocks .= jg_block( 'paragraph', array(), '<p><a href="https://business.safety.google/adsprocessorterms/">' . esc_html( $label ) . '</a></p>' );
				}
				if ( str_starts_with( $heading, 'Google Analytics' ) ) {
					$label = $lang === 'en' ? 'How Google uses information from websites that use its services' : 'Kā Google izmanto informāciju no vietnēm, kurās tiek lietoti tā pakalpojumi';
					$blocks .= jg_block( 'paragraph', array(), '<p><a href="https://policies.google.com/technologies/partner-sites">' . esc_html( $label ) . '</a></p>' );
				}
			}
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page['title'], 'post_name' => $page['slug'], 'post_content' => $blocks ), true );
			if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
			update_post_meta( $id, '_jg_privacy_language', $lang );
			if ( function_exists( 'pll_set_post_language' ) ) { pll_set_post_language( $id, $lang ); }
		}
	}
	$options[ 'page_' . $lang ] = $id;
	$ids[ $lang ] = $id;
}
if ( count( $ids ) === 2 && function_exists( 'pll_save_post_translations' ) ) { pll_save_post_translations( $ids ); }
update_option( 'jg_privacy', $options );
WP_CLI::success( 'Local privacy preview pages ready. Existing content and analytics settings preserved: ' . wp_json_encode( $ids ) );
