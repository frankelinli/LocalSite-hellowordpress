<?php
/**
 * 功能名称: 页眉线增加渐隐动画弹出
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}



//页眉下划线弹出动画渐隐
function add_header_underline_animation()
{
?>
    <style>
        .main-header-menu a {
            position: relative;
        }

        .main-header-menu a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: currentColor;
            /* 增加过渡时间，使用 ease-out 使动画更自然 */
            transition: width 0.3s ease-out;
            opacity: 1;
        }

        .main-header-menu a:hover::after {
            width: 100%;
        }
    </style>
<?php
}
add_action('wp_head', 'add_header_underline_animation');