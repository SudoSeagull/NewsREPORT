<?php
/**
 * Theme functions for CP Drudge Theme
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Enqueue Bootstrap + style
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bootstrap-5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', [], '5.3.3');
    wp_enqueue_style('drudge-style', get_stylesheet_uri(), ['bootstrap-5'], '1.0.1');
    wp_enqueue_script('bootstrap-5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], '5.3.3', true);
});

// Title tag support
add_theme_support('title-tag');

// Register widget areas for ads
add_action('widgets_init', function () {
    register_sidebar([
        'name' => 'Header Ad',
        'id'   => 'ad_header',
        'before_widget' => '<div class="ad-slot">',
        'after_widget'  => '</div>',
    ]);
    register_sidebar([
        'name' => 'Footer Ad',
        'id'   => 'ad_footer',
        'before_widget' => '<div class="ad-slot">',
        'after_widget'  => '</div>',
    ]);
});

// Helper: read external URL from plugin's meta key if present
function drudge_get_external_url($post_id = null) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $url = get_post_meta($post_id, '_drudge_external_url', true);
    if ( ! $url ) { $url = get_permalink($post_id); }
    return esc_url($url);
}

// Helper: safe rel attrs
function drudge_rel_attr() {
    return 'rel="noopener external nofollow"';
}

// Add body class
add_filter('body_class', function($classes){
    $classes[] = 'drudge';
    return $classes;
});
