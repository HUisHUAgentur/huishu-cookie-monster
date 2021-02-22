<?php

defined( 'ABSPATH' ) or die( 'No no no!' );

function get_pages_for_cmb2(){
	$pages = get_pages();
	$return = array();
	if($pages){
		foreach($pages as $page){
			$return[$page->ID] = esc_attr($page->post_title);
		}
	}
	return $return;
}

class HUisHUCookieMonsterBackend{
	
	public function __construct(){
		add_action('plugins_loaded',array($this,'initialize_options'));
	}
	
	public function initialize_options(){
		if(defined('CMB2_LOADED')){
			//add_filter('huishu_framework_additional_options_pages',array($this,'huishu_cookie_monster_add_settings_page_to_huishu_framework'));
			add_action('cmb2_admin_init',array($this,'build_admin_options_menu'));
		} else {
			add_action('admin_init',array($this,'huishu_cookie_monster_register_settings'));
			add_action( 'admin_menu', array($this,'huishu_cookie_monster_register_options_page'));
		}
		if(!($types = HUisHUCookieMonsterOptions::get('cookie_types',array()))){
			$types = array();
		}		
		if(in_array('google_analytics',$types) && HUisHUCookieMonsterOptions::get('ga_tracking_codes',array())){
			if(HUisHUCookieMonsterOptions::get('ga_send_cf7_events',0)){
				add_filter( 'wpcf7_editor_panels', function($panels) {
						$panels['hcm_cf7'] = array( 
								'title' => __( 'Google Analytics Events', 'contact-form-7' ),
								'callback' => array($this,'huishu_imcf7i_wpcf7_editor_panel')
						);
						return $panels;
				}, 10, 1 );
				add_action('wpcf7_save_contact_form',array($this,'save_cf7_postmeta_for_google_analytics_events'),10,3);
			}
		}
	}
	
	public function save_cf7_postmeta_for_google_analytics_events($contact_form, $args, $context){
		if(isset($args['id'])){
			$id = $args['id'];
			update_post_meta($id,'_hcm_ga_event_category',$args['cf7_field_for_ga_event_category']);
			update_post_meta($id,'_hcm_ga_event_action',$args['cf7_field_for_ga_event_action']);
			update_post_meta($id,'_hcm_ga_event_label',$args['cf7_field_for_ga_event_label']);
		}
	}
	
	public function huishu_imcf7i_wpcf7_editor_panel($post){
		$event_category="";
		$event_action="";
		$event_label="";
		if(isset($_GET['post'])){
			$postid = $_GET['post'];
			$event_category = get_post_meta($postid,'_hcm_ga_event_category',true);
			$event_category = $event_category ? $event_category : 'Kontaktformular';
			$event_action = get_post_meta($postid,'_hcm_ga_event_action',true);
			$event_action = $event_action ? $event_action : 'Absenden';
			$event_label = get_post_meta($postid,'_hcm_ga_event_label',true);
			$event_label = $event_label ? $event_label : get_the_title($postid);
		}
		?>
		<h2>Google Analytics Event Details</h2>
		<label for="cf7_field_for_ga_event_category">
			Event Kategorie:
		</label>
		<input type="text" name="cf7_field_for_ga_event_category" id="cf7_field_for_ga_event_category" placeholder="Event Kategorie" value="<?php echo esc_attr($event_category); ?>" /><br />
		<label for="cf7_field_for_ga_event_action">
			Event Action:
		</label>
		<input type="text" name="cf7_field_for_ga_event_action" id="cf7_field_for_ga_event_action" placeholder="Event Action" value="<?php echo esc_attr($event_action); ?>" /><br />
		<label for="cf7_field_for_ga_event_label">
			Event Label:
		</label>
		<input type="text" name="cf7_field_for_ga_event_label" id="cf7_field_for_ga_event_label" placeholder="Event Label" value="<?php echo esc_attr($event_label); ?>" /><br />
		<?php
	}
	
	public function huishu_cookie_monster_register_settings(){
		
	}
	
	public function huishu_cookie_monster_register_options_page(){
		
	}
	
