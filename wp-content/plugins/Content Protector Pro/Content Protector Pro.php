<?php
/*
Plugin Name: Content Protector Pro
Plugin URI: https://yourwebsite.com
Description: 文章内容保护插件
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

class ContentProtectorPro {
    private static $instance = null;
    
    // 获取实例
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // 构造函数
    private function __construct() {
        // 在构造函数中添加钩子
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        // 其他钩子...
    }

    // 加载样式
    public function enqueue_styles() {
        wp_enqueue_style(
            'content-protector', 
            plugins_url('assets/css/style.css', __FILE__),
            array(),
            '1.0.0'
        );
    }

    // 添加管理菜单
    public function add_admin_menu() {
        add_menu_page(
            '内容保护设置',
            '内容保护',
            'manage_options',
            'content-protector',
            array($this, 'admin_page'),
            'dashicons-lock'
        );
    }

    // 管理页面显示
    public function admin_page() {
        ?>
        <div class="wrap">
            <h2>内容保护设置</h2>
            <!-- 设置表单内容 -->
        </div>
        <?php
    }
}

// 初始化插件
function init_content_protector_pro() {
    return ContentProtectorPro::getInstance();
}

// 启动插件
add_action('plugins_loaded', 'init_content_protector_pro');

// 插件激活时的处理
register_activation_hook(__FILE__, 'content_protector_activate');
function content_protector_activate() {
    // 激活时的处理代码
}

// 插件停用时的处理
register_deactivation_hook(__FILE__, 'content_protector_deactivate');
function content_protector_deactivate() {
    // 停用时的处理代码
}