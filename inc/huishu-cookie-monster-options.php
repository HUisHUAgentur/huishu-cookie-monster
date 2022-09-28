<?php

defined( 'ABSPATH' ) or die( 'No no no!' );

class HUisHUCookieMonsterOptions{
	public static function get($key, $default_value = 0){
		$options_name = 'huishu_cookie_monster_options';
		if ( function_exists('cmb2_get_option') ) {
			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( $options_name, $key, $default_value );
		}
		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( $options_name, $default_value );
		$val = $default_value;
		if('all' == $key){
			$val = $opts;
		} elseif(is_array($opts) && array_key_exists($key, $opts) && (false !== $opts[$key])){
			$val = $opts[$key];
		}
		return $val;
	}	
}
