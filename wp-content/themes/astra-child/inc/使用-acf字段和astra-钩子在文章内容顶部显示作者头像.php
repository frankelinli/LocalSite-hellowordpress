<?php
/**
 * 功能名称: 使用 ACF字段和Astra 钩子在文章内容顶部显示作者头像
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}



// / 使用 ACF字段和Astra 钩子在文章内容顶部显示作者头像
add_action('astra_entry_top', 'display_custom_author_avatar');

function display_custom_author_avatar()
{
    // 仅在单篇文章页面显示
    if (!is_single()) {
        return;
    }

    // 获取文章作者的 ID
    $author_id = get_the_author_meta('ID');

    // 获取作者头像字段（ACF）
    $author_avatar = get_field('custom_author_avatar', 'user_' . $author_id);

    // 如果设置了自定义头像，显示头像；否则显示默认 Gravatar
    if ($author_avatar) {
        echo '<div class="custom-author-avatar" style="text-align: center; margin-bottom: 20px;">';
        echo '<img src="' . esc_url($author_avatar['url']) . '" alt="作者头像" style="width: 100px; height: 100px; border-radius: 50%;">';
        echo '</div>';
    } else {
        // 显示默认 Gravatar
        echo '<div class="custom-author-avatar" style="text-align: center; margin-bottom: 20px;">';
        echo get_avatar($author_id, 100);
        echo '</div>';
    }
}