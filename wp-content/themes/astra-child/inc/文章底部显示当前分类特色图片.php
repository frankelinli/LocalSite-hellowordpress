<?php
/**
 * 功能名称: 文章底部显示当前分类特色图片
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


// 获取ACF当前分类的特色图像, 使用 Astra 钩子在文章内容底部显示分类特色图像
//前提是安装了ACF插件，并设置了分类特色图片
function get_current_category_featured_image()
{
    if (is_single()) { // 确保只在单篇文章页面执行
        // 获取当前文章的分类
        $categories = get_the_category();
        if (!empty($categories)) {
            // 获取第一个分类（或主分类）
            $category_id = $categories[0]->term_id;

            // 从 ACF 获取分类的特色图像
            $category_featured_image = get_field('category_featured_image', 'category_' . $category_id);

            // 如果存在分类特色图像，返回 HTML
            if ($category_featured_image && isset($category_featured_image['url'])) {
                return '<div class="category-featured-image">
                            <img src="' . esc_url($category_featured_image['url']) . '" alt="' . esc_attr($categories[0]->name) . '" style="width: 100%; max-width: 800px; height: auto;">
                        </div>';
            }
        }
    }
    return ''; // 如果没有图片或不在单篇文章页面，返回空
}
add_action('astra_entry_bottom', 'display_category_featured_image');
function display_category_featured_image()
{
    // 获取并输出分类的特色图像
    echo get_current_category_featured_image();
}

