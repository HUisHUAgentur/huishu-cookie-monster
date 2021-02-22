<?php

defined( 'ABSPATH' ) or die( 'No no no!' );

class HUisHUCookieMonsterFrontend{
	
	public function __construct(){
		add_action('wp_footer',array($this,'insert_cookie_monster_banner_into_footer'));
		add_action('wp_enqueue_scripts',array($this,'enqueue_scripts_and_styles'));
		add_shortcode('change_cookie_settings',array($this,'change_cookie_settings_shortcode_handler'));
		add_action( 'wp_ajax_hcm_send_anonymous_event_ga', array($this,'hcm_send_anonymous_event_ga') );
		add_action( 'wp_ajax_nopriv_hcm_send_anonymous_event_ga', array($this,'hcm_send_anonymous_event_ga') );
	}
	
	public function change_cookie_settings_shortcode_handler($atts = array(),$content=""){
		if($content){
			return '<a href="#" class="hcm_cookie_show_details">'.$content.'</a>';
		} else {
			return '<a href="#" class="hcm_cookie_show_details">Cookie-Einstellungen anpassen.</a>';
		}
	}
	
	public function enqueue_scripts_and_styles(){
		wp_enqueue_style('huishu-cookie-monster-frontend-styles',plugins_url( '../huishu-cookie-monster-frontend-styles.css', __FILE__ ),array(),filemtime(plugin_dir_path(__FILE__).'../huishu-cookie-monster-frontend-styles.css'));
		wp_enqueue_script('js-cookie',plugins_url( '../js/js-cookie.js', __FILE__ ),array(),filemtime(plugin_dir_path(__FILE__).'../js/js-cookie.js'),true);
		wp_register_script('huishu-cookie-monster-frontend-script',plugins_url( '../js/huishu-cookie-monster-frontend.js', __FILE__ ),array('js-cookie'),filemtime(plugin_dir_path(__FILE__).'../js/huishu-cookie-monster-frontend.js'),true);
		$localize_array = $this->get_localize_cookie_options();
		wp_localize_script('huishu-cookie-monster-frontend-script','hcm_cookie_options',$localize_array);
		wp_enqueue_script('huishu-cookie-monster-frontend-script');
	}
	
