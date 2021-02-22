<?php
/*
Plugin Name: HUisHU Cookie Monster
Description: Die vollständige Breitband Cookie Lösung für DSGVO, e-privacy etc. NOM NOM NOM NOM
Version: 1.5
Author: HUisHU
Author URI: https://www.huishu-agentur.de
*/

defined( 'ABSPATH' ) or die( 'No no no!' );

function hcm_get_version(){
	return 1.5;
}


/**
 * Use Plugin Update Checker to check for Updates on Github
 */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/HUisHUAgentur/huishu-cookie-monster/',
	__FILE__,
	'huishu-cookie-monster'
);

function hcm_plugin_activate() {
	if($options = get_option('huishu_cookie_master_options')){
		update_option('huishu_cookie_monster_options',$options);
		delete_option('huishu_cookie_master_options');
	}
	if($ga_event_sender = get_option('huishu_ga_ev_sender_google_analytics_tracking_id',null)){
		if(!($hcm_options = get_option('huishu_cookie_monster_options',NULL))){
			$hcm_options = array (
				'cookie_types' => 
				array (
				  0 => 'google_analytics',
				),
				'cookie_settings_duration' => 'month',
				'cookie_banner_content' => '<h3>Cookies</h3>
Wir nutzen Cookies für unseren Internetauftritt. Einige von ihnen sind für bestimmte Funktionen unabdingbar (essenziell). 
Andere Cookies helfen uns dagegen, insgesamt ein nutzerfreundlicheres und verbessertes Angebot der Webseite zu erstellen. Sie können alle Cookies bestätigen oder einzelnen Cookies per Schaltfläche die Zustimmung erteilen oder verweigern.

{{COOKIES_SMALL_CHECKBOXES}}

{{privacy_page}} | {{imprint_page}}',
				'ga_use_anonymize' => 'on',
				'ga_send_cf7_events' => 'on',
				'ga_send_anonymous_cf7_events' => 'on',
				'ga_cookie_caption' => 'Google Analytics',
				'ga_cookie_vendor' => 'Google LLC',
				'ga_cookie_description' => 'Cookie von Google für Website-Analysen. Erzeugt statistische Daten darüber, wie der Besucher die Website nutzt.',
				'ga_cookie_names' => '_ga,_gat,_gid',
				'ga_cookie_runtime' => '2 Jahre',
				'ga_cookie_privacy_link' => 'https://policies.google.com/privacy',
				'fb_send_cf7_events' => 'on',
				'fb_cookie_caption' => 'Facebook Pixel',
				'fb_cookie_vendor' => 'Facebook Ireland Limited',
				'fb_cookie_description' => 'Cookie von Facebook, das für Website-Analysen, Ad-Targeting und Anzeigenmessung verwendet wird.',
				'fb_cookie_names' => '_fbp,act,c_user,datr,fr,m_pixel_ration,pl,presence,sb,spin,wd,xs',
				'fb_cookie_runtime' => 'Sitzung / 1 Jahr',
				'fb_cookie_privacy_link' => 'https://www.facebook.com/policies/cookies',
				'etracker_cookie_caption' => 'etracker',
				'etracker_cookie_privacy_link' => 'https://www.etracker.com/datenschutz/',
				'custom_cookies' => 
				array (
				),
				'ga_tracking_codes' => 
				array (
				  0 => $ga_event_sender,
				),
			  );
		}
		delete_option('huishu_ga_ev_sender_google_analytics_tracking_id');
	}
}
register_activation_hook( __FILE__, 'hcm_plugin_activate' );

/**
 * Options Helper Class
 */
require plugin_dir_path( __FILE__ ).'inc/huishu-cookie-monster-options.php';

/**
 * Backend Settings
 */
require plugin_dir_path( __FILE__ ).'inc/huishu-cookie-monster-backend.php';

/**
 * Frontend Interface
 */
require plugin_dir_path( __FILE__ ).'inc/huishu-cookie-monster-frontend.php';

$cookiemonsterfrontend = new HUisHUCookieMonsterFrontend();
$cookiemonsterbackend = new HUisHUCookieMonsterBackend();
