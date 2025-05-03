<?php
/**
 * Plugin Name: Simple Changelog
 * Plugin URI: 
 * Description: 添加更新日志(Changelog)自定义文章类型和显示模板
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: simple-changelog
 */

// 如果直接访问此文件，则退出
if (!defined('ABSPATH')) {
    exit;
}

// 注册自定义文章类型 Changelog
function register_changelog_post_type() {
    register_post_type(
        'changelog',
        array(
            'labels' => array(
                'name' => __('Changelog', 'simple-changelog'),
                'singular_name' => __('Changelog Entry', 'simple-changelog'),
                'add_new' => __('Add New Entry', 'simple-changelog'),
                'add_new_item' => __('Add New Changelog Entry', 'simple-changelog'),
                'edit_item' => __('Edit Changelog Entry', 'simple-changelog'),
            ),
            'public' => true,
            'publicly_queryable' => false, // 禁用单个文章页面访问
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array('title', 'editor', 'author'),
            'menu_icon' => 'dashicons-backup',
            'show_in_rest' => true, // 启用古腾堡编辑器支持
        )
    );
}
add_action('init', 'register_changelog_post_type');

// 添加自定义模板页面
function changelog_page_template($templates) {
    $templates['changelog-template.php'] = 'Changelog Template';
    return $templates;
}
add_filter('theme_page_templates', 'changelog_page_template');

// 加载自定义模板
function changelog_template_include($template) {
    if (is_page_template('changelog-template.php')) {
        $new_template = plugin_dir_path(__FILE__) . 'changelog-template.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'changelog_template_include');

// 注册和加载样式
function changelog_enqueue_styles() {
    wp_enqueue_style(
        'changelog-styles',
        plugin_dir_url(__FILE__) . 'changelog.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'changelog_enqueue_styles');

// 插件激活时运行的函数
function changelog_plugin_activation() {
    register_changelog_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'changelog_plugin_activation');

// 插件停用时运行的函数
function changelog_plugin_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'changelog_plugin_deactivation');