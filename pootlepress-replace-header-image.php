<?php
/*
Plugin Name: Canvas Extension - Replace Header Image Ppr Page
Plugin URI: http://www.pootlepress.com/
Description: An extension for WooThemes Canvas that allow you to use different header image for each post, page or product page.
Version: 1.0.0
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'pootlepress-replace-header-image-functions.php' );
require_once( 'classes/class-pootlepress-replace-header-image.php' );

$GLOBALS['pootlepress_replace_header_image'] = new Pootlepress_Replace_Header_Image( __FILE__ );
$GLOBALS['pootlepress_replace_header_image']->version = '1.0.0';

?>