	private function get_active_cookie_scripts_grouped(){
		$active_cookie_scripts = array();
		$gaoptions = array();
		$gaoptions['banner_preactivated'] = HUisHUCookieMonsterOptions::get('ga_banner_preactivated',0);
		$gaoptions['dialog_preactivated'] = HUisHUCookieMonsterOptions::get('ga_dialog_preactivated',0);
		$gaoptions['use_anonymize'] = HUisHUCookieMonsterOptions::get('ga_use_anonymize',false);
		$gaoptions['send_cf7_events'] = HUisHUCookieMonsterOptions::get('ga_send_cf7_events',0);
		$gaoptions['send_anonymous_cf7_events'] = HUisHUCookieMonsterOptions::get('ga_send_anonymous_cf7_events',0);
		$ga_tracking_codes = HUisHUCookieMonsterOptions::get('ga_tracking_codes',array());
		$ga_tracking_codes = apply_filters('huishu_cookie_monster_google_analytics_tracking_codes',$ga_tracking_codes);
		$gaoptions['tracking_codes'] = $ga_tracking_codes;
		$gaoptions['labels'] = array();
		$gaoptions['labels']['caption'] = HUisHUCookieMonsterOptions::get('ga_cookie_caption',"");
		$gaoptions['labels']['vendor'] = HUisHUCookieMonsterOptions::get('ga_cookie_vendor',"");
		$gaoptions['labels']['description'] = wpautop(HUisHUCookieMonsterOptions::get('ga_cookie_description',""));
		$gaoptions['labels']['cookie_names'] = HUisHUCookieMonsterOptions::get('ga_cookie_names',"");
		$gaoptions['labels']['runtime'] = HUisHUCookieMonsterOptions::get('ga_cookie_runtime',"");
		$gaoptions['labels']['privacy_link'] = HUisHUCookieMonsterOptions::get('ga_cookie_privacy_link',"");
		if(isset($gaoptions['tracking_codes']) && !empty($gaoptions['tracking_codes'])){
			$active_cookie_scripts['google_analytics'] = apply_filters('huishu_cookie_monster_google_analytics_cookie_options',$gaoptions);
		}
		$fboptions = array();
		$fboptions['banner_preactivated'] = HUisHUCookieMonsterOptions::get('fb_banner_preactivated',0);
		$fboptions['dialog_preactivated'] = HUisHUCookieMonsterOptions::get('fb_dialog_preactivated',0);
		$fboptions['send_cf7_events'] = HUisHUCookieMonsterOptions::get('fb_send_cf7_events',0);
		$fb_tracking_codes = HUisHUCookieMonsterOptions::get('fb_pixel_ids',array());
		$fb_tracking_codes = apply_filters('huishu_cookie_monster_facebook_pixel_codes',$fb_tracking_codes);
		$fboptions['tracking_codes'] = $fb_tracking_codes;
		$fboptions['labels'] = array();
		$fboptions['labels']['caption'] = HUisHUCookieMonsterOptions::get('fb_cookie_caption',"");
		$fboptions['labels']['vendor'] = HUisHUCookieMonsterOptions::get('fb_cookie_vendor',"");
		$fboptions['labels']['description'] = wpautop(HUisHUCookieMonsterOptions::get('fb_cookie_description',""));
		$fboptions['labels']['cookie_names'] = HUisHUCookieMonsterOptions::get('fb_cookie_names',"");
		$fboptions['labels']['runtime'] = HUisHUCookieMonsterOptions::get('fb_cookie_runtime',"");
		$fboptions['labels']['privacy_link'] = HUisHUCookieMonsterOptions::get('fb_cookie_privacy_link',"");
		if(isset($fboptions['tracking_codes']) && !empty($fboptions['tracking_codes'])){
			$active_cookie_scripts['fb_pixel'] = $fboptions;
		}

		$ytoptions = array();
		$ytoptions['banner_preactivated'] = HUisHUCookieMonsterOptions::get('yt_banner_preactivated',0);
		$ytoptions['dialog_preactivated'] = HUisHUCookieMonsterOptions::get('yt_dialog_preactivated',0);
		$ytoptions['labels'] = array();
		$ytoptions['labels']['caption'] = HUisHUCookieMonsterOptions::get('yt_cookie_caption',"");
		$ytoptions['labels']['vendor'] = HUisHUCookieMonsterOptions::get('yt_cookie_vendor',"");
		$ytoptions['labels']['description'] = wpautop(HUisHUCookieMonsterOptions::get('yt_cookie_description',""));
		$ytoptions['labels']['cookie_names'] = HUisHUCookieMonsterOptions::get('yt_cookie_names',"");
		$ytoptions['labels']['runtime'] = HUisHUCookieMonsterOptions::get('yt_cookie_runtime',"");
		$ytoptions['labels']['privacy_link'] = HUisHUCookieMonsterOptions::get('yt_cookie_privacy_link',"");
		$active_cookie_scripts['youtube'] = $ytoptions;

		$arteoptions = array();
		$arteoptions['banner_preactivated'] = HUisHUCookieMonsterOptions::get('artetv_banner_preactivated',0);
		$arteoptions['dialog_preactivated'] = HUisHUCookieMonsterOptions::get('artetv_dialog_preactivated',0);
		$arteoptions['labels'] = array();
		$arteoptions['labels']['caption'] = HUisHUCookieMonsterOptions::get('artetv_cookie_caption',"");
		$arteoptions['labels']['vendor'] = HUisHUCookieMonsterOptions::get('artetv_cookie_vendor',"");
		$arteoptions['labels']['description'] = wpautop(HUisHUCookieMonsterOptions::get('artetv_cookie_description',""));
		$arteoptions['labels']['cookie_names'] = HUisHUCookieMonsterOptions::get('artetv_cookie_names',"");
		$arteoptions['labels']['runtime'] = HUisHUCookieMonsterOptions::get('artetv_cookie_runtime',"");
		$arteoptions['labels']['privacy_link'] = HUisHUCookieMonsterOptions::get('artetv_cookie_privacy_link',"");
		$active_cookie_scripts['artetv'] = $arteoptions;

		$vimeooptions = array();
		$vimeooptions['banner_preactivated'] = HUisHUCookieMonsterOptions::get('vimeo_banner_preactivated',0);
		$vimeooptions['dialog_preactivated'] = HUisHUCookieMonsterOptions::get('vimeo_dialog_preactivated',0);
		$vimeooptions['labels'] = array();
		$vimeooptions['labels']['caption'] = HUisHUCookieMonsterOptions::get('vimeo_cookie_caption',"");
		$vimeooptions['labels']['vendor'] = HUisHUCookieMonsterOptions::get('vimeo_cookie_vendor',"");
		$vimeooptions['labels']['description'] = wpautop(HUisHUCookieMonsterOptions::get('vimeo_cookie_description',""));
		$vimeooptions['labels']['cookie_names'] = HUisHUCookieMonsterOptions::get('vimeo_cookie_names',"");
		$vimeooptions['labels']['runtime'] = HUisHUCookieMonsterOptions::get('vimeo_cookie_runtime',"");
		$vimeooptions['labels']['privacy_link'] = HUisHUCookieMonsterOptions::get('vimeo_cookie_privacy_link',"");
		$active_cookie_scripts['vimeo'] = $vimeooptions;

		$etracker = array();
		$etracker['banner_preactivated'] = HUisHUCookieMonsterOptions::get('etracker_banner_preactivated',0);
		$etracker['dialog_preactivated'] = HUisHUCookieMonsterOptions::get('etracker_dialog_preactivated',0);
		$etracker['respect_dnt'] = HUisHUCookieMonsterOptions::get('etracker_respect_dnt',0);
		$etracker['tracking_codes'] = HUisHUCookieMonsterOptions::get('etracker_account_key',array());
		$etracker['labels'] = array();
		$etracker['labels']['caption'] = HUisHUCookieMonsterOptions::get('etracker_cookie_caption',"");
		$etracker['labels']['vendor'] = HUisHUCookieMonsterOptions::get('etracker_cookie_vendor',"");
		$etracker['labels']['description'] = wpautop(HUisHUCookieMonsterOptions::get('etracker_cookie_description',""));
		$etracker['labels']['cookie_names'] = HUisHUCookieMonsterOptions::get('etracker_cookie_names',"");
		$etracker['labels']['runtime'] = HUisHUCookieMonsterOptions::get('etracker_cookie_runtime',"");
		$etracker['labels']['privacy_link'] = HUisHUCookieMonsterOptions::get('etracker_cookie_privacy_link',"");
		if(isset($etracker['tracking_codes']) && !empty($etracker['tracking_codes'])){
			$active_cookie_scripts['etracker'] = $etracker;
		}
		$customcodes = array();
		$customoptions = HUisHUCookieMonsterOptions::get('custom_cookies',array());
		if($customoptions){
			foreach($customoptions as $custom_cookie){
				$new_custom = array();
				$new_custom['banner_preactivated'] = (isset($custom_cookie['banner_preactivated']) && !empty($custom_cookie['banner_preactivated'])) ? $custom_cookie['banner_preactivated'] : false;
				$new_custom['dialog_preactivated'] = (isset($custom_cookie['dialog_preactivated']) && !empty($custom_cookie['dialog_preactivated'])) ? $custom_cookie['dialog_preactivated'] : false;
				$new_custom['code_on_accept'] = (isset($custom_cookie['custom_code_on_accept']) && !empty($custom_cookie['custom_code_on_accept'])) ? $custom_cookie['custom_code_on_accept'] : false;
				$new_custom['use_as_necessary'] = (isset($custom_cookie['use_as_necessary']) && !empty($custom_cookie['use_as_necessary'])) ? $custom_cookie['use_as_necessary'] : false;
				$new_custom['labels'] = array();
				$new_custom['labels']['caption'] = (isset($custom_cookie['caption']) && !empty($custom_cookie['caption'])) ? $custom_cookie['caption'] : "";
				$new_custom['labels']['vendor'] = (isset($custom_cookie['vendor']) && !empty($custom_cookie['vendor'])) ? $custom_cookie['vendor'] : "";
				$new_custom['labels']['description'] = (isset($custom_cookie['description']) && !empty($custom_cookie['description'])) ? $custom_cookie['description'] : "";
				$new_custom['labels']['cookie_names'] = (isset($custom_cookie['names']) && !empty($custom_cookie['names'])) ? $custom_cookie['names'] : "";
				$new_custom['labels']['runtime'] = (isset($custom_cookie['runtime']) && !empty($custom_cookie['runtime'])) ? $custom_cookie['runtime'] : "";
				$new_custom['labels']['privacy_link'] = (isset($custom_cookie['privacy_link']) && !empty($custom_cookie['privacy_link'])) ? $custom_cookie['privacy_link'] : "";
				$customcodes[] = $new_custom;
			}
		}
		if(!empty($customcodes)){
			$active_cookie_scripts['custom'] = $customcodes;
		}
		$return_cookie_scripts = array();
		$active_types = HUisHUCookieMonsterOptions::get('cookie_types',array());
		foreach($active_cookie_scripts as $type => $cookie){
			if(in_array($type,$active_types)){
				$return_cookie_scripts[$type] = $cookie;
			}
		}
		return apply_filters('huishu_cookie_monster_grouped_cookie_scripts',$return_cookie_scripts);
	}
	
