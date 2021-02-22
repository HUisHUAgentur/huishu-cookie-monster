hcmSendBrowserAgnosticEvent = function(elem, eventName) {
    //Needed for IE Support: https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/dispatchEvent#Browser_Compatibility
    //https://stackoverflow.com/a/49071358/79677
    let event;
    if (typeof(Event) === 'function') {
        event = new Event(eventName);
    } else {
        event = document.createEvent('Event');
        event.initEvent(eventName, true, true);
    }
    elem.dispatchEvent(event);

    return event;
};

function hcmInArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

var hcm_active_cookies = [];
var cookie_settings_version = parseFloat(hcm_cookie_options.version);
var hcm_inactive_cookies = hcm_cookie_options.all_cookie_names;
var hcm_dont_show_here = hcm_cookie_options.settings.dontshow;
var hcm_yt_player;

function hcm_cookie_is_active(cookiename){
	return hcmInArray(cookiename,hcm_active_cookies);
}

function hcm_add_cookie_to_active(cookiename){
	if(hcmInArray(cookiename,hcm_cookie_options.all_cookie_names)){
		if(!hcmInArray(cookiename,hcm_active_cookies)){
			hcm_active_cookies.push(cookiename);
		}
		if(hcmInArray(cookiename,hcm_inactive_cookies)){
			hcm_inactive_cookies = hcm_inactive_cookies.filter(function(value){
				return value != cookiename;
			});
		}
	}
}

function hcm_remove_cookie_from_active(cookiename){
	if(hcmInArray(cookiename,hcm_cookie_options.all_cookie_names)){
		if(hcmInArray(cookiename,hcm_active_cookies)){
			hcm_active_cookies = hcm_active_cookies.filter(function(value){
				return value != cookiename;
			});
		}
		if(!hcmInArray(cookiename,hcm_inactive_cookies)){
			hcm_inactive_cookies.push(cookiename);
		}
	}
}

function hcm_execute_active_cookies(setCookie){
	cookieduration = hcm_cookie_options.settings.duration;
	cookiedate = new Date();
	if(cookieduration == 'day'){
	   cookiedate.setDate(cookiedate.getDate() + 1);
	} else if(cookieduration == 'month'){
	   cookiedate.setMonth(cookiedate.getMonth() + 1);
	} else if(cookieduration == 'year'){
	   cookiedate.setFullYear(cookiedate.getFullYear() + 1);
	}
	cookiesettingsobject = {
		accepted_cookies:[],
		rejected_cookies:[],
		embedded_content_accepted:[],
        cookie_version: cookie_settings_version
	};
	hcm_active_cookies.forEach(function(cookiename){
		event = hcmSendBrowserAgnosticEvent(document,'hcm_'+cookiename+'_accepted');
		console.log('sending hcm_'+cookiename+'_accepted event');
		//document.dispatchEvent(event);
		cookiesettingsobject.accepted_cookies.push(cookiename);
		Cookies.remove('hcm_cookie_rejected_'+cookiename);
	});
	hcm_inactive_cookies.forEach(function(cookiename){
		event = hcmSendBrowserAgnosticEvent(document,'hcm_'+cookiename+'_rejected');
		//document.dispatchEvent(event);
		cookiesettingsobject.rejected_cookies.push(cookiename);
		if(!Cookies.get('hcm_cookie_rejected_'+cookiename)){
			Cookies.set('hcm_cookie_rejected_'+cookiename,1,{expires:cookiedate, sameSite:'strict' });
		}
	});
	if(setCookie){
		Cookies.set('hcm_cookie_settings',cookiesettingsobject,{expires:cookiedate, sameSite:'strict'});
	}
}

var hcm_yt_loaded = false;
var hcm_yt_player = false;
var hcm_yt_manually_activated = false;
var hcm_artetv_manually_activated = false;
var hcm_vimeo_manually_activated = false;
var hcm_element_to_be_activated = false;
var hcm_last_yt_type = false;

function hcm_video_manually_activated(provider){
	if(provider == 'youtube'){
		return hcm_yt_manually_activated;
	} else if(provider == 'artetv'){
		return hcm_artetv_manually_activated;
	} else if(provider == 'vimeo'){
		return hcm_vimeo_manually_activated;
	}
	return false;
}

function hcm_video_activate(element,provider){
	if(provider == 'youtube'){
		hcm_activate_youtube_video(element);
	} else if(provider == 'artetv'){
		hcm_activate_artetv_video(element);
	} else if(provider == 'vimeo'){
		hcm_activate_vimeo_video(element);
	}
}

