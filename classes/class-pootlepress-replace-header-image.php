<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Pootlepress_Replace_Header_Image Class
 *
 * Base class for the Pootlepress Replace Header Image.
 *
 * @package WordPress
 * @subpackage Pootlepress_Replace_Header_Image
 * @category Core
 * @author Pootlepress
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * public $token
 * public $version
 * 
 * - __construct()
 * - add_theme_options()
 * - get_menu_styles()
 * - load_stylesheet()
 * - load_script()
 * - load_localisation()
 * - check_plugin()
 * - load_plugin_textdomain()
 * - activation()
 * - register_plugin_version()
 * - get_header()
 * - woo_nav_custom()
 */
class Pootlepress_Replace_Header_Image {
	public $token = 'pootlepress-replace-header-image';
	public $version;
	private $file;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {
		$this->file = $file;
		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $file, array( &$this, 'activation' ) );

		// Load for a method/function for the selected style and load it.

		// Load for a stylesheet for the selected style and load it.
//        add_action( 'wp_enqueue_scripts', array( &$this, 'load_script' ) );
//
//        add_action('admin_print_scripts', array(&$this, 'load_admin_script'));
//
        add_action('wp_head', array(&$this, 'option_css'), 100);

        add_filter('wooframework_custom_field_tab_content', array($this, 'filter_metabox'));