	public function build_admin_options_menu(){
		$cmb2_huishu_cookie_monster_options_page = new_cmb2_box( array(
			'id'           => 'huishu_cookie_monster_options_page',
			'title'        => esc_html__( 'Cookie-Banner Einstellungen', 'myprefix' ),
			'object_types' => array( 'options-page' ),
			'option_key'      => 'huishu_cookie_monster_options', // The option key and admin menu page slug.
			//'icon_url'        => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
			//'menu_title'      => esc_html__( 'Options', 'myprefix' ), // Falls back to 'title' (above).
			//'parent_slug'     => 'edit.php?post_type=cookies', // Make options page a submenu item of the themes menu.
			//'capability'       => 'edit_options', // Cap required to view options-page.
			//'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
			//'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
			//'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
			//'save_button'     => esc_html__( 'Save Theme Options', 'myprefix' ), // The text for the options-page save button. Defaults to 'Save'.
		));
		$fields = array(				
				array(
					'name'    => 'Impressumsseite',
					'desc'    => 'Bitte wählen Sie Ihre Impressums-Seite aus. Auf dieser Seite und der Datenschutzseite wird das Cookie-Banner nicht ausgegeben.',
					'id'      => 'dont_show_on_imprint',
					'type'    => 'select',
					'options_cb' => 'get_pages_for_cmb2',
					'show_option_none' => 'Bitte Seite auswählen',
				),
				
				array(
					'name'    => 'Tracker-Typen',
					'desc'    => 'Bitte markieren Sie die Arten von Trackern/Cookies, die Sie auf dieser Seite nutzen',
					'id'      => 'cookie_types',
					'type'    => 'multicheck',
					'options' => array(
						'google_analytics' => 'Google Analytics',
						'fb_pixel' => 'Facebook Pixel',
						'youtube' => 'Youtube-Einbindung (Achtung: muss per spezieller Funktion eingebunden werden)',
						'artetv' => 'ArteTV-Einbindung (Achtung: muss per spezieller Funktion eingebunden werden)',
						'vimeo' => 'Vimeo-Einbindung (Achtung: muss per spezieller Funktion eingebunden werden)',
						'etracker' => 'eTracker',
						'custom' => 'Benutzerdefiniert',
					),
				),

				array(
					'name'    => 'Externe Inhalte',
					'desc'    => 'Bitte markieren Sie die Arten von Externen Inhalten, die Sie auf dieser Seite nutzen (nur mit Lazy Loading kompatibel) (Experimentell)',
					'id'      => 'external_media_types',
					'type'    => 'multicheck',
					'options' => array(
						'google_maps' => 'Google Maps',
					),
				),
				
				array(
					'name'    => 'Speicherdauer',
					'desc'    => 'Wie lange soll der Cookie, der die Einstellungen sichert, im Browser des Besuchers gespeichert bleiben?',
					'id'      => 'cookie_settings_duration',
					'type'    => 'select',
					'default' => 'month',
					'options' => array(
						'day' => '1 Tag',
						'month' => '1 Monat',
						'year' => '1 Jahr',
					),
				),
				array(
					'name'    => 'Inhalt des Cookie Banners',
					'description' => 'Geben Sie hier den Text für das Cookie-Banner ein. {{privacy_page}} wird automatisch durch den Link zu Ihrer Datenschutzerklärungsseite ersetzt.',
					'id'      => 'cookie_banner_content',
					'type'    => 'wysiwyg',
					'default' => "<h3>Cookies</h3>\nWir nutzen Cookies für unseren Internetauftritt. Einige von ihnen sind für bestimmte Funktionen unabdingbar (essenziell).\nAndere Cookies helfen uns dagegen, insgesamt ein nutzerfreundlicheres und verbessertes Angebot der Webseite zu erstellen. Sie können alle Cookies bestätigen oder einzelnen Cookies per Schaltfläche die Zustimmung erteilen oder verweigern.\n\n{{COOKIES_SMALL_CHECKBOXES}}\n\n{{privacy_page}} | {{imprint_page}}",
					'options' => array(
						'media_buttons' => false, // show insert/upload button(s)
						'textarea_rows' => 5,
					),
				),				
			);
		foreach($fields as $field){
			$cmb2_huishu_cookie_monster_options_page->add_field($field);
		}
		
		if(!($types = HUisHUCookieMonsterOptions::get('cookie_types',array()))){
			$types = array();
		}
		
		
		if(in_array('google_analytics',$types)){
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'Google Analytics',
						'id' => 'title_3',
						'type' => 'title',
					)
			);
			