function hcm_activate_youtube_video(element){
	if(!hcm_yt_loaded){
		console.log('not loaded yet');
		hcm_element_to_be_activated = element;
		var hcm_yt_script = document.createElement("script");
		hcm_yt_script.type = "text/javascript";
		hcm_yt_script.setAttribute("async", "true");
		hcm_yt_script.setAttribute("src", "https://www.youtube.com/iframe_api");
		document.head.appendChild(hcm_yt_script);
		window.onYouTubeIframeAPIReady = function() {
			hcm_yt_loaded = true;
			height = hcm_element_to_be_activated.dataset.ytheight || "100%";
			width = hcm_element_to_be_activated.dataset.ytwidth || "100%";
			if(hcm_element_to_be_activated.dataset.videotype == 'youtubeid'){
				console.log('youtubeid');
				console.log(hcm_element_to_be_activated);
				hcm_yt_player = new YT.Player(hcm_element_to_be_activated, {
					host: 'https://www.youtube-nocookie.com',
					playsinline: 1,
					rel: 0,
					events: {
						'onReady': hcm_onPlayerReady,
					}, 
					modestbranding:1,               
					height: height,
					width: width,
					videoId: hcm_element_to_be_activated.dataset.videoid,
				});
				hcm_yt_manually_activated = true;
				hcm_last_yt_type = 'youtubeid';
			} else if(hcm_element_to_be_activated.dataset.videotype == 'youtubeplaylist'){
				console.log('youtubeplaylist');
				hcm_yt_player = new YT.Player(hcm_element_to_be_activated, {
					host: 'https://www.youtube-nocookie.com',
					playsinline: 1,
					rel: 0,
					events: {
						'onReady': hcm_onPlayerReady,
					}, 
					modestbranding:1,               
					height: height,
					width: width,
					playerVars: 
					{
						listType:'playlist',
						list: hcm_element_to_be_activated.dataset.videoid
					},
				});
				hcm_yt_manually_activated = true;
				hcm_last_yt_type = 'youtubeplaylist';
			}
		}
	} else {
		if(hcm_yt_player){
				if(element.dataset.videotype == 'youtubeid'){
					//hcm_yt_player.loadVideoById(element.dataset.videoid);
					hcm_yt_player.cueVideoById(element.dataset.videoid);
					hcm_last_yt_type = 'youtubeid';
				} else if(element.dataset.videotype == 'youtubeplaylist'){
					console.log('loadplaylist');
					//hcm_yt_player.loadPlaylist({
					hcm_yt_player.cuePlaylist({
						listType:'playlist',
						list: element.dataset.videoid
					});
					hcm_last_yt_type = 'youtubeplaylist';
				}
		} else {
			console.log('new player');
			hcm_element_to_be_activated = element;
			if(element.dataset.videotype == 'youtubeid'){
				hcm_yt_player = new YT.Player(hcm_element_to_be_activated, {
					host: 'https://www.youtube-nocookie.com',
					playsinline: 1,
					rel: 0,
					events: {
						'onReady': hcm_onPlayerReady,
					}, 
					modestbranding:1,    
					videoId:hcm_element_to_be_activated.dataset.videoid      
				});
				hcm_last_yt_type = 'youtubeplaylist';
				hcm_yt_manually_activated = true;
			} else if(element.dataset.videotype == 'youtubeplaylist'){
				hcm_yt_player = new YT.Player(hcm_element_to_be_activated, {
					host: 'https://www.youtube-nocookie.com',
					playsinline: 1,
					rel: 0,
					events: {
						'onReady': hcm_onPlayerReady,
					}, 
					modestbranding:1,               
					playerVars: 
					{
						listType:'playlist',
						list: hcm_element_to_be_activated.dataset.videoid
					},
				});
				hcm_last_yt_type = 'youtubeplaylist';
				hcm_yt_manually_activated = true;
			}
		}
	}
}

function hcm_activate_artetv_video(element){
	iframe = document.createElement('iframe');
	iframe.setAttribute('id',element.id);
	iframe.setAttribute('aria-label',element.getAttribute('aria-label'));
	iframe.setAttribute('role',element.getAttribute('role'));
	iframe.setAttribute('tabindex',element.getAttribute('tabindex'));
	iframe.setAttribute('class',element.getAttribute('class'));
	iframe.setAttribute('allowfullscreen',"true");
	iframe.setAttribute('frameborder',"0");
	iframe.dataset.label = element.dataset.label;
	iframe.dataset.thumbpic = element.dataset.thumbpic;
	iframe.dataset.fullpic = element.dataset.fullpic;
	iframe.dataset.ytautoplay = element.dataset.ytautoplay;
	iframe.dataset.videoid = element.dataset.videoid;
	iframe.dataset.videotype = element.dataset.videotype;
	iframe.dataset.provider = element.dataset.provider;
	iframe.dataset.copyright = element.dataset.copyright;
	iframe.dataset.livemarker = element.dataset.livemarker;
	console.log(element.dataset.provider);
	iframe.src = element.dataset.videoid;
	element.replaceWith(iframe);
	hcm_artetv_manually_activated = true;
}


