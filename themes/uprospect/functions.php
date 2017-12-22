<?php

/** Constants */
defined('THEME_URI') || define('THEME_URI', get_template_directory_uri());
defined('THEME_PATH') || define('THEME_PATH', realpath(__DIR__));

include_once THEME_PATH . '/includes/functions.php';
require_once THEME_PATH . '/includes/register-sidebar.php';

// Constants
defined('DISALLOW_FILE_EDIT') || define('DISALLOW_FILE_EDIT', FALSE);
defined('TEXT_DOMAIN') || define('TEXT_DOMAIN', 'jp-basic');
define('JPB_THEME_PATH', realpath(__DIR__));

/*
  Favicon Admin
 */

function favicon() {
    echo '<link rel="shortcut icon" href="', get_template_directory_uri(), '/favicon.ico" />', "\n";
}

add_action('admin_head', 'favicon');

/**
 * Add scripts and styles to all Admin pages
 */
function jscustom_admin_scripts() {
    wp_enqueue_media();
    wp_register_script('custom-upload', get_template_directory_uri() . '/js/media-uploader.js', array('jquery'));
    wp_enqueue_script('custom-upload');
}

add_action('admin_print_scripts', 'jscustom_admin_scripts');

add_filter('update_footer', 'right_admin_footer_text_output', 11);

function right_admin_footer_text_output($text) {
    $text = 'Develop by Alexander Contreras';
    return $text;
}

//Theme settings
require(get_template_directory() . '/inc/theme-options.php');


//include_once __DIR__ . '/includes/register-script.php';
include_once __DIR__ . '/includes/register-script-local.php';
include_once __DIR__ . '/includes/register-style.php';
//include_once __DIR__ . '/includes/register-style-local.php';

add_action('wp_enqueue_scripts', function () {

    /* Styles */
    wp_enqueue_style('bootstrap');
    wp_enqueue_style('animate');
    wp_enqueue_style('hover');
    wp_enqueue_style('font-awesome');
    // Theme
    wp_enqueue_style('main-theme');

    /* Scripts */
    wp_enqueue_script('modernizr');
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap');
    wp_enqueue_script('jquery-form');

    // Bootstrap Alerts
    wp_register_script('bootstrap-alerts', apply_filters('js_cdn_uri', THEME_URI . '/js/bootstrap-alerts.min.js', 'bootstrap-alerts'), array('jquery', 'bootstrap'), NULL, TRUE);
    wp_enqueue_script('bootstrap-alerts');

    // Add defer atribute
    do_action('defer_script', array('jquery-form', 'bootstrap-alerts'));

    // Bootstrap complemetary text align
    wp_register_style('bs-text-align', THEME_URI . '/css/bootstrap-text-align.min.css', array('bootstrap'), '1.0');
    wp_enqueue_style('bs-text-align');

    // Wordpress Core
    wp_register_style('wordpress-core', THEME_URI . '/css/wordpress-core.min.css', array('bootstrap', 'bs-text-align'), '1.0');
    wp_enqueue_style('wordpress-core');

    if (is_child_theme()) {
        // Theme
        wp_register_style('theme', get_stylesheet_uri(), array('animate'), '1.0');
        wp_enqueue_style('theme');
    }
});

include_once __DIR__ . '/includes/theme-features.php';

/**
 * Encoded Mailto Link
 *
 * Create a spam-protected mailto link written in Javascript
 *
 * @param	string	the email address
 * @param	string	the link title
 * @param	mixed	any attributes
 * @return	string
 */
