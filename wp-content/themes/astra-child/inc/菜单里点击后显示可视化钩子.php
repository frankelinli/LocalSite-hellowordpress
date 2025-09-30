<?php
/**
 * 功能名称: 菜单里点击后显示可视化钩子
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


/**
 * 在主菜单的末尾添加一个"显示钩子"按钮
 */
function haowiki_add_hook_viewer_to_menu($items, $args) {
    // 只在前台显示，并且只对管理员显示
    if (is_admin() || !current_user_can('administrator')) {
        return $items;
    }
    
    // 确保我们只在主菜单添加按钮
    // 注意：'primary'是大多数主题的主菜单位置，您可能需要根据Astra主题调整
    if ($args->theme_location != 'primary') {
        return $items;
    }
    
    // 获取当前URL
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    // 检查URL中是否已有参数
    $separator = (strpos($current_url, '?') !== false) ? '&' : '?';
    
    // 生成带有show_hooks参数的URL
    $hooks_url = $current_url . $separator . 'show_hooks=1';
    
    // 创建按钮HTML
    $hook_button = '<li class="menu-item hook-viewer-menu-item">';
    $hook_button .= '<a href="' . esc_url($hooks_url) . '" style="background-color: #2271b1; color: white; padding: 0 15px; border-radius: 3px; margin-left: 10px;">';
    $hook_button .= '显示钩子';
    $hook_button .= '</a></li>';
    
    // 将按钮添加到菜单末尾
    return $items . $hook_button;
}
add_filter('wp_nav_menu_items', 'haowiki_add_hook_viewer_to_menu', 10, 2);

/**
 * 为菜单中的钩子按钮添加样式
 */
function haowiki_add_menu_button_styles() {
    // 只在前台为管理员添加样式
    if (is_admin() || !current_user_can('administrator')) {
        return;
    }
    
    ?>
    <style>
        .hook-viewer-menu-item a {
            background-color: #2271b1 !important;
            color: white !important;
            transition: background-color 0.3s !important;
        }
        .hook-viewer-menu-item a:hover {
            background-color: #135e96 !important;
            color: white !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'haowiki_add_menu_button_styles');