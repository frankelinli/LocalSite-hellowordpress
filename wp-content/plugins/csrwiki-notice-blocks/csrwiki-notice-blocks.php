<?php
/**
 * Plugin Name: CSRWiki 提示区块插件
 * Plugin URI: https://example.com/plugins/csrwiki-notice-blocks/
 * Description: 为WordPress古腾堡编辑器添加提示块、信息块、警告块和文末注释块等自定义区块
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: csrwiki-notice-blocks
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// 如果直接访问则退出
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件目录路径和URL
define('CSRWIKI_BLOCKS_DIR', plugin_dir_path(__FILE__));
define('CSRWIKI_BLOCKS_URL', plugin_dir_url(__FILE__));

// 注册 Note 区块
function csrwiki_register_note_block()
{
    // 注册编辑器端脚本 simple-note-block.js，依赖 wp-blocks 和 wp-element
    wp_register_script(
        'simple-note-block',
        CSRWIKI_BLOCKS_URL . 'simple-note-block.js',
        array('wp-blocks', 'wp-element')
    );
    
    // 注册样式文件，仅在使用此区块时加载
    wp_register_style(
        'simple-note-block-style',
        CSRWIKI_BLOCKS_URL . 'simple-note-block.css',
        array(),
        filemtime(CSRWIKI_BLOCKS_DIR . 'simple-note-block.css')
    );

    // 注册区块类型 csrwiki/simple-note，并关联上面注册的脚本和样式
    register_block_type('csrwiki/simple-note', array(
        'editor_script' => 'simple-note-block',
        'style' => 'simple-note-block-style', // 前端样式
        'editor_style' => 'simple-note-block-style', // 编辑器样式
    ));
}
// 在 WordPress 初始化时注册 Note 区块
add_action('init', 'csrwiki_register_note_block');

// 注册 Tip 区块
function csrwiki_register_tip_block()
{
    // 注册编辑器端脚本 tip-block.js，依赖 wp-blocks、wp-element、wp-editor、wp-components
    wp_register_script(
        'tip-block',
        CSRWIKI_BLOCKS_URL . 'tip-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components')
    );
    
    // 注册样式文件，仅在使用此区块时加载
    wp_register_style(
        'tip-block-style',
        CSRWIKI_BLOCKS_URL . 'tip-block.css',
        array(),
        filemtime(CSRWIKI_BLOCKS_DIR . 'tip-block.css')
    );

    // 注册区块类型 csrwiki/tip，并关联上面注册的脚本和样式
    register_block_type('csrwiki/tip', array(
        'editor_script' => 'tip-block',
        'style' => 'tip-block-style', // 前端样式
        'editor_style' => 'tip-block-style', // 编辑器样式
    ));
}
// 在 WordPress 初始化时注册 Tip 区块
add_action('init', 'csrwiki_register_tip_block');

// 注册 Warning 区块
function csrwiki_register_warning_block()
{
    // 注册编辑器端脚本 warning-block.js，依赖 wp-blocks、wp-element、wp-editor、wp-components
    wp_register_script(
        'warning-block',
        CSRWIKI_BLOCKS_URL . 'warning-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components')
    );
    
    // 注册样式文件，仅在使用此区块时加载
    wp_register_style(
        'warning-block-style',
        CSRWIKI_BLOCKS_URL . 'warning-block.css',
        array(),
        filemtime(CSRWIKI_BLOCKS_DIR . 'warning-block.css')
    );

    // 注册区块类型 csrwiki/warning，并关联上面注册的脚本和样式
    register_block_type('csrwiki/warning', array(
        'editor_script' => 'warning-block',
        'style' => 'warning-block-style', // 前端样式
        'editor_style' => 'warning-block-style', // 编辑器样式
    ));
}
// 在 WordPress 初始化时注册 Warning 区块
add_action('init', 'csrwiki_register_warning_block');


// 注册 Info 区块
function csrwiki_register_info_block()
{
    // 注册编辑器端脚本 info-block.js，依赖 wp-blocks、wp-element、wp-editor、wp-components
    wp_register_script(
        'info-block',
        CSRWIKI_BLOCKS_URL . 'info-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components')
    );
    
    // 注册样式文件，仅在使用此区块时加载
    wp_register_style(
        'info-block-style',
        CSRWIKI_BLOCKS_URL . 'info-block.css',
        array(),
        filemtime(CSRWIKI_BLOCKS_DIR . 'info-block.css')
    );

    // 注册区块类型 csrwiki/info，并关联上面注册的脚本和样式
    register_block_type('csrwiki/info', array(
        'editor_script' => 'info-block',
        'style' => 'info-block-style', // 前端样式
        'editor_style' => 'info-block-style', // 编辑器样式
    ));
}
// 在 WordPress 初始化时注册 Info 区块
add_action('init', 'csrwiki_register_info_block');