<?php
/*
Plugin Name: Canvas Extension - Replace Header Image Per Page
Plugin URI: http://www.pootlepress.com/
Description: An extension for WooThemes Canvas that allow you to use different header image for each post, page or product page.
Version: 1.1.0
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'pootlepress-replace-header-image-functions.php' );
require_once( 'classes/class-pootlepress-replace-header-image.php' );
require_once( 'classes/class-pootlepress-updater.php');

$GLOBALS['pootlepress_replace_header_image'] = new Pootlepress_Replace_Header_Image( __FILE__ );
$GLOBALS['pootlepress_replace_header_image']->version = '1.1.0';

add_action('init', 'pp_rhi_updater');
function pp_rhi_updater()
{
    if (!function_exists('get_plugin_data')) {
        include(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $data = get_plugin_data(__FILE__);
    $wptuts_plugin_current_version = $data['Version'];
    $wptuts_plugin_remote_path = 'http://www.pootlepress.com/?updater=1';
    $wptuts_plugin_slug = plugin_basename(__FILE__);
    new Pootlepress_Updater ($wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug);
}

?>
