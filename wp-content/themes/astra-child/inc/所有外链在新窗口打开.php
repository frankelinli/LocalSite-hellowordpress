<?php
/**
 * 功能名称: 所有外链在新窗口打开
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}



/**
 * 让所有外部链接在新窗口打开
 */
function haowiki_external_links_new_window() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // 获取当前域名
        var currentDomain = window.location.hostname;
        
        // 选择所有链接
        $('a').each(function() {
            var href = $(this).attr('href');
            
            // 确保链接存在且不是空链接、锚点链接或javascript链接
            if (href && href !== '#' && !href.startsWith('javascript:') && !href.startsWith('tel:') && !href.startsWith('mailto:')) {
                try {
                    // 尝试创建URL对象来解析链接
                    var url = new URL(href, window.location.href);
                    
                    // 检查链接是否为外部链接（不包含当前域名）
                    if (url.hostname !== currentDomain && url.hostname !== '') {
                        // 为外部链接添加target和rel属性
                        $(this).attr('target', '_blank');
                        $(this).attr('rel', 'noopener noreferrer');
                    }
                } catch(e) {
                    // 如果URL解析失败（可能是相对路径），则忽略
                    console.log('URL解析失败:', href);
                }
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'haowiki_external_links_new_window');