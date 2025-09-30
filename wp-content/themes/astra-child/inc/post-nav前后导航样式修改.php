<?php


function haowiki_skewed_post_navigation_improved() {
    ?>
    <style>
        /* 导航区域整体样式 */
        .navigation.post-navigation {
            margin: 3rem 0;
            padding: 1.5rem 0;
            overflow: visible; /* 允许子元素溢出 */
        }
        
        /* 导航链接容器 */
        .navigation.post-navigation .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 25px;
            perspective: 1000px; /* 添加透视效果 */
        }
        
        /* 上一篇/下一篇通用样式 - 斜向卡片 */
        .navigation.post-navigation .nav-previous,
        .navigation.post-navigation .nav-next {
            flex: 1;
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            background-color: #f7f7f7; /* 浅色背景 */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: skewX(-8deg); /* 卡片整体倾斜 */
            transition: all 0.3s ease;
            max-width: 48%;
        }
        
        /* 链接悬停效果 - 加深背景色 */
        .navigation.post-navigation .nav-previous:hover,
        .navigation.post-navigation .nav-next:hover {
            transform: skewX(-8deg) translateY(-5px);
            background-color: #efefef; /* 悬浮时背景色加深 */
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
        }
        
        /* 链接样式 - 内容反向倾斜，保持正常显示 */
        .navigation.post-navigation .nav-previous a,
        .navigation.post-navigation .nav-next a {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 20px;
            text-decoration: none;
            color: #333;
            transform: skewX(8deg); /* 内容反向倾斜，抵消容器的倾斜 */
            transition: all 0.3s ease;
        }
        
        /* 导航文字"Previous"和"Next"样式 */
        .navigation.post-navigation .ast-post-nav {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
        }
        
        /* 文章标题样式 */
        .navigation.post-navigation p {
            margin: 0;
            font-size: 1rem;
            line-height: 1.4;
            color: #222;
            font-weight: 500;
        }
        
        /* 箭头图标调整 */
        .navigation.post-navigation .ahfb-svg-iconset {
            display: inline-flex;
            align-items: center;
        }
        
        /* 上一篇箭头图标 */
        .navigation.post-navigation .nav-previous .ahfb-svg-iconset {
            margin-right: 8px;
            color: #4a89dc; /* 蓝色箭头 */
        }
        
        /* 下一篇箭头图标 */
        .navigation.post-navigation .nav-next .ahfb-svg-iconset {
            margin-left: 8px;
            color: #e67e22; /* 橙色箭头 */
        }
        
        /* SVG图标大小调整 */
        .navigation.post-navigation .ahfb-svg-iconset svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }
        
        /* 响应式调整 */
        @media (max-width: 768px) {
            .navigation.post-navigation .nav-links {
                flex-direction: column;
            }
            
            .navigation.post-navigation .nav-previous,
            .navigation.post-navigation .nav-next {
                width: 100%;
                max-width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // 为标题添加省略号效果
        $('.navigation.post-navigation p').each(function() {
            var titleText = $(this).text().trim();
            if(titleText.length > 45) {
                $(this).text(titleText.substring(0, 45) + '...');
            }
        });
        
        // 添加鼠标悬停动画效果
        $('.navigation.post-navigation .nav-previous, .navigation.post-navigation .nav-next').hover(
            function() {
                // 鼠标进入时略微增加倾斜度并加深背景色
                $(this).css({
                    'transform': 'skewX(-10deg) translateY(-5px)',
                    'background-color': '#e8e8e8', /* 更深的背景色 */
                    'transition': 'all 0.3s ease'
                });
                
                // 内容需要相应调整以保持直立
                $(this).find('a').css({
                    'transform': 'skewX(10deg)',
                    'transition': 'all 0.3s ease'
                });
                
                // 增强文字对比度
                $(this).find('p').css('color', '#000');
            },
            function() {
                // 鼠标离开时恢复原状
                $(this).css({
                    'transform': 'skewX(-8deg)',
                    'background-color': '#f7f7f7',
                    'transition': 'all 0.3s ease'
                });
                
                $(this).find('a').css({
                    'transform': 'skewX(8deg)',
                    'transition': 'all 0.3s ease'
                });
                
                // 恢复文字颜色
                $(this).find('p').css('color', '#222');
            }
        );
        
        // 添加点击效果
        $('.navigation.post-navigation .nav-previous a, .navigation.post-navigation .nav-next a')
            .mousedown(function() {
                $(this).parent().css({
                    'transform': 'skewX(-8deg) scale(0.98)',
                    'background-color': '#e0e0e0', /* 点击时背景色更深 */
                    'box-shadow': '0 2px 8px rgba(0, 0, 0, 0.1)'
                });
            })
            .mouseup(function() {
                $(this).parent().css({
                    'transform': 'skewX(-8deg)',
                    'background-color': '#f7f7f7',
                    'box-shadow': '0 4px 12px rgba(0, 0, 0, 0.08)'
                });
            });
    });
    </script>
    <?php
}

// 添加到WordPress钩子
add_action('wp_footer', 'haowiki_skewed_post_navigation_improved');