<?php
/**
 * 功能名称: 在post meta里插入打印和分享二维码
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}



function modify_entry_meta($output)
{
    if (is_single()) {
        $print_button = '<span class="posted-on print-button"><a href="javascript:window.print();">打印</a></span>';
        $qr_share = '<span class="posted-on qr-share"><a href="#" class="qr-trigger">分享</a><div class="qr-popup"><div id="qrcode"></div></div></span>';

        // 查找 entry-meta 结束标签的位置
        $pos = strrpos($output, '</div>');

        if ($pos !== false) {
            // 在 </div> 之前插入打印按钮和二维码分享
            $output = substr_replace($output, $print_button . $qr_share, $pos, 0);
        }

        // 在页脚添加 qrcode.js 库
        wp_enqueue_script('qrcode-js', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array('jquery'), null, true);

        // 添加自定义 JavaScript
        wp_add_inline_script('qrcode-js', '
            jQuery(document).ready(function($) {
                var qrcode = new QRCode(document.getElementById("qrcode"), {
                    text: window.location.href,
                    width: 100,
                    height: 100,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
                
                $(".qr-trigger").hover(
                    function() {
                        $(this).next(".qr-popup").stop().fadeIn(200);
                    },
                    function() {
                        $(this).next(".qr-popup").stop().fadeOut(200);
                    }
                );
            });
        ');
    }
    return $output;
}
add_filter('astra_single_post_meta', 'modify_entry_meta');