function safe_mailto($email, $title = '', $attributes = '') {
    $title = (string) $title;

    if ($title === '') {
        $title = $email;
    }

    $x = str_split('<a href="mailto:', 1);

    for ($i = 0, $l = strlen($email); $i < $l; $i++) {
        $x[] = '|' . ord($email[$i]);
    }

    $x[] = '"';

    if ($attributes !== '') {
        if (is_array($attributes)) {
            foreach ($attributes as $key => $val) {
                $x[] = ' ' . $key . '="';
                for ($i = 0, $l = strlen($val); $i < $l; $i++) {
                    $x[] = '|' . ord($val[$i]);
                }
                $x[] = '"';
            }
        } else {
            for ($i = 0, $l = strlen($attributes); $i < $l; $i++) {
                $x[] = $attributes[$i];
            }
        }
    }

    $x[] = '>';

    $temp = array();
    for ($i = 0, $l = strlen($title); $i < $l; $i++) {
        $ordinal = ord($title[$i]);

        if ($ordinal < 128) {
            $x[] = '|' . $ordinal;
        } else {
            if (count($temp) === 0) {
                $count = ($ordinal < 224) ? 2 : 3;
            }

            $temp[] = $ordinal;
            if (count($temp) === $count) {
                $number = ($count === 3) ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64) : (($temp[0] % 32) * 64) + ($temp[1] % 64);
                $x[] = '|' . $number;
                $count = 1;
                $temp = array();
            }
        }
    }

    $x[] = '<';
    $x[] = '/';
    $x[] = 'a';
    $x[] = '>';

    $x = array_reverse($x);

    $output = "<script type=\"text/javascript\">\n"
            . "\t//<![CDATA[\n"
            . "\tvar l=new Array();\n";

    for ($i = 0, $c = count($x); $i < $c; $i++) {
        $output .= "\tl[" . $i . "] = '" . $x[$i] . "';\n";
    }

    $output .= "\n\tfor (var i = l.length-1; i >= 0; i=i-1) {\n"
            . "\t\tif (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");\n"
            . "\t\telse document.write(unescape(l[i]));\n"
            . "\t}\n"
            . "\t//]]>\n"
            . '</script>';

    return $output;
}

require_once __DIR__ . '/admin/admin.php';


// Register Custom Navigation Walker
require_once('wp_bootstrap_navwalker.php');

class Custom_Walker extends Walker_Nav_Menu {

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        global $wp_query;
        $indent = ( $depth > 0 ? str_repeat("\t", $depth) : '' ); // code indent
        // depth dependent classes
        $depth_classes = array(
            ( $depth == 0 ? 'main-menu-item' : 'sub-menu-item' ),
            ( $depth >= 2 ? 'sub-sub-menu-item' : '' ),
            ( $depth % 2 ? 'menu-item-odd' : 'menu-item-even' ),
            'menu-item-depth-' . $depth
        );
        $depth_class_names = esc_attr(implode(' ', $depth_classes));

        // passed classes
        $classes = empty($item->classes) ? array() : (array) $item->classes;

        if (!in_array($item->object, array('custom'))) {
            $post_data = get_post($item->object_id);
            $classes[] = $post_data->post_type . '-' . $post_data->post_name;
        }

        $class_names = esc_attr(implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item)));

        // build html
        $output .= $indent . '<li id="nav-menu-item-' . $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . '">';

        // link attributes
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

        $item_output = sprintf('%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s', $args->before, $attributes, $args->link_before, apply_filters('the_title', $item->title, $item->ID), $args->link_after, $args->after
        );

        // build html
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

}