	private function get_active_cookie_scripts(){
		$cookies = $this->get_active_cookie_scripts_grouped();
		$cf7events = false;
		$cf7_types = array();
		if($cookies){
			?>
			<script>
					<?php
					if(isset($cookies['google_analytics']) && !empty($cookies['google_analytics'])){
						if($cookies['google_analytics']['send_cf7_events']){
							$cf7events = true;
							$cf7_types[] = 'google_analytics';
						}
						$first_ga_cookie = reset($cookies['google_analytics']['tracking_codes']);
						?>
						document.addEventListener("hcm_google_analytics_accepted", function(){
							var hcm_ga_script = document.createElement("script");
							hcm_ga_script.type = "text/javascript";
							hcm_ga_script.setAttribute("async", "true");
							hcm_ga_script.setAttribute("src", "https://www.googletagmanager.com/gtag/js?id=<?php echo $first_ga_cookie; ?>");
							document.head.appendChild(hcm_ga_script);
							window.dataLayer = window.dataLayer || [];
							window.gtag = function(){window.dataLayer.push(arguments);};
							window.gtag('js', new Date());
							<?php
							$gaconfig = array();
							$anonmode = $cookies['google_analytics']['use_anonymize'] ? true : false;
							$gaconfig['send_page_view'] = false;
							if($anonmode){
								$gaconfig['anonymize_ip'] = true;
							}
							//$gaconfig = apply_filters('hcm_google_analytics_config',$gaconfig);
							foreach($cookies['google_analytics']['tracking_codes'] as $gacookie){
								?>
								window.gtag('config','<?php echo $gacookie ?>',<?php echo json_encode(apply_filters('hcm_google_analytics_config',$gaconfig,$gacookie)); ?>);
								window.gtag('event', 'page_view', {
									'event_callback': function(){
										hcmSendBrowserAgnosticEvent(document,'hcm_google_analytics_pageview_sent');
									}
								});
								<?php
							}
							?>
						});
						<?php
					}
					if(isset($cookies['fb_pixel']) && !empty($cookies['fb_pixel'])){
						if($cookies['fb_pixel']['send_cf7_events']){
							$cf7events = true;
							$cf7_types[] = 'fb_pixel';
						}
						?>
						document.addEventListener("hcm_fb_pixel_accepted", function(){
							(function (f, b, e, v, n, t, s) {
								if (f.fbq) return; n = f.fbq = function () {
										n.callMethod ?
										n.callMethod.apply(n, arguments) : n.queue.push(arguments)
									}; if (!f._fbq) f._fbq = n;
									n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
									t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
							})(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js', undefined, undefined, undefined);
							<?php
							foreach($cookies['fb_pixel']['tracking_codes'] as $fbcookie){
								?>
								fbq('init', '<?php echo $fbcookie; ?>');
								<?php
							}
							?>
							fbq('track', 'PageView');
							fbq('track', 'ViewContent');
							<?php
							if(is_singular() && (get_post_meta(get_the_ID(),'send_contact_event_to_facebook',true) == 'on')){
								echo "	fbq('track', 'Contact');\n";
							}
							?>
						});
						<?php
					}
					if(isset($cookies['youtube']) && !empty($cookies['youtube'])){
						?>
						document.addEventListener("hcm_youtube_accepted", function(){
							var hcm_yt_script = document.createElement("script");
							hcm_yt_script.type = "text/javascript";
							hcm_yt_script.setAttribute("async", "true");
							hcm_yt_script.setAttribute("src", "https://www.youtube.com/iframe_api");
							document.head.appendChild(hcm_yt_script);
							window.onYouTubeIframeAPIReady = function(){
								var ytloaded = true;
								var yt_player_containers = [].slice.call(document.querySelectorAll('.hcm_cookie_video_container[data-provider="youtube"]'));
								hcm_yt_loaded = true;
								yt_player_containers.forEach(function(player){
									videotype = player.dataset.videotype || false;
									videoid = player.dataset.videoid || false;
									if(videotype && videoid){
										console.log('activating '+videoid);
										hcm_activate_youtube_video(player);
									}
								});
							}
						});
						<?php
					}

					if(isset($cookies['artetv']) && !empty($cookies['artetv'])){
						?>
						document.addEventListener("hcm_artetv_accepted", function(){
							var arte_player_containers = [].slice.call(document.querySelectorAll('.hcm_cookie_video_container[data-provider="artetv"]'));
							arte_player_containers.forEach(function(player){
								videoid = player.dataset.videoid || false;
								if(videoid){
									hcm_activate_artetv_video(player);
								}
							});
						});
						<?php
					}

					if(isset($cookies['vimeo']) && !empty($cookies['vimeo'])){
						?>
						console.log('vimeo active');
						document.addEventListener("hcm_vimeo_accepted", function(){
							var vimeo_player_containers = [].slice.call(document.querySelectorAll('.hcm_cookie_video_container[data-provider="vimeo"]'));
							console.log('activating vimeo');
							vimeo_player_containers.forEach(function(player){
								videoid = player.dataset.videoid || false;
								if(videoid){
									hcm_activate_vimeo_video(player);
								}
							});
						});
						<?php
					}

					if(isset($cookies['etracker']) && !empty($cookies['etracker']) && isset($cookies['etracker']['tracking_codes']) && !empty($cookies['etracker']['tracking_codes'])){
						?>
						document.addEventListener("hcm_etracker_accepted", function(){
							var hcm_etracker_script = document.createElement("script");
							hcm_etracker_script.type = "text/javascript";
							hcm_etracker_script.setAttribute("src", "//static.etracker.com/code/e.js");
							hcm_etracker_script.setAttribute("charset", "UTF-8");
							hcm_etracker_script.setAttribute("data-secure-code", "<?php echo $cookies['etracker']['tracking_codes'] ?>");
							<?php
							if($cookies['etracker']['respect_dnt']){
								?>hcm_etracker_script.setAttribute("data-respect-dnt", "true");
								<?php
							}
							?>
							document.head.appendChild(hcm_etracker_script);
						});
						<?php
					}
					if(!empty($cookies['custom'])){
						foreach($cookies['custom'] as $customcookie){
							if(isset($customcookie['labels']['caption'])){
								$caption_sanitized = str_replace('-','_',sanitize_title($customcookie['labels']['caption']));
								?>
								document.addEventListener("hcm_custom_<?php echo $caption_sanitized; ?>_accepted", function(){
									<?php echo $customcookie['code_on_accept']; ?>
									<?php
									if(is_singular()){
										if($code = get_post_meta(get_the_ID(),'_hcm_custom_cookie_post_for_'.$caption_sanitized,true)){
											echo $code;
										}
									}
									?>
								});
								<?php
							}
						}
					}
					?>
				<?php
				if($cf7events){
					$contactforms = get_posts(array('post_type' => 'wpcf7_contact_form',
													'posts_per_page' => -1));
					if($contactforms){
						$conts = array();
						foreach($contactforms as $contactform){
							$title = $contactform->post_title;
							$action = get_post_meta($contactform->ID,'_hcm_ga_event_action',true) ? : 'Absenden';
							$category = get_post_meta($contactform->ID,'_hcm_ga_event_category',true) ? : 'Kontaktformular';
							$label = get_post_meta($contactform->ID,'_hcm_ga_event_label',true) ? : $title;
							$conts[$contactform->ID] = array('title' => $contactform->post_title,
															 'action' => $action,
															 'category' => $category,
															 'label' => $label);
						}
						?>
						var contactforms = <?php echo json_encode($conts); ?>;
						document.addEventListener( 'wpcf7mailsent', function( event ) {
							formnumber = event.detail.contactFormId;
							formtitle = contactforms[formnumber].title;
							formaction = contactforms[formnumber].action;
							formcategory = contactforms[formnumber].category;
							formlabel = contactforms[formnumber].label;
							<?php
							if(in_array('google_analytics',$cf7_types)){
								if($cookies['google_analytics']['send_anonymous_cf7_events']){
									?>
									if(window.ga){
										tid = ga.getAll().map(function(tracker) { return tracker.get('trackingId');});
										cid = ga.getAll().map(function(tracker){ return tracker.get('clientId'); });
										var data = {
											'action': 'hcm_send_anonymous_event_ga',
											'TID':tid,
											'EC': formcategory,
											'EA': formaction,
											'EL': formlabel,
											'DL':document.location.href,
											'DP':document.location.pathname,
											'DH':document.location.origin
										};
										var hcm_ga_ev_s_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
										jQuery.post(hcm_ga_ev_s_ajax_url,data,function(){});
									} else {
										tid = <?php echo json_encode($cookies['google_analytics']['tracking_codes']) ?>;
										var data = {
											'action': 'hcm_send_anonymous_event_ga',
											'TID':tid,
											'EC': formcategory,
											'EA': formaction,
											'EL': formlabel,
											'DL':document.location.href,
											'DP':document.location.pathname,
											'DH':document.location.origin
										};
										var hcm_ga_ev_s_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
										jQuery.post(hcm_ga_ev_s_ajax_url,data,function(){});
									}
									<?php
								} else {
									?>
									if(window.ga){
										tid = ga.getAll().map(function(tracker){ return tracker.get('trackingId');});
										cid = ga.getAll().map(function(tracker) { return tracker.get('clientId');} );
										var data = {
											'action': 'hcm_send_anonymous_event_ga',
											'TID':tid,
											'EC': formcategory,
											'EA': formaction,
											'EL': formlabel,
											'DL':document.location.href,
											'DP':document.location.pathname,
											'DH':document.location.origin
										};
										var hcm_ga_ev_s_ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
										jQuery.post(hcm_ga_ev_s_ajax_url,data,function(){});
									}
									<?php
								}
							}
							if(in_array('fb_pixel',$cf7_types)){
								?>
								if(window.fbq){
									fbq('trackCustom', 'Kontaktformular abgeschickt', {contactform: formtitle});
								}
								<?php
							}
							?>
						});
						<?php
					}
				}
				?>
			</script>
			<?php
		}
	}
	
	public function hcm_send_anonymous_event_ga(){
		$tracking_ids = isset($_POST['TID']) ? $_POST['TID'] : array();
		$client_id = 555;
		if(isset($_POST['CID'])){
			$client_id = $_POST['CID'];
		}
		$event_action = isset($_POST['EA']) ? sanitize_text_field($_POST['EA']) : NULL;
		$event_label = isset($_POST['EL']) ?  sanitize_text_field($_POST['EL']) : NULL;
		$event_category = isset($_POST['EC']) ? sanitize_text_field($_POST['EC']) : NULL;
		$event_value = (isset($_POST['EV']) && is_numeric($_POST['EV'])) ? intval($_POST['EV']) : NULL;
		$document_location = (isset($_POST['DL'])) ? sanitize_text_field($_POST['DL']) : NULL;
		$document_path = (isset($_POST['DP'])) ? sanitize_text_field($_POST['DP']) : NULL;
		$document_hostname = (isset($_POST['DH'])) ? sanitize_text_field($_POST['DH']) : NULL;
		if(is_array($tracking_ids) && !empty($tracking_ids) && $event_action && $event_category){
			if(count($tracking_ids)>1){
				$args = array();
				foreach($tracking_ids as $count => $tracking_id){
					if(is_array($client_id)){
						$cid = $client_id[$count];
					} else {
						$cid = $client_id;
					}
					$singleargs = array(
						'v' 	=> 	1,           		// Version.
						'tid'	=>	$tracking_id,  // Tracking ID / Property ID.
						'cid'	=>	$cid,				// Anonymous Client ID.
						't' 	=>	'event',			// Event hit type
						'ec'	=>	$event_category,	// Event Category. Required.
						'ea'	=>	$event_action,         // Event Action. Required.
						'ds' 	=> 'web',
					);
					if($event_label){
						$singleargs['el'] = $event_label;
					}
					if($event_value){
						$singleargs['ev'] = $event_value;
					}
					if($document_location){
						$singleargs['dl'] = $document_location;
						$singleargs['dr'] = $document_location;
					}
					if($document_hostname){
						$singleargs['dh'] = $document_hostname;
					}
					if($document_path){
						$singleargs['dp'] = $document_path;
					}
					$args[] = $singleargs;
				}
				$response = array();
				$url = 'https://www.google-analytics.com/collect';
				foreach($args as $arg){
					$response[] = wp_safe_remote_post($url,array('body' => $arg));
				}
			} else {
				$args = array(
					'v' 	=> 	1,           		// Version.
					'tid'	=>	$tracking_ids[0],  // Tracking ID / Property ID.
					'cid'	=>	$client_id,				// Anonymous Client ID.
					't' 	=>	'event',			// Event hit type
					'ec'	=>	$event_category,	// Event Category. Required.
					'ea'	=>	$event_action,         // Event Action. Required.
					'ds' 	=> 'web',
				);
				if($event_label){
					$args['el'] = $event_label;
				}
				if($event_value){
					$args['ev'] = $event_value;
				}
				if($document_location){
					$args['dl'] = $document_location;
					$args['dr'] = $document_location;
				}
				if($document_hostname){
					$args['dh'] = $document_hostname;
				}
				if($document_path){
					$args['dp'] = $document_path;
				}
				$url = 'https://www.google-analytics.com/collect';
				$response = wp_safe_remote_post($url,array('body' => $args));
			}
		}
		wp_die();
	}
	
	
	private function get_localize_cookie_options(){
		$cookies = $this->get_active_cookie_scripts_grouped();
		$localize_options = array('version' => hcm_get_version(), 'settings' => array(),'all_cookie_names' => array(),'active_cookies' => array(),'banner_preactivated' => array(),'dialog_preactivated' => array(),'necessary_cookies' => array());
		$duration = HUisHUCookieMonsterOptions::get('cookie_settings_duration','month');
		$localize_options['settings']['duration'] = $duration;
		$impid = (int)HUisHUCookieMonsterOptions::get('dont_show_on_imprint',0);
		$dsid = (int)get_option( 'wp_page_for_privacy_policy' );
		$localize_options['settings']['dontshow'] = false;
		if($impid){
			if(is_singular() && is_page($impid)){
				$localize_options['settings']['dontshow'] = true;
			}
		}
		if($dsid){
			if(is_singular() && is_page($dsid)){
				$localize_options['settings']['dontshow'] = true;
			}
		}
		
		if(isset($cookies['google_analytics'])){
			$localize_options['active_cookies']['google_analytics'] = array(
								'banner_preactivated' => $cookies['google_analytics']['banner_preactivated'],
								'dialog_preactivated' => $cookies['google_analytics']['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_google_analytics_call_on_acceptance',
			);
			if($cookies['google_analytics']['banner_preactivated']){
				$localize_options['banner_preactivated'][] = 'google_analytics';
			}
			if($cookies['google_analytics']['dialog_preactivated']){
				$localize_options['dialog_preactivated'][] = 'google_analytics';
			}
			$localize_options['all_cookie_names'][] = 'google_analytics';
		}
		
		if(isset($cookies['fb_pixel'])){
			$localize_options['active_cookies']['fb_pixel'] = array(
								'banner_preactivated' => $cookies['fb_pixel']['banner_preactivated'],
								'dialog_preactivated' => $cookies['fb_pixel']['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_fb_pixel_call_on_acceptance',
			);
			if($cookies['fb_pixel']['banner_preactivated']){
				$localize_options['banner_preactivated'][] = 'fb_pixel';
			}
			if($cookies['fb_pixel']['dialog_preactivated']){
				$localize_options['dialog_preactivated'][] = 'fb_pixel';
			}
			$localize_options['all_cookie_names'][] = 'fb_pixel';
		}

		if(isset($cookies['youtube'])){
			$localize_options['active_cookies']['youtube'] = array(
								'banner_preactivated' => $cookies['youtube']['banner_preactivated'],
								'dialog_preactivated' => $cookies['youtube']['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_youtube_call_on_acceptance',
			);
			if($cookies['fb_pixel']['banner_preactivated']){
				$localize_options['banner_preactivated'][] = 'youtube';
			}
			if($cookies['fb_pixel']['dialog_preactivated']){
				$localize_options['dialog_preactivated'][] = 'youtube';
			}
			$localize_options['all_cookie_names'][] = 'youtube';
		}

		if(isset($cookies['artetv'])){
			$localize_options['active_cookies']['artetv'] = array(
								'banner_preactivated' => $cookies['artetv']['banner_preactivated'],
								'dialog_preactivated' => $cookies['artetv']['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_artetv_call_on_acceptance',
			);
			if($cookies['artetv']['banner_preactivated']){
				$localize_options['banner_preactivated'][] = 'artetv';
			}
			if($cookies['artetv']['dialog_preactivated']){
				$localize_options['dialog_preactivated'][] = 'artetv';
			}
			$localize_options['all_cookie_names'][] = 'artetv';
		}

		if(isset($cookies['vimeo'])){
			$localize_options['active_cookies']['vimeo'] = array(
								'banner_preactivated' => $cookies['vimeo']['banner_preactivated'],
								'dialog_preactivated' => $cookies['vimeo']['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_vimeo_call_on_acceptance',
			);
			if($cookies['vimeo']['banner_preactivated']){
				$localize_options['banner_preactivated'][] = 'vimeo';
			}
			if($cookies['vimeo']['dialog_preactivated']){
				$localize_options['dialog_preactivated'][] = 'vimeo';
			}
			$localize_options['all_cookie_names'][] = 'vimeo';
		}
		
		if(isset($cookies['etracker'])){
			$localize_options['active_cookies']['etracker'] = array(
								'banner_preactivated' => $cookies['etracker']['banner_preactivated'],
								'dialog_preactivated' => $cookies['etracker']['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_etracker_call_on_acceptance',
			);
			if($cookies['etracker']['banner_preactivated']){
				$localize_options['banner_preactivated'][] = 'etracker';
			}
			if($cookies['etracker']['dialog_preactivated']){
				$localize_options['dialog_preactivated'][] = 'etracker';
			}
			$localize_options['all_cookie_names'][] = 'etracker';
		}
		
		if(isset($cookies['custom'])){
			foreach($cookies['custom'] as $custom_cookie){
				if(isset($custom_cookie['labels']['caption'])){
					$caption_sanitized = str_replace('-','_',sanitize_title($custom_cookie['labels']['caption']));
					$localize_options['active_cookies']['custom_'.$caption_sanitized] = array(
								'banner_preactivated' => $custom_cookie['banner_preactivated'],
								'dialog_preactivated' => $custom_cookie['dialog_preactivated'],
								'function_on_acceptance' => 'cookie_custom_'.$caption_sanitized.'_call_on_acceptance',
					);
					if($custom_cookie['use_as_necessary'] == 'on'){
						$localize_options['necessary_cookies'][] = 'custom_'.$caption_sanitized;
					} else {
						if($custom_cookie['banner_preactivated']){
							$localize_options['banner_preactivated'][] = 'custom_'.$caption_sanitized;
						}
						if($custom_cookie['dialog_preactivated']){
							$localize_options['dialog_preactivated'][] = 'custom_'.$caption_sanitized;
						}
						$localize_options['all_cookie_names'][] = 'custom_'.$caption_sanitized;
					}
				}
			}
		}
		return $localize_options;
	}
	
	public function insert_cookie_monster_banner_into_footer(){
		//first, we get all our activated cookies and their activation scripts
		$this->get_active_cookie_scripts();
		$cookies = $this->get_active_cookie_scripts_grouped();
		$impid = (int)HUisHUCookieMonsterOptions::get('dont_show_on_imprint',0);
		if($impid){
			$impurl = get_permalink($impid);
			$imptitle = get_the_title($impid);
			$implink = '<a href="'.$impurl.'">'.__($imptitle).'</a>';
		} else {
			$implink = "";
		}
		$text = __(HUisHUCookieMonsterOptions::get('cookie_banner_content',''));
		$privacy_page_link = '<a href="'.get_privacy_policy_url().'">'.__(get_the_title((int)get_option( 'wp_page_for_privacy_policy' ))).'</a>';
		$banner_accepted_cookies = array();
		foreach($cookies as $type => $cookie){
			if($type == 'custom'){
				foreach($cookie as $customcookie){
					if($customcookie['banner_preactivated']){
						$banner_accepted_cookies[] = $customcookie['labels']['caption'];
					}
				}
			} else {
				if($cookie['banner_preactivated']){
					$banner_accepted_cookies[] = $cookie['labels']['caption'];
				}
			}
		}
		$text = str_replace('{{privacy_page}}',	$privacy_page_link, $text);
		$text = str_replace('{{imprint_page}}',$implink,$text);
		$checkboxes_banner = '<div class="hcm_banner_checkboxes"><span class="hcm_banner_checkbox_span"><span class="fakelabel">Essentiell</span></span>';
		foreach($cookies as $type => $cookie){
			if(!($type == 'custom')){
				$checkboxes_banner.='<span class="hcm_banner_checkbox_span"><input autocomplete="off" type="checkbox" id="hcm_banner_cookie_checkbox_'.$type.'" class="hcm_cookie_checkbox" value="1" data-cookiename="'.$type.'" /><label for="hcm_banner_cookie_checkbox_'.$type.'">'.$cookie['labels']['caption'].'</label></span>';
			} else {
				foreach($cookie as $customcookie){
					if(!$customcookie['use_as_necessary']){
						$checkboxes_banner.='<span class="hcm_banner_checkbox_span"><input autocomplete="off" type="checkbox" id="hcm_banner_cookie_checkbox_custom_'.str_replace('-','_',sanitize_title($customcookie['labels']['caption'])).'" class="hcm_cookie_checkbox" value="1" data-cookiename="custom_'.str_replace('-','_',sanitize_title($customcookie['labels']['caption'])).'" /><label for="hcm_banner_cookie_checkbox_custom_'.str_replace('-','_',sanitize_title($customcookie['labels']['caption'])).'">'.$customcookie['labels']['caption'].'</label></span>';
					}
				}
			}
		}
		$checkboxes_banner.='</div>';
		$text = str_replace('{{COOKIES_SMALL_CHECKBOXES}}',$checkboxes_banner,$text);
		if($text){
			?>
			<div id="hcm_cookie_container" class="huishu-cookie-monster-banner-container">
				<div class="huishu-cookie-monster-banner">
					<div class="huishu-cookie-monster-text">
						<?php echo apply_filters('the_content',$text); ?>
					</div>
					<button class="hcm_cookie_accept_all">Alle akzeptieren</button>
					<button class="hcm_cookie_save_choices">Auswahl speichern</button>
					<button class="hcm_cookie_show_details">Details anzeigen</button>
				</div>
				<?php
					$laufzeit = array(
									'day' => '1 Tag',
									'month' => '1 Monat',
									'year' => '1 Jahr',
									  );
					$cookie_rejected_names = "";
					foreach($cookies as $type => $cookie){
						if(!($type == 'custom')){
							$cookie_rejected_names.=', hcm_cookie_rejected_'.$type;
						} else {
							foreach($cookie as $customcookie){
										$labels = $customcookie['labels'];
										if(!$customcookie['use_as_necessary']){
											$cookie_rejected_names.=', hcm_cookie_rejected_custom_'.str_replace('-','_',sanitize_title($labels['caption']));
										}
							}
						}
					}
					?>
					<div id="hcm_advanced_options" class="huishu-cookie-monster-advanced-options">
						<div class="huishu-cookie-monster-advanced-options-einleitung">
								<h3>Cookie Details</h3>
								<p>Hier finden Sie eine Übersicht über alle verwendeten Cookies. Sie können alle Cookies bestätigen, oder einzelne Cookies die Zustimmung erteilen oder verweigern.</p>
								<button class="hcm_cookie_accept_all">Alle akzeptieren</button>
								<button class="hcm_cookie_save_choices">Auswahl speichern</button>
								<button class="hcm_cookie_accept_none">Nur Essentielle</button>
								<span class="imprintlinks">
									<?php echo $privacy_page_link; ?> | <?php echo $implink; ?>
								</span>
						</div>
						<div class="cookie-descriptions">
							<div class="cookie-description closed">
								<div class="simple-view">
									<h4 class="cookiename">Cookie-Einstellungen (essentiell)</h4>
									<p>Dieser Cookie speichert Ihre Einstellungen, welche Cookies Sie zulassen wollen.</p>
									<a class="show-enhanced" href="#">Weitere Informationen anzeigen</a>
								</div>
								<table class="enhanced-view">
									<tr>
										<th>Anbieter</th>
										<td>Eigentümer dieser Website</td>
									</tr>
									<tr>
										<th>Zweck</th>
										<td>Speichert die Einstellungen der Besucher, welche Cookies zugelassen oder verweigert wurden.</td>
									</tr>
									<tr>
										<th>Cookie-Name</th>
										<td>hcm_cookie_settings<?php echo $cookie_rejected_names; ?></td>
									</tr>
									<tr>
										<th>Cookie Laufzeit</th>
										<td><?php echo $laufzeit[HUisHUCookieMonsterOptions::get('cookie_settings_duration')]; ?></td>
									</tr>
								</table>
							</div>
							<?php
							foreach($cookies as $type => $cookie){
								if(!($type == 'custom')){
									$labels = $cookie['labels'];
									?>
									<div class="cookie-description">
										<div class="simple-view">
											<h4 class="cookiename">
												<?php echo $labels['caption']; ?>
												<span class="hcm_switch_container">
													<label class="hcm_switch">
														<input type="checkbox" autocomplete="off" id="<?php echo $type; ?>" data-cookiename="<?php echo $type; ?>" class="hcm_cookie_checkbox" value="1" />
														<span class="hcm_switch_slider"></span>
													</label>
												</span>
											</h4>
											<?php echo $labels['description']; ?>
											<a class="show-enhanced" href="#">Weitere Informationen anzeigen</a>
										</div>
										<table class="enhanced-view">
											<?php if($labels['vendor']){ ?>
											<tr>
												<th>Anbieter</th>
												<td><?php echo $labels['vendor']; ?></td>
											</tr>
											<?php } ?>
											<?php if($labels['cookie_names']){ ?>
											<tr>
												<th>Cookie-Name</th>
												<td><?php echo $labels['cookie_names']; ?></td>
											</tr>
											<?php } ?>
											<?php if($labels['runtime']){ ?>
											<tr>
												<th>Cookie Laufzeit</th>
												<td><?php echo $labels['runtime']; ?></td>
											</tr>
											<?php } ?>
											<?php if($labels['privacy_link']){ ?>
											<tr>
												<th>Datenschutzlink</th>
												<td><a target="_blank" href="<?php echo esc_url($labels['privacy_link']); ?>"><?php echo $labels['privacy_link']; ?></a></td>
											</tr>
											<?php } ?>
										</table>
									</div>
									<?php
								} else {
									foreach($cookie as $customcookie){
										$labels = $customcookie['labels'];
										?>
										<div class="cookie-description">
											<div class="simple-view">
												<h4 class="cookiename">
													<?php echo $labels['caption']; ?>
													<?php if($customcookie['use_as_necessary']){ ?>
															(essentiell)
														<?php } else { ?>
															<span class="hcm_switch_container">
																<label class="hcm_switch">
																	<input type="checkbox" autocomplete="off" id="custom_<?php echo str_replace('-','_',sanitize_title($labels['caption'])); ?>" data-cookiename="custom_<?php echo str_replace('-','_',sanitize_title($labels['caption'])); ?>" class="hcm_cookie_checkbox" value="1" />
																	<span class="hcm_switch_slider">
																	</span>
																</label>
															</span>
														<?php } ?>
												</h4>
												<?php echo $labels['description']; ?>
												<a class="show-enhanced" href="#">Weitere Informationen anzeigen</a>
											</div>
											<table class="enhanced-view">
												<?php if($labels['vendor']){ ?>
												<tr>
													<th>Anbieter</th>
													<td><?php echo $labels['vendor']; ?></td>
												</tr>
												<?php } ?>
												<?php if($labels['cookie_names']){ ?>
												<tr>
													<th>Cookie-Name</th>
													<td><?php echo $labels['cookie_names']; ?></td>
												</tr>
												<?php } ?>
												<?php if($labels['runtime']){ ?>
												<tr>
													<th>Cookie Laufzeit</th>
													<td><?php echo $labels['runtime']; ?></td>
												</tr>
												<?php } ?>
												<?php if($labels['privacy_link']){ ?>
												<tr>
													<th>Datenschutzlink</th>
													<td><a target="_blank" href="<?php echo esc_url($labels['privacy_link']); ?>"><?php echo $labels['privacy_link']; ?></a></td>
												</tr>
												<?php } ?>
											</table>
										</div>
										<?php
									}
								}
							}
							?>
						</div>
					</div>
			</div>
			<?php
		}
	}
}