<?php
/**
 * 功能名称: 给私密文章标题加锁
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


function haowiki_customize_private_post_titles() {
    // 使用filter钩子修改私密文章的标题格式
    add_filter('private_title_format', 'haowiki_custom_private_title');
    
    // 使用filter钩子修改密码保护文章的标题格式
    add_filter('protected_title_format', 'haowiki_custom_protected_title');
}
add_action('init', 'haowiki_customize_private_post_titles');

// 回调函数 - 自定义私密文章标题格式
function haowiki_custom_private_title($format) {
    // 返回新的格式，%s 是原标题的占位符
    return '🔒私密文章： %s';
}

// 回调函数 - 自定义密码保护文章标题格式
function haowiki_custom_protected_title($format) {
    return '🔑 %s';
}