// require_once( get_stylesheet_directory() .'/includes/content-portfolio.php' );
// Register Custom Post Type
function custom_cards() {

    $labels = array(
        'name' => _x('Cards', 'Post Type General Name', 'jp-basic'),
        'singular_name' => _x('Card', 'Post Type Singular Name', 'jp-basic'),
        'menu_name' => __('Cards', 'jp-basic'),
        'name_admin_bar' => __('Cards', 'jp-basic'),
        'archives' => __('Item Archives', 'jp-basic'),
        'attributes' => __('Item Attributes', 'jp-basic'),
        'parent_item_colon' => __('Parent Item:', 'jp-basic'),
        'all_items' => __('All Items', 'jp-basic'),
        'add_new_item' => __('Add New Item', 'jp-basic'),
        'add_new' => __('Add New', 'jp-basic'),
        'new_item' => __('New Item', 'jp-basic'),
        'edit_item' => __('Edit Item', 'jp-basic'),
        'update_item' => __('Update Item', 'jp-basic'),
        'view_item' => __('View Item', 'jp-basic'),
        'view_items' => __('View Items', 'jp-basic'),
        'search_items' => __('Search Item', 'jp-basic'),
        'not_found' => __('Not found', 'jp-basic'),
        'not_found_in_trash' => __('Not found in Trash', 'jp-basic'),
        'featured_image' => __('Featured Image', 'jp-basic'),
        'set_featured_image' => __('Set featured image', 'jp-basic'),
        'remove_featured_image' => __('Remove featured image', 'jp-basic'),
        'use_featured_image' => __('Use as featured image', 'jp-basic'),
        'insert_into_item' => __('Insert into item', 'jp-basic'),
        'uploaded_to_this_item' => __('Uploaded to this item', 'jp-basic'),
        'items_list' => __('Items list', 'jp-basic'),
        'items_list_navigation' => __('Items list navigation', 'jp-basic'),
        'filter_items_list' => __('Filter items list', 'jp-basic'),
    );
    $args = array(
        'label' => __('Card', 'jp-basic'),
        'description' => __('Post Type Description', 'jp-basic'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-feedback',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => false,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'page',
    );
    register_post_type('cards', $args);
}

add_action('init', 'custom_cards', 0);


/* Facebook Head */

add_action('wp_head', function() {
    if (is_page() or is_singular()) {
        $current_post = get_post();
        $meta['og:title'] = esc_attr($current_post->post_title);
        $meta['og:description'] = esc_attr($current_post->post_excerpt);
        $meta['og:site_name'] = get_bloginfo('name');
        $meta['og:url'] = get_permalink($current_post->ID);
        $meta['og:image'] = wp_get_attachment_url(get_post_thumbnail_id($current_post->ID));
        foreach ($meta as $key => $value) {
            if (!empty($value)) {
                printf('<meta name="%s" content="%s" />', $key, $value);
                echo "\n";
            }
        }
    }
}, 1);

//Cut images
if (function_exists('add_image_size')) {
    add_image_size('card-image', 290, 310, true);
    add_image_size('blogimage', 280, 280, true);
}

// Here go metabox

function rw_register_meta_box() {
    if (!class_exists('RW_Meta_Box') or ! is_admin())
        return;
    $post_ID = !empty($_POST['post_ID']) ?
            $_POST['post_ID'] :
            (!empty($_GET['post']) ? $_GET['post'] : FALSE);

    $post_name = '';
    if ($post_ID) {
        $current_post = get_post($post_ID);
        if ($current_post) {
            $current_post_type = $current_post->post_type;
            $post_name = $current_post->post_name;
        } else {
            $post_name = '';
        }
    }

    $meta_box[] = array(
        'title' => 'Select card type',
        'pages' => array('cards'),
        'hidden' => array('post_format', 'aside'),
        'fields' => array(
            array(
                'id' => 'type',
                'name' => 'Type',
                'desc' => 'Select your type',
                'type' => 'select',
                'options' => array(
                    'card_multiple_options' => 'Multiple options',
                    'card_logo' => 'Logo',
                    'card_video' => 'Video'
                )
            ),
        )
    );

    $meta_box[] = array(
        'title' => 'Content in card',
        'pages' => array('cards'),
        'hidden' => array('post_format', 'aside'),
        'fields' => array(
            array(
                'name' => __('Logo', 'jp-basic'),
                'id' => 'cmo_logo',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_multiple_options')
            ),
            array(
                'name' => __('Image 01 box', 'jp-basic'),
                'id' => 'cmo_img_01',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_multiple_options')
            ),
            array(
                'name' => __('Image 02 box', 'jp-basic'),
                'id' => 'cmo_img_02',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_multiple_options')
            ),
            array(
                'name' => __('Image 03 box', 'jp-basic'),
                'id' => 'cmo_img_03',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_multiple_options')
            ),
            array(
                'name' => __('Image 04 box', 'jp-basic'),
                'id' => 'cmo_img_04',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_multiple_options')
            ),
            array(
                'name' => 'Title in card',
                'id' => 'cmo_title',
                'type' => 'text',
                'hidden' => array('type', '!=', 'card_multiple_options')
            ),
            array(
                'name' => __('Logo', 'jp-basic'),
                'id' => 'csl_logo',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_logo')
            ),
            array(
                'name' => __('Code video', 'jp-basic'),
                'id' => 'csv_code',
                'type' => 'text',
                'hidden' => array('type', '!=', 'card_video')
            ),
            array(
                'name' => __('Image video', 'jp-basic'),
                'id' => 'csv_image',
                'type' => 'image_advanced',
                'max_file_uploads' => 1,
                'hidden' => array('type', '!=', 'card_video')
            ),
        )
    );

    $meta_box[] = array(
        'title' => 'Social options',
        'pages' => array('cards'),
        'hidden' => array('post_format', 'aside'),
        'fields' => array(
            array(
                'name' => 'Contact email',
                'id' => 'surl_email',
                'type' => 'text',
            ),
            array(
                'name' => 'Map',
                'id' => 'surl_map',
                'type' => 'text',
            ),
            array(
                'name' => 'Mobile Number',
                'id' => 'surl_mnumber',
                'type' => 'text',
            ),
            array(
                'name' => 'Facebook',
                'id' => 'surl_fb',
                'type' => 'text',
            ),
            array(
                'name' => 'Twitter',
                'id' => 'surl_twitter',
                'type' => 'text',
            ),
            array(
                'name' => 'Linkedin',
                'id' => 'surl_in',
                'type' => 'text',
            ),
            array(
                'name' => 'Instagram',
                'id' => 'surl_ins',
                'type' => 'text',
            ),
        )
    );

    $meta_box[] = array(
        'title' => 'Menu in card',
        'pages' => array('cards'),
        'fields' => array(
            array(
                'id' => 'menu_card',
                'type' => 'group',
                'clone' => true,
                'sort_clone' => true,
                'fields' => array(
                    array(
                        'name' => 'Title',
                        'id' => 'menu_title',
                        'type' => 'text',
                    ),
                    array(
                        'name' => 'Url',
                        'id' => 'menu_url',
                        'type' => 'text',
                    )
                ),
            ),
        ),
    );


    if (is_array($meta_box)) {
        foreach ($meta_box as $value) {
            new RW_Meta_Box($value);
        }
    }
}

add_action('wp_ajax_rwmb_reorder_images', array("RWMB_Image_Field", 'wp_ajax_reorder_images'));
add_action('wp_ajax_rwmb_delete_file', array("RWMB_File_Field", 'wp_ajax_delete_file'));
add_action('wp_ajax_rwmb_attach_media', array("RWMB_Image_Advanced_Field", 'wp_ajax_attach_media'));
add_action('admin_init', 'rw_register_meta_box');


add_shortcode('cards', 'display_custom_post_type');

function display_custom_post_type($atts) {

    $atts = shortcode_atts(
            array('id' => ''), $atts, 'card');

    $args = array(
        'post__in' => array($atts['id']),
        'posts_per_page' => '1',
        'post_type' => 'cards',
        'caller_get_posts' => 1,
        'post_status' => 'publish'
    );

    $string = '';
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $string .= '';
        while ($query->have_posts()) {
            $query->the_post();
            /* Variables */
            $title = get_the_title();
            $content = get_the_content();
            $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'card-image');

            /* Single Metabox */
            $type = rwmb_meta('type');
            $cmo_title = rwmb_meta('cmo_title');
            $cmo_logo = rwmb_meta('cmo_logo', 'type=image&size=FULL');
            $img_01_box = rwmb_meta('cmo_img_01', 'type=image&size=FULL');
            $img_02_box = rwmb_meta('cmo_img_02', 'type=image&size=FULL');
            $img_03_box = rwmb_meta('cmo_img_03', 'type=image&size=FULL');
            $img_04_box = rwmb_meta('cmo_img_04', 'type=image&size=FULL');
            $csl_logo_url = rwmb_meta('csl_logo', 'type=image&size=FULL');
            $csv_image = rwmb_meta('csv_image', 'type=image&size=FULL');

            /* Variables Social */
            $email = rwmb_meta('surl_email');
            $map = rwmb_meta('surl_map');
            $mnumber = rwmb_meta('surl_mnumber');
            $fb = rwmb_meta('surl_fb');
            $tw = rwmb_meta('surl_twitter');
            $in = rwmb_meta('surl_in');
            $ins = rwmb_meta('surl_ins');


            /* Functions */
            foreach ($cmo_logo as $image_logo) {
                $img = $image_logo['full_url'];
            }
            foreach ($img_01_box as $img_01_box_loop) {
                $img_01 = $img_01_box_loop['full_url'];
            }
            foreach ($img_02_box as $img_02_box_loop) {
                $img_02 = $img_02_box_loop['full_url'];
            }
            foreach ($img_03_box as $img_03_box_loop) {
                $img_03 = $img_03_box_loop['full_url'];
            }
            foreach ($img_04_box as $img_04_box_loop) {
                $img_04 = $img_04_box_loop['full_url'];
            }

            foreach ($csl_logo_url as $csl_logo_url_big) {
                $img_logo_big = $csl_logo_url_big['full_url'];
            }

            foreach ($csv_image as $csv_image_url) {
                $csv_image_full = $csv_image_url['full_url'];
            }

            /* Output */
            $li = '<li>';
            $li_out = '</li>';

            /* Social */
            if (!empty($email)) {
                $email_li = $li . '<a href="mailto:' . $email . '"><i class="fa fa-envelope"></i></a>' . $li_out;
            }

            if (!empty($map)) {
                $map_li = $li . '<a target="_blank" href="' . $map . '"><i class="fa fa-map-marker"></i></a>' . $li_out;
            }

            if (!empty($mnumber)) {
                $mnumber_li = $li . '<a href="tel:' . $mnumber . '"><i class="fa fa-phone"></i></a>' . $li_out;
            }

            if (!empty($fb)) {
                $fb_li = $li . '<a target="_blank" href="' . $fb . '"><i class="fa fa-facebook"></i></a>' . $li_out;
            }

            if (!empty($tw)) {
                $tw_li = $li . '<a target="_blank" href="' . $tw . '"><i class="fa fa-twitter"></i></a>' . $li_out;
            }

            if (!empty($in)) {
                $in_li = $li . '<a target="_blank" href="' . $in . '"><i class="fa fa-linkedin"></i></a>' . $li_out;
            }

            if (!empty($ins)) {
                $ins_li = $li . '<a target="_blank" href="' . $ins . '"><i class="fa fa-instagram"></i></a>' . $li_out;
            }



            $menu_card = rwmb_meta('menu_card');
            if (!empty($menu_card)) {
                $li_menu = "";
                $ul_div = '<div class="menu-card" id="card-menu"><ul>';
                foreach ($menu_card as $menu_card_item) {
                    $url_menu = $menu_card_item['menu_url'];
                    $title_menu = $menu_card_item['menu_title'];
                    $li_menu .= '<li><a href="' . $url_menu . '">' . $title_menu . '</a></li>';
                }
                $ul_div_close = '</ul></div>';
            }




            if ($type == 'card_multiple_options') {
                $string .= '<div class="card">
                                <div class="card-content">
                                    <button class="button-toogle" id="toggle-menu">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </button>
                                    
                                        ' . $ul_div . $li_menu . $ul_div_close .'
                                    
                                    <div class="card-image">
                                        <img src="' . $featured_img_url . '" alt="' . $title . '">
                                    </div>
                                    <div class="card-description">
                                        
                                        <div class="card-logo text-center">
                                            <img src="' . $img . '">
                                        </div>
                                        <div class="card-boxes">
                                            <div class="box">
                                                <a href="#">
                                                    <img src="' . $img_01 . '">
                                                </a>
                                            </div>
                                            <div class="box">
                                                <a href="#">
                                                    <img src="' . $img_02 . '">
                                                </a>
                                            </div>
                                            <div class="box">
                                                <a href="#">
                                                    <img src="' . $img_03 . '">
                                                </a>
                                            </div>
                                            <div class="box">
                                                <a href="#">
                                                    <img src="' . $img_04 . '">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-title">
                                            <h2>
                                                ' . $cmo_title . '
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="card-name tg-verticalmiddle">
                                        <h2>
                                            ' . $title . '
                                        </h2>
                                        <p>
                                            ' . $content . '
                                        </p>
                                    </div>
                                    <div class="card-action tg-verticalmiddle">
                                        <div class="card-social text-center">
                                            <ul class="list-social">' . $email_li . $map_li . $mnumber_li . $fb_li . $tw_li . $in_li . $ins_li . '</ul>
                                        </div>
                                        <div class="card-button text-center">
                                            <a href="#">
                                                Schedule a Meeting
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
            }

            if ($type == 'card_logo') {
                $string .= '<div class="card">
                                <div class="card-content">
                                <button class="button-toogle" id="toggle-menu">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </button>
                                        ' .  $ul_div . $li_menu . $ul_div_close  . '
                                    <div class="card-image">
                                        <img src="' . $featured_img_url . '" alt="' . $title . '">
                                    </div>
                                    <div class="card-description">
                                        <div class="card-boxes card-boxes-one text-center">
                                                <div class="box">
                                                                <img src="' . $img_logo_big . '">
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="card-name tg-verticalmiddle">
                                        <h2>
                                            ' . $title . '
                                        </h2>
                                        <p>
                                            ' . $content . '
                                        </p>
                                    </div>
                                    <div class="card-action tg-verticalmiddle">
                                        <div class="card-social text-center">
                                            <ul class="list-social">' . $email_li . $map_li . $mnumber_li . $fb_li . $tw_li . $in_li . $ins_li . '</ul>
                                        </div>
                                        <div class="card-button text-center">
                                            <a href="#">
                                                Schedule a Meeting
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
            }
            if ($type == 'card_video') {
                $string .= '<div class="card">
                                <div class="card-content">
                                <button class="button-toogle" id="toggle-menu">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </button>
                                    ' .  $ul_div . $li_menu . $ul_div_close  . '
                                    <div class="card-image">
                                        <img src="' . $featured_img_url . '" alt="' . $title . '">
                                    </div>
                                    <div class="card-description">
                                        <div class="card-boxes card-boxes-one text-center">
                                                <div class="box">
                                                                <img src="' . $csv_image_full . '">
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="card-name tg-verticalmiddle">
                                        <h2>
                                            ' . $title . '
                                        </h2>
                                        <p>
                                            ' . $content . '
                                        </p>
                                    </div>
                                    <div class="card-action tg-verticalmiddle">
                                        <div class="card-social text-center">
                                            <ul class="list-social">' . $email_li . $map_li . $mnumber_li . $fb_li . $tw_li . $in_li . $ins_li . '</ul>
                                        </div>
                                        <div class="card-button text-center">
                                            <a href="#">
                                                Schedule a Meeting
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
            }
            $string .= '';
        }
        wp_reset_postdata();
        return $string;
    }
}

function shortcode_button_script() {
    if (wp_script_is("quicktags")) {
        ?>
        <script type="text/javascript">

            //this function is used to retrieve the selected text from the text editor
            function getSel()
            {
                var txtarea = document.getElementById("content");
                var start = txtarea.selectionStart;
                var finish = txtarea.selectionEnd;
                return txtarea.value.substring(start, finish);
            }

            QTags.addButton(
                    "code_shortcode",
                    "Cards",
                    callback
                    );

            function callback()
            {
                var selected_text = getSel();
                QTags.insertContent('[cards id=]');
            }
        </script>
        <?php

    }
}

add_action("admin_print_footer_scripts", "shortcode_button_script");

function register_my_menus() {
    register_nav_menus(
            array(
                'footer-menu' => __('Footer Menu'),
            )
    );
}

add_action('init', 'register_my_menus');