function hcm_activate_vimeo_video(element){
	iframe = document.createElement('iframe');
	iframe.setAttribute('id',element.id);
	iframe.setAttribute('aria-label',element.getAttribute('aria-label'));
	iframe.setAttribute('role',element.getAttribute('role'));
	iframe.setAttribute('tabindex',element.getAttribute('tabindex'));
	iframe.setAttribute('class',element.getAttribute('class'));
	iframe.setAttribute('allowfullscreen',"true");
	iframe.setAttribute('frameborder',"0");
	iframe.dataset.label = element.dataset.label;
	iframe.dataset.thumbpic = element.dataset.thumbpic;
	iframe.dataset.fullpic = element.dataset.fullpic;
	iframe.dataset.ytautoplay = element.dataset.ytautoplay;
	iframe.dataset.videoid = element.dataset.videoid;
	iframe.dataset.videotype = element.dataset.videotype;
	iframe.dataset.provider = element.dataset.provider;
	iframe.dataset.copyright = element.dataset.copyright;
	iframe.dataset.livemarker = element.dataset.livemarker;
	//iframe.src = "https://player.vimeo.com/video/"+element.dataset.videoid+"?autoplay=1";
	iframe.src = "https://player.vimeo.com/video/"+element.dataset.videoid;
	element.replaceWith(iframe);
	hcm_vimeo_manually_activated = true;
}

function hcm_onPlayerReady(event){
	//autoplay = event.target.l.dataset.ytautoplay || false;
	autoplay = false;
	if(autoplay){
		event.target.playVideo();
	}
}


function hcm_activate_single_yt_video(button){
	if(!hcm_cookie_is_active('youtube')){
		var hcm_tobeactivated = button;
		if(!hcm_yt_loaded){
			var hcm_yt_script = document.createElement("script");
			hcm_yt_script.type = "text/javascript";
			hcm_yt_script.setAttribute("async", "true");
			hcm_yt_script.setAttribute("src", "https://www.youtube.com/iframe_api");
			document.head.appendChild(hcm_yt_script);	
			window.onYouTubeIframeAPIReady = function() {
				var hcm_yt_loaded = true;
				height = tobeactivated.dataset.ytheight || "100%";
				width = tobeactivated.dataset.ytwidth || "100%";
				hcm_yt_player = new YT.Player(tobeactivated, {
					host: 'https://www.youtube-nocookie.com',
					playsinline: 1,
					rel: 0,
					events: {
						'onReady': onPlayerReady,
					}, 
					modestbranding:1,               
					height: height,
					width: width,
					videoId: tobeactivated.dataset.videoid,
				});
				manually_activated = true;
			}
		} else {
			height = button.dataset.ytheight || "100%";
			width = button.dataset.ytwidth || "100%";
			hcm_yt_player = new YT.Player(button, {
				host: 'https://www.youtube-nocookie.com',
				playsinline: 1,
				rel: 0,
				events: {
					'onReady': onPlayerReady,
				}, 
				modestbranding:1,               
				height: height,
				width: width,
				videoId: button.dataset.videoid,
			});
		}
		window.onPlayerReady = function(event){
			autoplay = event.target.l.dataset.ytautoplay || false;
			if(autoplay){
				event.target.playVideo();
			}
		}
	}
}

