<?php
/**
* Plugin name: Search Popup ThunderBolt (Optimate search realtime)
* Plugin URL: https://foxplugin.com
* Description: Search Popup ThunderBolt (Optimate search realtime)
* Domain Path: /languages
* Version: 1.1.6
* Author: Fox Plugin
* Author URL: https://foxplugin.com
* License: GPLv2 or later
/**
* Search Popup ThunderBolt (Optimate search realtime)
*/
# check
if (! defined( 'ABSPATH' )){
	die( '-1' );
}
# on jquery index
function popup_tb_enable_scripts(){
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'popup_tb_enable_scripts');
# Them ver
define( 'POPUPTB_VER', '1.1.6' );
# link plugin
define('POPUPTB_URL', plugin_dir_url( __FILE__ ));
define('POPUPTB_DIR', plugin_dir_path( __FILE__ ));
define('POPUPTB_BASE', plugin_basename( __FILE__ ));
# popup tb global
$popuptb_options = get_option('popuptb_settings');
# the ngon ngu
function popup_tb_load_textdomain() {
   load_plugin_textdomain( 'popup-tb', false, dirname( POPUPTB_BASE ) . '/lang' ); 
}
add_action( 'plugins_loaded', 'popup_tb_load_textdomain' );
# load css js
function popup_tb_customize_enqueue(){
	wp_enqueue_style('popuptb', POPUPTB_URL . 'css/index.css', array(), POPUPTB_VER);
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' );
}
add_action( 'admin_head', 'popup_tb_customize_enqueue' );
# add code functions
include(POPUPTB_DIR . 'inc/popuptb-admin.php');
include(POPUPTB_DIR . 'inc/popuptb-content.php');
# them lien ket gioi thieu
function popuptb_settings_about($links, $file) {
	if (false !== strpos($file, 'popup-tb/popup-tb.php')) {
		$settings_link = '<a href="' . admin_url('admin.php?page=popuptb-options') . '">'. __('Setting', 'popup-tb'). '</a>';
		array_unshift($links, $settings_link);
		$settings_link = '<a href="https://foxplugin.com" target="_blank">'. __('Home', 'popup-tb'). '</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'popuptb_settings_about', 10, 2);