			/*$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker bei OK im Cookie-Banner aktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert". Wenn im Cookie-Banner auf "OK" geklickt wird, gilt dieser Tracker als "Zugestimmt".',
				'id'   => 'ga_banner_preactivated',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker im Cookie-Dialog voraktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert" im Cookie-Dialog ein. Wenn der Cookie-Dialog angezeigt wird, ist dieser Tracker voraktiviert.',
				'id'   => 'ga_dialog_preactivated',
				'type' => 'checkbox',
			) );*/
			
			$cmb2_huishu_cookie_monster_options_page->add_field(array(
				'name' 	=> 'anonymize_ip aktivieren?',
				'desc'    => 'Standardmäßig erfasst Google Analytics die IP-Adressen der Besucher Ihrer Website. Bei Aktivierung dieses Häkchens wird das "anonymize IP"-Flag gesetzt,'
							.' damit die IP-Adresse nur anonymisiert übertragen und gespeichert wird. <br /><strong>ACHTUNG:</strong> Bei Deaktivierung dieser Funktion verstößt die Nutzung von '
							.'Google Analytics gegen geltendes Datenschutzrecht!',
				'type'    => 'checkbox',
				'id'      => 'ga_use_anonymize',
			));
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Ereignisse bei Kontaktformularen senden?',
				'desc' => 'Bei Aktivierung werden bei erfolgreichem Versand von Contact Form 7 Kontaktformularen ein "Ereignis" an Google Analytics gesendet.',
				'id'   => 'ga_send_cf7_events',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Anonyme Ereignisse senden, wenn Google Analytics abgelehnt wurde?',
				'desc' => 'Bei Aktivierung werden bei erfolgreichem Versand von Contact Form 7 Kontaktformularen ein anonymes "Ereignis" an Google Analytics gesendet, selbst wenn die Nutzung von Google Analytics abgelehnt wurde.',
				'id'   => 'ga_send_anonymous_cf7_events',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field(array(
				'name'    => 'Google Analytics Code(s)',
				'id'      => 'ga_tracking_codes',
				'type'    => 'text',
				'desc'    => 'Geben Sie hier den Tracking-Code ein, der Ihnen bei Google Analytics angezeigt wird.',
				'attributes' => array(
									  'placeholder' => 'UA-XXXXXXXXX-XX',
									  ),
				'repeatable' => true,
			)  );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'ga_cookie_caption',
				'default' => 'Google Analytics',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'ga_cookie_vendor',
				'default' => 'Google LLC',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'ga_cookie_description',
				'type'    => 'wysiwyg',
				'default' => 'Cookie von Google für Website-Analysen. Erzeugt statistische Daten darüber, wie der Besucher die Website nutzt.',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'ga_cookie_names',
				'default' => '_ga,_gat,_gid',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'ga_cookie_runtime',
				'default' => '2 Jahre',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'ga_cookie_privacy_link',
				'type' => 'text_url',
				'default' => 'https://policies.google.com/privacy',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
		}
		
		
		if(in_array('fb_pixel',$types)){
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'Facebook Pixel',
						'id' => 'title_4',
						'type' => 'title',
					)
			);
			
			/*$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker bei OK im Cookie-Banner aktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert". Wenn im Cookie-Banner auf "OK" geklickt wird, gilt dieser Tracker als "Zugestimmt".',
				'id'   => 'fb_banner_preactivated',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker im Cookie-Dialog voraktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert" im Cookie-Dialog ein. Wenn der Cookie-Dialog angezeigt wird, ist dieser Tracker voraktiviert.',
				'id'   => 'fb_dialog_preactivated',
				'type' => 'checkbox',
			) );*/
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Ereignisse bei Kontaktformularen senden?',
				'desc' => 'Bei Aktivierung werden bei erfolgreichem Versand von Contact Form 7 Kontaktformularen ein "Ereignis" an Facebook gesendet.',
				'id'   => 'fb_send_cf7_events',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field(array(
				'name'    => 'Facebook Pixel ID',
				'id'      => 'fb_pixel_ids',
				'type'    => 'text',
				'desc'    => "Geben Sie hier die Pixel-ID(s) ein, die Ihnen bei Facebook angezeigt wird (fbq('init','<strong>1234567890</strong>');)",
				'attributes' => array(
									  'placeholder' => '1234567890',
									  ),
				'repeatable' => true,
			)  );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'fb_cookie_caption',
				'default' => 'Facebook Pixel',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'fb_cookie_vendor',
				'default' => 'Facebook Ireland Limited',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'fb_cookie_description',
				'type'    => 'wysiwyg',
				'default' => 'Cookie von Facebook, das für Website-Analysen, Ad-Targeting und Anzeigenmessung verwendet wird.',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'fb_cookie_names',
				'default' => '_fbp,act,c_user,datr,fr,m_pixel_ration,pl,presence,sb,spin,wd,xs',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'fb_cookie_runtime',
				'default' => 'Sitzung / 1 Jahr',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'fb_cookie_privacy_link',
				'type' => 'text_url',
				'default' => 'https://www.facebook.com/policies/cookies',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
		}

		if(in_array('youtube',$types)){
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'Youtube Video Einbindungen',
						'description' => 'Youtube-Videos müssen speziell eingebunden werden.',
						'id' => 'title_yt_6',
						'type' => 'title',
					)
			);
			
			/*$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker bei OK im Cookie-Banner aktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert". Wenn im Cookie-Banner auf "OK" geklickt wird, gilt dieser Tracker als "Zugestimmt".',
				'id'   => 'fb_banner_preactivated',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker im Cookie-Dialog voraktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert" im Cookie-Dialog ein. Wenn der Cookie-Dialog angezeigt wird, ist dieser Tracker voraktiviert.',
				'id'   => 'fb_dialog_preactivated',
				'type' => 'checkbox',
			) );*/
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'yt_cookie_caption',
				'default' => 'YouTube Videos',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'yt_cookie_vendor',
				'default' => 'YouTube LLC, 901 Cherry Ave., San Bruno, CA 94066, USA.',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'yt_cookie_description',
				'type'    => 'wysiwyg',
				'default' => 'Einbindung von YouTube-Videos aktivieren. Beim aktivieren von YouTube-Videos werden die Videodaten per Iframe eingebunden.',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'yt_cookie_names',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'yt_cookie_runtime',
				'default' => 'Sitzung / 1 Jahr',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'yt_cookie_privacy_link',
				'type' => 'text_url',
				'default' => 'https://policies.google.com/privacy',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
		}

		if(in_array('artetv',$types)){
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'ArteTV Video Einbindungen',
						'description' => 'ArteTV-Videos müssen speziell eingebunden werden.',
						'id' => 'title_artetv_6',
						'type' => 'title',
					)
			);
			
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'artetv_cookie_caption',
				'default' => 'ArteTV Einbindungen',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'artetv_cookie_vendor',
				'default' => 'YouTube LLC, 901 Cherry Ave., San Bruno, CA 94066, USA.',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'artetv_cookie_description',
				'type'    => 'wysiwyg',
				'default' => 'Einbindung von artetv-Videos aktivieren. Beim aktivieren von artetv-Videos werden die Videodaten per Iframe eingebunden.',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'artetv_cookie_names',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'artetv_cookie_runtime',
				'default' => 'Sitzung / 1 Jahr',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'artetv_cookie_privacy_link',
				'type' => 'text_url',
				'default' => 'https://policies.google.com/privacy',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
		}

		if(in_array('vimeo',$types)){
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'Vimeo Video Einbindungen',
						'description' => 'Vimeo-Videos müssen speziell eingebunden werden.',
						'id' => 'title_vimeo_7',
						'type' => 'title',
					)
			);
			
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'vimeo_cookie_caption',
				'default' => 'vimeo Einbindungen',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'vimeo_cookie_vendor',
				'default' => 'YouTube LLC, 901 Cherry Ave., San Bruno, CA 94066, USA.',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'vimeo_cookie_description',
				'type'    => 'wysiwyg',
				'default' => 'Einbindung von artetv-Videos aktivieren. Beim aktivieren von artetv-Videos werden die Videodaten per Iframe eingebunden.',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'vimeo_cookie_names',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'vimeo_cookie_runtime',
				'default' => 'Sitzung / 1 Jahr',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'vimeo_cookie_privacy_link',
				'type' => 'text_url',
				'default' => 'https://vimeo.com/privacy',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
		}
		
		if(in_array('etracker',$types)){
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'eTracker',
						'id' => 'title_5',
						'type' => 'title',
					)
			);
			
			/*$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker bei OK im Cookie-Banner aktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert". Wenn im Cookie-Banner auf "OK" geklickt wird, gilt dieser Tracker als "Zugestimmt".',
				'id'   => 'etracker_banner_preactivated',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Tracker im Cookie-Dialog voraktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert" im Cookie-Dialog ein. Wenn der Cookie-Dialog angezeigt wird, ist dieser Tracker voraktiviert.',
				'id'   => 'etracker_dialog_preactivated',
				'type' => 'checkbox',
			) );*/
			
			
			$cmb2_huishu_cookie_monster_options_page->add_field(array(
				'name'    => 'eTracker Account-Schlüssel',
				'id'      => 'etracker_account_key',
				'type'    => 'text_small',
				'desc'    => 'Geben Sie hier den Account-Schlüssel 1 ein, wie er Ihnen in eTracker unter Account-Info > Account-Einstellungen > Account-Schlüssel angezeigt wird.',
				'attributes' => array(
									  'placeholder' => 'Account-Schlüssel',
									  ),
			)  );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => 'Do Not Track berücksichtigen?',
				'desc' => 'Bei Aktivierung werden Besucher nicht getrackt, die in Ihrem Browser das "Do-Not-Track"-Feature aktiviert haben. Diese Option sollten Sie aktiviert lassen.',
				'id'   => 'etracker_respect_dnt',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'etracker_cookie_caption',
				'default' => 'etracker',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'etracker_cookie_vendor',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'etracker_cookie_description',
				'type'    => 'wysiwyg',
				'default' => '',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'etracker_cookie_names',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'etracker_cookie_runtime',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_field( array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'etracker_cookie_privacy_link',
				'type' => 'text_url',
				'default' => 'https://www.etracker.com/datenschutz/',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
		}
		
		if(in_array('custom',$types)){
			
			$cmb2_huishu_cookie_monster_options_page->add_field(
				array(
						'name' => 'Benutzerdefinierte Tracker/Cookies',
						'id' => 'title_6',
						'type' => 'title',
					)
			);
			
			$cmb2_custom_group_field_id = $cmb2_huishu_cookie_monster_options_page->add_field( array(
				'id'          => 'custom_cookies',
				'type'        => 'group',
				'description' => __( '', 'cmb2' ),
				'options'     => array(
					'group_title'       => __( 'Tracker/Cookie {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
					'add_button'        => __( 'Tracker/Cookie hinzufügen', 'cmb2' ),
					'remove_button'     => __( 'Tracker/Cookie entfernen', 'cmb2' ),
					'sortable'          => true,
					// 'closed'         => true, // true to have the groups closed by default
					'remove_confirm' => esc_html__( 'Sind sie sicher?', 'cmb2' ), // Performs confirmation before removing group.
				),
			) );
			
			/*$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name' => 'Tracker bei OK im Cookie-Banner aktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert". Wenn im Cookie-Banner auf "OK" geklickt wird, gilt dieser Tracker als "Zugestimmt".',
				'id'   => 'banner_preactivated',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name' => 'Tracker im Cookie-Dialog voraktivieren?',
				'desc' => 'Stellt diesen Tracker als "Voraktiviert" im Cookie-Dialog ein. Wenn der Cookie-Dialog angezeigt wird, ist dieser Tracker voraktiviert.',
				'id'   => 'dialog_preactivated',
				'type' => 'checkbox',
			) );*/
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name'    => 'Cookie Bezeichnung',
				'desc'    => 'Nennen Sie hier die Bezeichnung des Cookies, so wie sie als Benennung angezeigt werden soll.',
				'id'      => 'caption',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name' => 'Erforderlicher Cookie?',
				'desc' => 'Bei Aktivierung dieser Option wird der Cookie als zwingend erforderlich für die Funktion der Website gekennzeichnet. Er ist dann nicht vom Besucher deaktivierbar.',
				'id'   => 'use_as_necessary',
				'type' => 'checkbox',
				//'show_on_cb' => 'cmb_only_show_for_custom',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name' => 'Script für den Cookie auf einzelnen Seiten',
				'desc' => 'Soll auf den einzelnen Inhaltsseiten ein manuelles Zusatzfeld für Skripts dieses Trackers angezeigt werden?',
				'id' => 'custom_code_on_posts',
				'type' => 'checkbox',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name' => 'Script für den Cookie',
				'desc' => 'Geben Sie hier den Code ein, der beim Akzeptieren des Cookies ausgeführt werden soll. Bitte tragen Sie ausschließlich den Javascript-Code ohne "&lt;script&gt;"-Tags ein. "&lt;noscript&gt;"-Anweisungen werden derzeit nicht unterstützt.',
				'id' => 'custom_code_on_accept',
				'type' => 'textarea_code',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name'    => 'Anbieter',
				'desc'    => 'Nennen Sie hier den Anbieter des Cookies/Trackers.',
				'id'      => 'vendor',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name'    => 'Kurzbeschreibung/Zweck',
				'desc'    => 'Geben Sie hier eine kurze Beschreibung für den Tracker und was er tut / wofür er benötigt wird ein.',
				'id'      => 'description',
				'type'    => 'wysiwyg',
				'default' => '',
				'options' => array(	    'wpautop' => true, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 5, // rows="..."
				),
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name'    => 'Cookie-Name',
				'desc'    => 'Geben Sie hier den/die Namen des/der Cookies ein, so wie sie im Browser gespeichert werden.',
				'id'      => 'names',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name'    => 'Cookie-Laufzeit',
				'desc'    => 'Geben Sie hier ein, wie lange der Cookie standardmäßig im Browser des Besuchers gespeichert bleibt.',
				'id'      => 'runtime',
				'default' => '',
				'type'    => 'text',
			) );
			
			$cmb2_huishu_cookie_monster_options_page->add_group_field($cmb2_custom_group_field_id, array(
				'name' => __( 'Link zur Datenschutzerklärung', 'cmb2' ),
				'id'   => 'privacy_link',
				'type' => 'text_url',
				'default' => '',
				'protocols' => array( 'http', 'https' ), // Array of allowed protocols
			) );
			
			$customs = HUisHUCookieMonsterOptions::get('custom_cookies',array());
			$dosome = false;
			if($customs){
				foreach($customs as $custom){
					if(isset($custom['custom_code_on_posts']) && ($custom['custom_code_on_posts'] == 'on')){
						$dosome = true;
					}
				}
			}
			if($dosome){
				$cmb_extrascript = new_cmb2_box( array(
					'id'            => 'cookie_custom_skript_metabox',
					'title'         => __( 'Cookiescript-Daten', 'cmb2' ),
					'object_types'  => apply_filters('huishu_cookie_monster_custom_cookie_show_metabox_on_post_types',array( 'page','post' )), // Post type
					'context'       => 'normal',
					//'priority'      => '',
					'show_names'    => true, // Show field names on the left
					// 'cmb_styles' => false, // false to disable the CMB stylesheet
					// 'closed'     => true, // Keep the metabox closed by default
				) );
				foreach($customs as $custom){
					if(isset($custom['custom_code_on_posts']) && ($custom['custom_code_on_posts'] == 'on')){
						$cmb_extrascript->add_field(array(
							'name' 	=> 'Cookie Code für '.$custom['caption'],
							'desc'    => 'Code für diese Unterseite',
							'type'    => 'textarea',
							'id'      => '_hcm_custom_cookie_post_for_'.str_replace('-','_',sanitize_title($custom['caption'])),
							'sanitization_cb' => false,
						));
					}
				}
			}	
		}
		
	}
}

function cmb_only_show_for_google_analytics( $cmb ) {
	$status = get_post_meta( $cmb->object_id(), '_hcm_cookie_type', 1 );
	return 'google_analytics' === $status;
}

function cmb_only_show_for_facebook_pixel( $cmb ) {
	$status = get_post_meta( $cmb->object_id(), '_hcm_cookie_type', 1 );
	return 'fb_pixel' === $status;
}

function cmb_only_show_for_etracker( $cmb ) {
	$status = get_post_meta( $cmb->object_id(), '_hcm_cookie_type', 1 );
	return 'etracker' === $status;
}

function cmb_only_show_for_custom( $cmb ) {
	$status = get_post_meta( $cmb->object_id(), '_hcm_cookie_type', 1 );
	return 'custom' === $status;
}

function cmb2_set_checkbox_default_for_new_post( $default ) {
	return isset( $_GET['post'] ) ? '' : ( $default ? (string) $default : '' );
}