        add_action( 'edit_post', array($this, 'metabox_handle'));

	} // End __construct()

    public function filter_metabox($output) {
        $headerImageOptions = $this->metabox_fields();
        $output .= $this->metabox_create_fields($headerImageOptions, '', 'general');
        return $output;
    }

    private function metabox_fields() {
        $headerImageOptions = array(
            array(
                'name' => 'header_image',
                'label' => 'Header Background Image',
                'desc' => 'Upload a background image, or specify the image address of your image (http://yoursite.com/image.png).
Image should be same width as your site width.',
                'type' => 'upload',
            ),
            array(
                'name' => 'header_image_repeat',
                'label' => 'Header Background Image Repeat',
                'desc' => 'Select how you want your background image to display.',
                'type' => 'select',
                'default' => 'no-repeat',
                'options' => array(
                    'no-repeat',
                    'repeat',
                    'repeat-x',
                    'repeat-y'
                )
            ),

            array(
                'name' => 'header_image_full_width',
                'label' => 'Header Background Image (Full Width)',
                'desc' => 'Upload a background image, or specify the image address of your image (http://yoursite.com/image.png).
Image should be same width as your site width.',
                'type' => 'upload',
            ),
            array(
                'name' => 'header_image_repeat_full_width',
                'label' => 'Header Background Image Repeat (Full Width)',
                'desc' => 'Select how you want your background image to display.',
                'type' => 'select',
                'default' => 'no-repeat',
                'options' => array(
                    'no-repeat',
                    'repeat',
                    'repeat-x',
                    'repeat-y'
                )
            )
        );
        return $headerImageOptions;
    }

    public function metabox_create_fields ( $metaboxes, $callback, $token = 'general' ) {
        global $post;

        if ( ! is_array( $metaboxes ) ) { return; }

        // $template_to_show = $callback['args'];
        $template_to_show = $token;

        $output = '';

        $output .= '<div id="wf-tab-' . esc_attr( $token ) . '">' . "\n";
        $output .= '<table class="woo_metaboxes_table">'."\n";
        foreach ( $metaboxes as $k => $woo_metabox ) {

            // Setup CSS classes to be added to each table row.
            $row_css_class = 'woo-custom-field';
            if ( ( $k + 1 ) == count( $metaboxes ) ) { $row_css_class .= ' last'; }

            $woo_id = 'woothemes_' . $woo_metabox['name'];
            $woo_name = $woo_metabox['name'];

            if ( function_exists( 'woothemes_content_builder_menu' ) ) {
                $metabox_post_type_restriction = $woo_metabox['cpt'][$post->post_type];
            } else {
                $metabox_post_type_restriction = 'undefined';
            }

            if ( ( $metabox_post_type_restriction != '' ) && ( $metabox_post_type_restriction == 'true' ) ) {
                $type_selector = true;
            } elseif ( $metabox_post_type_restriction == 'undefined' ) {
                $type_selector = true;
            } else {
                $type_selector = false;
            }

            $woo_metaboxvalue = '';

            if ( $type_selector ) {

                if( isset( $woo_metabox['type'] ) && ( in_array( $woo_metabox['type'], woothemes_metabox_fieldtypes() ) ) ) {

                    $woo_metaboxvalue = get_post_meta($post->ID,$woo_name,true);

                }

                // Make sure slashes are stripped before output.
                foreach ( array( 'label', 'desc', 'std' ) as $k ) {
                    if ( isset( $woo_metabox[$k] ) && ( $woo_metabox[$k] != '' ) ) {
                        $woo_metabox[$k] = stripslashes( $woo_metabox[$k] );
                    }
                }

                if ( $woo_metaboxvalue == '' && isset( $woo_metabox['std'] ) ) {

                    $woo_metaboxvalue = $woo_metabox['std'];
                }

                // Add a dynamic CSS class to each row in the table.
                $row_css_class .= ' woo-field-type-' . strtolower( $woo_metabox['type'] );

                if( $woo_metabox['type'] == 'info' ) {

                    $output .= "\t".'<tr class="' . $row_css_class . '" style="background:#f8f8f8; font-size:11px; line-height:1.5em;">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="'. esc_attr( $woo_id ) .'">'.$woo_metabox['label'].'</label></th>'."\n";
                    $output .= "\t\t".'<td style="font-size:11px;">'.$woo_metabox['desc'].'</td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }
                elseif( $woo_metabox['type'] == 'text' ) {

                    $add_class = ''; $add_counter = '';
                    if($template_to_show == 'seo'){$add_class = 'words-count'; $add_counter = '<span class="counter">0 characters, 0 words</span>';}
                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.esc_attr( $woo_id ).'">'.$woo_metabox['label'].'</label></th>'."\n";
                    $output .= "\t\t".'<td><input class="woo_input_text '.$add_class.'" type="'.$woo_metabox['type'].'" value="'.esc_attr( $woo_metaboxvalue ).'" name="'.$woo_name.'" id="'.esc_attr( $woo_id ).'"/>';
                    $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'] .' '. $add_counter .'</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }

                elseif ( $woo_metabox['type'] == 'textarea' ) {

                    $add_class = ''; $add_counter = '';
                    if( $template_to_show == 'seo' ){ $add_class = 'words-count'; $add_counter = '<span class="counter">0 characters, 0 words</span>'; }
                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox.'">'.$woo_metabox['label'].'</label></th>'."\n";
                    $output .= "\t\t".'<td><textarea class="woo_input_textarea '.$add_class.'" name="'.$woo_name.'" id="'.esc_attr( $woo_id ).'">' . esc_textarea(stripslashes($woo_metaboxvalue)) . '</textarea>';
                    $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'] .' '. $add_counter.'</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }

                elseif ( $woo_metabox['type'] == 'calendar' ) {

                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox.'">'.$woo_metabox['label'].'</label></th>'."\n";
                    $output .= "\t\t".'<td><input class="woo_input_calendar" type="text" name="'.$woo_name.'" id="'.esc_attr( $woo_id ).'" value="'.esc_attr( $woo_metaboxvalue ).'">';
                    $output .= "\t\t" . '<input type="hidden" name="datepicker-image" value="' . get_template_directory_uri() . '/functions/images/calendar.gif" />';
                    $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'].'</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }

                elseif ( $woo_metabox['type'] == 'time' ) {

                    $output .= "\t".'<tr>';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
                    $output .= "\t\t".'<td><input class="woo_input_time" type="' . $woo_metabox['type'] . '" value="' . esc_attr( $woo_metaboxvalue ) . '" name="' . $woo_name . '" id="' . esc_attr( $woo_id ) . '"/>';
                    $output .= '<span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }

                elseif ( $woo_metabox['type'] == 'time_masked' ) {

                    $output .= "\t".'<tr>';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
                    $output .= "\t\t".'<td><input class="woo_input_time_masked" type="' . $woo_metabox['type'] . '" value="' . esc_attr( $woo_metaboxvalue ) . '" name="' . $woo_name . '" id="' . esc_attr( $woo_id ) . '"/>';
                    $output .= '<span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }

                elseif ( $woo_metabox['type'] == 'select' ) {

                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
                    $output .= "\t\t".'<td><select class="woo_input_select" id="' . esc_attr( $woo_id ) . '" name="' . esc_attr( $woo_name ) . '">';

                    $array = $woo_metabox['options'];

                    if( $array ) {

                        foreach ( $array as $id => $option ) {
                            $selected = '';

                            if (empty($woo_metaboxvalue)) {
                                if( isset( $woo_metabox['default'] ) )  {
                                    if( $woo_metabox['default'] == $option) {
                                        $selected = 'selected="selected"';
                                    }
                                }
                            } else {
                                if( $woo_metaboxvalue == $option ){
                                    $selected = 'selected="selected"';
                                }
                            }

                            $output .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . $option . '</option>';
                        }
                    }

                    $output .= '</select><span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";
                }
                elseif ( $woo_metabox['type'] == 'select2' ) {

                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
                    $output .= "\t\t".'<td><select class="woo_input_select" id="' . esc_attr( $woo_id ) . '" name="' . esc_attr( $woo_name ) . '">';
                    $output .= '<option value="">Select to return to default</option>';

                    $array = $woo_metabox['options'];

                    if( $array ) {

                        foreach ( $array as $id => $option ) {
                            $selected = '';

                            if( isset( $woo_metabox['default'] ) )  {
                                if( $woo_metabox['default'] == $id && empty( $woo_metaboxvalue ) ) { $selected = 'selected="selected"'; }
                                else  { $selected = ''; }
                            }

                            if( $woo_metaboxvalue == $id ) { $selected = 'selected="selected"'; }
                            else  {$selected = '';}

                            $output .= '<option value="'. esc_attr( $id ) .'" '. $selected .'>' . $option . '</option>';
                        }
                    }

                    $output .= '</select><span class="woo_metabox_desc">'.$woo_metabox['desc'].'</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";
                }

                elseif ( $woo_metabox['type'] == 'checkbox' ){

                    if( $woo_metaboxvalue == 'true' ) { $checked = ' checked="checked"'; } else { $checked=''; }

                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.esc_attr( $woo_id ).'">'.$woo_metabox['label'].'</label></th>'."\n";
                    $output .= "\t\t".'<td><input type="checkbox" '.$checked.' class="woo_input_checkbox" value="true"  id="'.esc_attr( $woo_id ).'" name="'. esc_attr( $woo_name ) .'" />';
                    $output .= '<span class="woo_metabox_desc" style="display:inline">'.$woo_metabox['desc'].'</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";
                }

                elseif ( $woo_metabox['type'] == 'radio' ) {

                    $array = $woo_metabox['options'];

                    if( $array ) {

                        $output .= "\t".'<tr class="' . $row_css_class . '">';
                        $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
                        $output .= "\t\t".'<td>';

                        foreach ( $array as $id => $option ) {
                            if($woo_metaboxvalue == $id) { $checked = ' checked'; } else { $checked=''; }

                            $output .= '<input type="radio" '.$checked.' value="' . $id . '" class="woo_input_radio"  name="'. esc_attr( $woo_name ) .'" />';
                            $output .= '<span class="woo_input_radio_desc" style="display:inline">'. $option .'</span><div class="woo_spacer"></div>';
                        }
                        $output .= "\t".'</tr>'."\n";
                    }
                } elseif ( $woo_metabox['type'] == 'images' ) {

                    $i = 0;
                    $select_value = '';
                    $layout = '';

                    foreach ( $woo_metabox['options'] as $key => $option ) {
                        $i++;

                        $checked = '';
                        $selected = '';
                        if( $woo_metaboxvalue != '' ) {
                            if ( $woo_metaboxvalue == $key ) { $checked = ' checked'; $selected = 'woo-meta-radio-img-selected'; }
                        }
                        else {
                            if ( isset( $option['std'] ) && $key == $option['std'] ) { $checked = ' checked'; }
                            elseif ( $i == 1 ) { $checked = ' checked'; $selected = 'woo-meta-radio-img-selected'; }
                            else { $checked = ''; }

                        }

                        $layout .= '<div class="woo-meta-radio-img-label">';
                        $layout .= '<input type="radio" id="woo-meta-radio-img-' . $woo_name . $i . '" class="checkbox woo-meta-radio-img-radio" value="' . esc_attr($key) . '" name="' . $woo_name . '" ' . $checked . ' />';
                        $layout .= '&nbsp;' . esc_html($key) . '<div class="woo_spacer"></div></div>';
                        $layout .= '<img src="' . esc_url( $option ) . '" alt="" class="woo-meta-radio-img-img '. $selected .'" onClick="document.getElementById(\'woo-meta-radio-img-'. esc_js( $woo_metabox["name"] . $i ) . '\').checked = true;" />';
                    }

                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="' . esc_attr( $woo_id ) . '">' . $woo_metabox['label'] . '</label></th>'."\n";
                    $output .= "\t\t".'<td class="woo_metabox_fields">';
                    $output .= $layout;
                    $output .= '<span class="woo_metabox_desc">' . $woo_metabox['desc'] . '</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }

                elseif( $woo_metabox['type'] == 'upload' )
                {
                    if( isset( $woo_metabox['default'] ) ) $default = $woo_metabox['default'];
                    else $default = '';

                    // Add support for the WooThemes Media Library-driven Uploader Module // 2010-11-09.
                    if ( function_exists( 'woothemes_medialibrary_uploader' ) ) {

                        $_value = $default;

                        $_value = get_post_meta( $post->ID, $woo_metabox['name'], true );

                        $output .= "\t".'<tr class="' . $row_css_class . '">';
                        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox['name'].'">'.$woo_metabox['label'].'</label></th>'."\n";
                        $output .= "\t\t".'<td class="woo_metabox_fields">'. woothemes_medialibrary_uploader( $woo_metabox['name'], $_value, 'postmeta', $woo_metabox['desc'], $post->ID );
                        $output .= '</td>'."\n";
                        $output .= "\t".'</tr>'."\n";

                    } else {

                        $output .= "\t".'<tr class="' . $row_css_class . '">';
                        $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.esc_attr( $woo_id ).'">'.$woo_metabox['label'].'</label></th>'."\n";
                        $output .= "\t\t".'<td class="woo_metabox_fields">'. woothemes_uploader_custom_fields( $post->ID, $woo_name, $default, $woo_metabox['desc'] );
                        $output .= '</td>'."\n";
                        $output .= "\t".'</tr>'."\n";

                    }
                }

                // Timestamp field.
                elseif ( $woo_metabox['type'] == 'timestamp' ) {
                    $woo_metaboxvalue = get_post_meta($post->ID,$woo_name,true);

                    // Default to current UNIX timestamp.
                    if ( $woo_metaboxvalue == '' ) {
                        $woo_metaboxvalue = time();
                    }

                    $output .= "\t".'<tr class="' . $row_css_class . '">';
                    $output .= "\t\t".'<th class="woo_metabox_names"><label for="'.$woo_metabox.'">'.$woo_metabox['label'].'</label></th>'."\n";
                    $output .= "\t\t".'<td><input type="hidden" name="datepicker-image" value="' . admin_url( 'images/date-button.gif' ) . '" /><input class="woo_input_calendar" type="text" name="'.$woo_name.'[date]" id="'.esc_attr( $woo_id ).'" value="' . esc_attr( date( 'm/d/Y', $woo_metaboxvalue ) ) . '">';

                    $output .= ' <span class="woo-timestamp-at">' . __( '@', 'woothemes' ) . '</span> ';

                    $output .= '<select name="' . $woo_name . '[hour]" class="woo-select-timestamp">' . "\n";
                    for ( $i = 0; $i <= 23; $i++ ) {

                        $j = $i;
                        if ( $i < 10 ) {
                            $j = '0' . $i;
                        }

                        $output .= '<option value="' . $i . '"' . selected( date( 'H', $woo_metaboxvalue ), $j, false ) . '>' . $j . '</option>' . "\n";
                    }
                    $output .= '</select>' . "\n";

                    $output .= '<select name="' . $woo_name . '[minute]" class="woo-select-timestamp">' . "\n";
                    for ( $i = 0; $i <= 59; $i++ ) {

                        $j = $i;
                        if ( $i < 10 ) {
                            $j = '0' . $i;
                        }

                        $output .= '<option value="' . $i . '"' . selected( date( 'i', $woo_metaboxvalue ), $j, false ) .'>' . $j . '</option>' . "\n";
                    }
                    $output .= '</select>' . "\n";
                    /*
                    $output .= '<select name="' . $woo_name . '[second]" class="woo-select-timestamp">' . "\n";
                        for ( $i = 0; $i <= 59; $i++ ) {

                            $j = $i;
                            if ( $i < 10 ) {
                                $j = '0' . $i;
                            }

                            $output .= '<option value="' . $i . '"' . selected( date( 's', $woo_metaboxvalue ), $j, false ) . '>' . $j . '</option>' . "\n";
                        }
                    $output .= '</select>' . "\n";
                    */
                    $output .= '<span class="woo_metabox_desc">'.$woo_metabox['desc'].'</span></td>'."\n";
                    $output .= "\t".'</tr>'."\n";

                }
            } // End IF Statement
        }

        $output .= '</table>'."\n\n";
        $output .= '</div><!--/#wf-tab-' . $token . '-->' . "\n\n";

        return $output;
    }


    public function metabox_handle($post_id) {
        $pID = '';
        global $globals, $post;

        if (!isset($_POST['post_type'])) {
            return $post_id;
        }

        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        $woo_metaboxes = $this->metabox_fields();

        // Sanitize post ID.
        if( isset( $_POST['post_ID'] ) ) {
            $pID = intval( $_POST['post_ID'] );
        }

        // Don't continue if we don't have a valid post ID.
        if ( $pID == 0 ) return;

        $upload_tracking = array();

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ) {
            if ( ( get_post_type() != '' ) && ( get_post_type() != 'nav_menu_item' ) && wp_verify_nonce( $_POST['wooframework-custom-fields-nonce'], 'wooframework-custom-fields' ) ) {
                foreach ( $woo_metaboxes as $k => $woo_metabox ) { // On Save.. this gets looped in the header response and saves the values submitted
                    if( isset( $woo_metabox['type'] ) && ( in_array( $woo_metabox['type'], woothemes_metabox_fieldtypes() ) ) ) {
                        $var = $woo_metabox['name'];

                        // Get the current value for checking in the script.
                        $current_value = '';
                        $current_value = get_post_meta( $pID, $var, true );

                        if ( isset( $_POST[$var] ) ) {
                            // Sanitize the input.
                            $posted_value = '';
                            $posted_value = $_POST[$var];

                            // If it doesn't exist, add the post meta.
                            if(get_post_meta( $pID, $var ) == "") {
                                add_post_meta( $pID, $var, $posted_value, true );
                            }
                            // Otherwise, if it's different, update the post meta.
                            elseif( $posted_value != get_post_meta( $pID, $var, true ) ) {
                                update_post_meta( $pID, $var, $posted_value );
                            }
                            // Otherwise, if no value is set, delete the post meta.
                            elseif($posted_value == "") {
                                delete_post_meta( $pID, $var, get_post_meta( $pID, $var, true ) );
                            } // End IF Statement
                        } elseif ( ! isset( $_POST[$var] ) && $woo_metabox['type'] == 'checkbox' ) {
                            update_post_meta( $pID, $var, 'false' );
                        } else {
                            delete_post_meta( $pID, $var, $current_value ); // Deletes check boxes OR no $_POST
                        } // End IF Statement

                    } else if ( $woo_metabox['type'] == 'timestamp' ) {
                        // Timestamp save logic.

                        // It is assumed that the data comes back in the following format:
                        // date: month/day/year
                        // hour: int(2)
                        // minute: int(2)
                        // second: int(2)

                        $var = $woo_metabox['name'];

                        // Format the data into a timestamp.
                        $date = $_POST[$var]['date'];

                        $hour = $_POST[$var]['hour'];
                        $minute = $_POST[$var]['minute'];
                        // $second = $_POST[$var]['second'];
                        $second = '00';

                        $day = substr( $date, 3, 2 );
                        $month = substr( $date, 0, 2 );
                        $year = substr( $date, 6, 4 );

                        $timestamp = mktime( $hour, $minute, $second, $month, $day, $year );

                        update_post_meta( $pID, $var, $timestamp );
                    } elseif( isset( $woo_metabox['type'] ) && $woo_metabox['type'] == 'upload' ) { // So, the upload inputs will do this rather
                        $id = $woo_metabox['name'];
                        $override['action'] = 'editpost';

                        if(!empty($_FILES['attachement_'.$id]['name'])){ //New upload
                            $_FILES['attachement_'.$id]['name'] = preg_replace( '/[^a-zA-Z0-9._\-]/', '', $_FILES['attachement_'.$id]['name']);
                            $uploaded_file = wp_handle_upload($_FILES['attachement_' . $id ],$override);
                            $uploaded_file['option_name']  = $woo_metabox['label'];
                            $upload_tracking[] = $uploaded_file;
                            update_post_meta( $pID, $id, $uploaded_file['url'] );
                        } elseif ( empty( $_FILES['attachement_'.$id]['name'] ) && isset( $_POST[ $id ] ) ) {
                            // Sanitize the input.
                            $posted_value = '';
                            $posted_value = $_POST[$id];

                            update_post_meta($pID, $id, $posted_value);
                        } elseif ( $_POST[ $id ] == '' )  {
                            delete_post_meta( $pID, $id, get_post_meta( $pID, $id, true ) );
                        } // End IF Statement

                    } // End IF Statement

                    // Error Tracking - File upload was not an Image
                    update_option( 'woo_custom_upload_tracking', $upload_tracking );
                } // End FOREACH Loop
            }
        }
    }

    public function load_script() {

    }

    public function load_admin_script() {
    }

    public function option_css() {

        $isWooCommerceInstalled = isset($GLOBALS['woocommerce']);

        if ($isWooCommerceInstalled) {
            if (is_home() || (is_post_type_archive() && !is_shop())) {
                return;
            }

            if (is_shop()) {
                $postID = woocommerce_get_page_id('shop');
            } else {
                $postID = get_the_ID();
            }
        } else {
            if (is_home() || is_post_type_archive()) {
                return;
            }

            $postID = get_the_ID();
        }


        global $woo_options;
        $isFullWidth = (isset( $woo_options['woo_header_full_width'] ) && $woo_options['woo_header_full_width'] == 'true');

        $css = '';
        if (!$isFullWidth) {
            $headerImage = get_post_meta($postID, 'header_image', true);
            $headerImageRepeat = get_post_meta($postID, 'header_image_repeat', true);

            if (!empty($headerImage)) {
                $headerCss = '';

                $headerCss .= 'background-image:url('.$headerImage.'); ';
        		if (empty($headerImageRepeat)) {
                    $headerImageRepeat = 'no-repeat';
                }

                $headerCss .= ' background-repeat:'.$headerImageRepeat.'; background-position:left top;';


                $css .= "#header { " . $headerCss . " }\n";
            }
        } else {
            $headerImage = get_post_meta($postID, 'header_image_full_width', true);
            $headerImageRepeat = get_post_meta($postID, 'header_image_repeat_full_width', true);
            $headerCss = '';
            if (!empty($headerImage)) {
                if (empty($headerImageRepeat)) {
                    $headerImageRepeat = 'no-repeat';
                }
                $headerCss .= '#header-container { background-image: url(' . $headerImage . ');background-repeat:' . $headerImageRepeat . ';background-position:top center;}';
                $headerCss .= "#header { background-image: none; }\n";
            }
            $css .= $headerCss;
        }

        echo "<style>".$css."</style>";

    }

	/**
	 * Load stylesheet required for the style, if has any.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function load_stylesheet () {

	} // End load_stylesheet()

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( $this->token, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = $this->token;
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( $this->token . '-version', $this->version );
		}
	} // End register_plugin_version()


} // End Class


