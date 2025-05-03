<?php

/**
 * 在 WordPress 网站顶部添加公告栏。
 *
 * 此函数挂钩到 'astra_header_before' 动作，用于在网站顶部显示一个可自定义的公告栏。
 * 公告栏包括一条消息、一个关闭按钮以及带有条纹背景的样式。
 * 它还使用 JavaScript 和 jQuery 处理关闭按钮功能，并通过 localStorage 持久化关闭状态。
 *
 * 功能特点:
 * - 显示可自定义的公告消息。
 * - 包含关闭按钮以隐藏公告栏。
 * - 使用 localStorage 持久化关闭状态，防止用户再次看到。
 * - 使用重复线性渐变背景进行样式化。
 *
 * 样式:
 * - 公告栏使用 CSS 变量定义条纹颜色。
 * - 使用 Flexbox 居中内容并定位关闭按钮。
 *
 * JavaScript 行为:
 * - 检查 localStorage 以确定是否显示公告栏。
 * - 当点击关闭按钮时隐藏公告栏，并将关闭状态存储到 localStorage。
 *
 * 使用方法:
 * - 此函数自动挂钩到 'astra_header_before' 动作。
 * - 要自定义消息或样式，请修改函数中的 HTML、CSS 或 JavaScript。
 *
 * 文件路径:
 * - /wp-content/themes/astra-child/inc/网页顶部announcement-bar.php
 *
 * @package AstraChildTheme
 */


function add_top_announcement_bar() {
    ?>
    <div class="announcement-bar">
        <span class="announcement-content">
            🦖 Docusaurus v3.6 is out! 🥳
        </span>
        <button class="announcement-close">×</button>
    </div>
    <style>
        :root {
            --site-announcement-bar-stripe-color1: #e8d7ff;
            --site-announcement-bar-stripe-color2: #ffe9d1;
        }
        
        .announcement-bar {
            background: repeating-linear-gradient(
                35deg,
                var(--site-announcement-bar-stripe-color1),
                var(--site-announcement-bar-stripe-color1) 20px,
                var(--site-announcement-bar-stripe-color2) 10px,
                var(--site-announcement-bar-stripe-color2) 40px
            );
            text-align: center;
            padding: 8px 15px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .announcement-content {
            font-size: 20px;
            font-weight: 700;
        }
        
        .announcement-close {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 0 5px;
            font-weight: bold;
        }
        
        .announcement-bar.hidden {
            display: none;
        }
    </style>
    <script>
    jQuery(document).ready(function($) {
        if (!localStorage.getItem('announcement_closed')) {
            $('.announcement-bar').show();
        } else {
            $('.announcement-bar').hide();
        }
        
        $('.announcement-close').click(function() {
            $('.announcement-bar').slideUp();
            localStorage.setItem('announcement_closed', 'true');
        });
    });
    </script>
    <?php
}

add_action('astra_header_before', 'add_top_announcement_bar');