document.addEventListener("DOMContentLoaded", function() {
    show_cookie_dialog = false;
	cookiesettings = Cookies.get('hcm_cookie_settings');
	allcookies = hcm_cookie_options.all_cookie_names;
	hcm_cookie_options.necessary_cookies.forEach(function(cookiename){
	     event = hcmSendBrowserAgnosticEvent(document,'hcm_'+cookiename+'_accepted');
	     //document.dispatchEvent(event);
	});
	if(cookiesettings){
		//Cookie Settings were made previously
		//now check the settings and load some scripts!
		cookiesettingsobject = Cookies.getJSON('hcm_cookie_settings');
        if(cookiesettingsobject.cookie_version){
            if(cookiesettingsobject.cookie_version < cookie_settings_version){
                show_cookie_dialog = true;
            }
        } else {
            show_cookie_dialog = true;
        }
	} else {
		if(!hcm_dont_show_here){
            show_cookie_dialog = true;
		}
	}
    if(show_cookie_dialog){
        cookie_container = document.getElementById('hcm_cookie_container');
		cookie_container.style.display="block";
    } else {
        configured_cookies = hcm_cookie_options.active_cookies;
		cookie_container = document.getElementById('hcm_cookie_container');
		cookie_container.style.display="none";
		if(cookiesettings){
            cookiesettingsobject.accepted_cookies.forEach(function(cookiename){
                hcm_add_cookie_to_active(cookiename);
            });
            hcm_cookie_options.all_cookie_names.forEach(function(cookiename){
                if(!hcmInArray(cookiename,cookiesettingsobject.accepted_cookies)){
                    hcm_remove_cookie_from_active(cookiename);
                }
            });
            cookiecheckboxes = [].slice.call(document.querySelectorAll('#hcm_advanced_options .cookie-descriptions .hcm_cookie_checkbox'));
            cookiecheckboxes.forEach(function(cookiecheckbox){
                cookiename = cookiecheckbox.dataset.cookiename;
                if(hcmInArray(cookiename,cookiesettingsobject.accepted_cookies)){
                    cookiecheckbox.checked = true;
                } else {
                    cookiecheckbox.checked = false;
                }
            });
            hcm_execute_active_cookies(false);
        }
    }
	
    var hcm_cookie_details = [].slice.call(document.querySelectorAll(".hcm_cookie_show_details"));
    hcm_cookie_details.forEach(function(details_button){
        details_button.addEventListener('click',function(event){
            event.preventDefault();
            if(hcm_dont_show_here){
                document.getElementById('hcm_cookie_container').addEventListener('click',function(event){
                    if(event.target == this){
                        cookie_container = document.getElementById('hcm_cookie_container');
                        cookie_container.classList.remove('show-advanced');
                        cookie_container.style.display="none";
                    }
                });
            }
            document.getElementById('hcm_cookie_container').classList.add('show-advanced');
            document.getElementById('hcm_cookie_container').style.display = "block";
            var cookie_advanced_informations = [].slice.call(document.querySelectorAll("#hcm_advanced_options .cookie-descriptions .show-enhanced"));
            cookie_advanced_informations.forEach(function(link){
               link.addEventListener('click',function(event){
                  event.preventDefault();
                  cont = event.target.parentNode;
                  tbl = cont.nextElementSibling;
                  tbl.style.display = (window.getComputedStyle(tbl).display == 'none') ? 'table' : 'none';
               });
            });
        });
    });                                           
    
	var hcm_cookie_checkboxes = [].slice.call(document.querySelectorAll(".hcm_cookie_checkbox"));
	hcm_cookie_checkboxes.forEach(function(cookie_checkbox){
		cookie_checkbox.addEventListener('change',function(){
			cookiename = this.dataset.cookiename;
			if(this.checked) {
				hcm_add_cookie_to_active(cookiename);
			} else {
				hcm_remove_cookie_from_active(cookiename);
			}
		});
	});
	
	var hcm_cookies_accept_all = [].slice.call(document.querySelectorAll(".hcm_cookie_accept_all"));
	hcm_cookies_accept_all.forEach(function(accept_all_button){
		accept_all_button.addEventListener('click',function(){
			hcm_active_cookies = hcm_cookie_options.all_cookie_names;
			hcm_inactive_cookies = [];
			hcm_execute_active_cookies(true);
			cookie_container = document.getElementById('hcm_cookie_container');
            cookie_container.classList.remove('show-advanced');
			cookie_container.style.display="none";
            cookiecheckboxes = [].slice.call(document.querySelectorAll('#hcm_advanced_options .cookie-descriptions .hcm_cookie_checkbox'));
            cookiecheckboxes.forEach(function(cookiecheckbox){
                cookiecheckbox.checked = true;
            });
		});
	});
	
	var hcm_cookies_accept_none = [].slice.call(document.querySelectorAll(".hcm_cookie_accept_none"));
	hcm_cookies_accept_none.forEach(function(accept_none_button){
		accept_none_button.addEventListener('click',function(){
			hcm_active_cookies = [];
			hcm_inactive_cookies = hcm_cookie_options.all_cookie_names;
			hcm_execute_active_cookies(true);
			cookie_container = document.getElementById('hcm_cookie_container');
            cookie_container.classList.remove('show-advanced');
			cookie_container.style.display="none";
            cookiecheckboxes = [].slice.call(document.querySelectorAll('#hcm_advanced_options .cookie-descriptions .hcm_cookie_checkbox'));
            cookiecheckboxes.forEach(function(cookiecheckbox){
                cookiecheckbox.checked = false;
            });
		});
	});
	
	var hcm_cookies_save_choices = [].slice.call(document.querySelectorAll(".hcm_cookie_save_choices"));
	hcm_cookies_save_choices.forEach(function(save_choices_button){
		save_choices_button.addEventListener('click',function(){
			hcm_execute_active_cookies(true);
			cookie_container = document.getElementById('hcm_cookie_container');
            cookie_container.classList.remove('show-advanced');
			cookie_container.style.display="none";
		});
	});



});