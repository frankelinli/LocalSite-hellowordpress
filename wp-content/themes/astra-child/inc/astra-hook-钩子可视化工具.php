<?php
/**
 * 功能名称: astra hook 钩子可视化工具
 * 使用方法：在任意网页后加上  ?show_hooks=1
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}



function haowiki_astra_hooks_visualizer() {
    // 只在管理员登录时显示
    if (!current_user_can('administrator')) {
        return;
    }

    // 检查是否启用了可视化器
    if (!isset($_GET['show_hooks']) || $_GET['show_hooks'] !== '1') {
        return;
    }

    // 添加样式
    add_action('wp_head', 'haowiki_add_hooks_visualizer_styles');
    
    // 添加所有钩子
    add_action('astra_html_before', function() { haowiki_display_hook('astra_html_before', '#ff6b6b'); }, 1);
    add_action('astra_head_top', function() { haowiki_display_hook('astra_head_top', '#ff9e7a'); }, 1);
    add_action('astra_head_bottom', function() { haowiki_display_hook('astra_head_bottom', '#ffd57a'); }, 1);
    add_action('astra_body_top', function() { haowiki_display_hook('astra_body_top', '#a1de93'); }, 1);
    add_action('astra_header_before', function() { haowiki_display_hook('astra_header_before', '#70a1d7'); }, 1);
    add_action('astra_masthead_top', function() { haowiki_display_hook('astra_masthead_top', '#a3d6d4'); }, 1);
    add_action('astra_main_header_bar_top', function() { haowiki_display_hook('astra_main_header_bar_top', '#c2aff0'); }, 1);
    add_action('astra_masthead_toggle_buttons_before', function() { haowiki_display_hook('astra_masthead_toggle_buttons_before', '#ff6b6b'); }, 1);
    add_action('astra_masthead_toggle_buttons_after', function() { haowiki_display_hook('astra_masthead_toggle_buttons_after', '#ff9e7a'); }, 1);
    add_action('astra_masthead_content', function() { haowiki_display_hook('astra_masthead_content', '#ffd57a'); }, 1);
    add_action('astra_main_header_bar_bottom', function() { haowiki_display_hook('astra_main_header_bar_bottom', '#a1de93'); }, 1);
    add_action('astra_masthead_bottom', function() { haowiki_display_hook('astra_masthead_bottom', '#70a1d7'); }, 1);
    add_action('astra_header_after', function() { haowiki_display_hook('astra_header_after', '#a3d6d4'); }, 1);
    add_action('astra_content_before', function() { haowiki_display_hook('astra_content_before', '#c2aff0'); }, 1);
    add_action('astra_content_top', function() { haowiki_display_hook('astra_content_top', '#ff6b6b'); }, 1);
    add_action('astra_primary_content_top', function() { haowiki_display_hook('astra_primary_content_top', '#ff9e7a'); }, 1);
    add_action('astra_entry_before', function() { haowiki_display_hook('astra_entry_before', '#ffd57a'); }, 1);
    add_action('astra_entry_top', function() { haowiki_display_hook('astra_entry_top', '#a1de93'); }, 1);
    add_action('astra_entry_content_before', function() { haowiki_display_hook('astra_entry_content_before', '#70a1d7'); }, 1);
    add_action('astra_entry_content_after', function() { haowiki_display_hook('astra_entry_content_after', '#a3d6d4'); }, 1);
    add_action('astra_entry_bottom', function() { haowiki_display_hook('astra_entry_bottom', '#c2aff0'); }, 1);
    add_action('astra_entry_after', function() { haowiki_display_hook('astra_entry_after', '#ff6b6b'); }, 1);
    add_action('astra_primary_content_bottom', function() { haowiki_display_hook('astra_primary_content_bottom', '#ff9e7a'); }, 1);
    add_action('astra_sidebars_before', function() { haowiki_display_hook('astra_sidebars_before', '#ffd57a'); }, 1);
    add_action('astra_sidebars_after', function() { haowiki_display_hook('astra_sidebars_after', '#a1de93'); }, 1);
    add_action('astra_content_bottom', function() { haowiki_display_hook('astra_content_bottom', '#70a1d7'); }, 1);
    add_action('astra_content_after', function() { haowiki_display_hook('astra_content_after', '#a3d6d4'); }, 1);
    add_action('astra_footer_before', function() { haowiki_display_hook('astra_footer_before', '#c2aff0'); }, 1);
    add_action('astra_footer_content_top', function() { haowiki_display_hook('astra_footer_content_top', '#ff6b6b'); }, 1);
    add_action('astra_footer_content_bottom', function() { haowiki_display_hook('astra_footer_content_bottom', '#ff9e7a'); }, 1);
    add_action('astra_footer_after', function() { haowiki_display_hook('astra_footer_after', '#ffd57a'); }, 1);
    add_action('astra_body_bottom', function() { haowiki_display_hook('astra_body_bottom', '#a1de93'); }, 1);
    
    // 添加控制面板
    add_action('wp_footer', 'haowiki_add_hooks_control_panel', 999);
}
add_action('wp_loaded', 'haowiki_astra_hooks_visualizer');

// 显示钩子函数
function haowiki_display_hook($hook_name, $color) {
    echo '<div class="haowiki-hook-marker" style="background-color:' . esc_attr($color) . ';" data-hook="' . esc_attr($hook_name) . '">';
    echo '<span class="hook-name">' . esc_html($hook_name) . '</span>';
    echo '<span class="hook-diamond"></span>';
    echo '</div>';
}

// 添加样式
function haowiki_add_hooks_visualizer_styles() {
    ?>
    <style>
        .haowiki-hook-marker {
            position: relative;
            padding: 6px 8px;
            margin: 2px 0;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            color: #fff;
            display: block;
            text-align: left;
            z-index: 999999;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .haowiki-hook-marker:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 6px rgba(0,0,0,0.4);
        }
        
        .haowiki-hook-marker.copied {
            animation: pulse 0.5s;
            box-shadow: 0 0 10px 5px rgba(255, 255, 255, 0.5);
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { box-shadow: 0 0 10px 5px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
        
        .haowiki-hook-marker .hook-name {
            font-weight: bold;
            text-shadow: 0 1px 1px rgba(0,0,0,0.4);
            padding-left: 20px;
            position: relative;
        }
        
        .haowiki-hook-marker .hook-diamond {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
            width: 8px;
            height: 8px;
            background-color: #fff;
        }
        
        #haowiki-hooks-control-panel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 9999999;
            padding: 15px;
            width: 320px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        #haowiki-hooks-control-panel h3 {
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            font-size: 16px;
        }
        
        #haowiki-hooks-control-panel .controls {
            margin-bottom: 15px;
        }
        
        #haowiki-hooks-control-panel button {
            background: #0073aa;
            border: none;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        #haowiki-hooks-control-panel button:hover {
            background: #005177;
        }
        
        #haowiki-hooks-control-panel .legend {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            font-size: 11px;
        }
        
        #haowiki-hooks-control-panel .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        #haowiki-hooks-control-panel .color-box {
            width: 12px;
            height: 12px;
            margin-right: 5px;
            border-radius: 2px;
        }
        
        #haowiki-hooks-control-panel .hook-filter {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .haowiki-hidden-hook {
            display: none !important;
        }
        
        #copy-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            z-index: 9999999;
            display: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 14px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }
        
        .copy-code-text {
            display: block;
            font-size: 10px;
            margin-top: 3px;
            color: rgba(255,255,255,0.8);
        }
        
        .hook-code {
            padding: 2px 4px;
            background: rgba(0,0,0,0.1);
            border-radius: 3px;
            margin-top: 3px;
            font-size: 10px;
            display: none;
        }
        
        .haowiki-hook-marker:hover .hook-code {
            display: block;
        }
        
        #code-copied-preview {
            position: fixed;
            top: 70px;
            right: 20px;
            background-color: #f5f5f5;
            border-left: 4px solid #4CAF50;
            color: #333;
            padding: 15px;
            border-radius: 4px;
            z-index: 9999998;
            display: none;
            font-family: monospace;
            font-size: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            max-height: 200px;
            overflow: auto;
            white-space: pre;
        }
    </style>
    <div id="copy-notification">代码已复制到剪贴板!</div>
    <div id="code-copied-preview"></div>
    <?php
}

// 添加控制面板
function haowiki_add_hooks_control_panel() {
    ?>
    <div id="haowiki-hooks-control-panel">
        <h3>Astra Hooks 可视化工具</h3>
        
        <div class="controls">
            <input type="text" class="hook-filter" placeholder="搜索钩子..." id="hook-filter">
            <button id="toggle-all-hooks">显示/隐藏所有钩子</button>
            <button id="copy-all-hooks">复制所有钩子代码</button>
            <button id="reset-hooks">重置</button>
            <a href="<?php echo esc_url(remove_query_arg('show_hooks')); ?>" style="display:inline-block; margin-top:5px; text-decoration:none; color:#0073aa; font-size:12px;">退出可视化</a>
        </div>
        
        <div class="legend">
            <div class="legend-item">
                <div class="color-box" style="background-color:#ff6b6b;"></div>
                <span>html/head/entry</span>
            </div>
            <div class="legend-item">
                <div class="color-box" style="background-color:#ff9e7a;"></div>
                <span>head/content</span>
            </div>
            <div class="legend-item">
                <div class="color-box" style="background-color:#ffd57a;"></div>
                <span>body/entry</span>
            </div>
            <div class="legend-item">
                <div class="color-box" style="background-color:#a1de93;"></div>
                <span>header/content</span>
            </div>
            <div class="legend-item">
                <div class="color-box" style="background-color:#70a1d7;"></div>
                <span>content/footer</span>
            </div>
            <div class="legend-item">
                <div class="color-box" style="background-color:#a3d6d4;"></div>
                <span>masthead/sidebar</span>
            </div>
            <div class="legend-item">
                <div class="color-box" style="background-color:#c2aff0;"></div>
                <span>footer/misc</span>
            </div>
        </div>
        
        <div style="margin-top: 10px; font-size: 11px; color: #666;">
            提示: 点击任何钩子以复制完整的代码示例
            <div style="margin-top: 5px; background: #f5f5f5; padding: 8px; border-radius: 3px; font-family: monospace;">
                <strong>粘贴到functions.php中即可看到效果</strong>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // 生成完整的代码示例
        function getFullCodeExample(hookName) {
            const functionName = hookName.replace('astra_', 'haowiki_astra_') + '_example';
            
            return `/**
 * 在${hookName}位置添加自定义内容
 */
function ${functionName}() {
    echo '<div style="background-color: #f0f0f0; padding: 10px; margin: 5px 0; border-left: 4px solid #0073aa;">
        <p>此处使用${hookName}钩子添加的内容</p>
        <p>编辑functions.php中的<strong>${functionName}</strong>函数来修改此内容</p>
    </div>';
}
add_action('${hookName}', '${functionName}');`;
        }
        
        // 复制到剪贴板函数
        function copyToClipboard(text) {
            // 创建临时输入框
            const input = document.createElement('textarea');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            
            // 显示通知
            $('#copy-notification').fadeIn().delay(1500).fadeOut();
            
            // 显示复制的代码预览
            $('#code-copied-preview').text(text).fadeIn().delay(3000).fadeOut();
        }
        
        // 添加使用示例代码到钩子
        $('.haowiki-hook-marker').each(function() {
            const hookName = $(this).data('hook');
            const fullCode = getFullCodeExample(hookName);
            
            $(this).append('<span class="copy-code-text">点击复制完整代码</span>');
            $(this).append('<div class="hook-code">' + fullCode.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>');
            $(this).data('code', fullCode);
        });
        
        // 切换所有钩子
        $('#toggle-all-hooks').on('click', function() {
            $('.haowiki-hook-marker').toggleClass('haowiki-hidden-hook');
        });
        
        // 复制所有钩子代码
        $('#copy-all-hooks').on('click', function() {
            let allCodes = [];
            $('.haowiki-hook-marker:not(.haowiki-hidden-hook)').each(function() {
                allCodes.push($(this).data('code'));
            });
            
            copyToClipboard(allCodes.join("\n\n"));
        });
        
        // 重置
        $('#reset-hooks').on('click', function() {
            $('.haowiki-hook-marker').removeClass('haowiki-hidden-hook');
            $('#hook-filter').val('');
        });
        
        // 搜索过滤
        $('#hook-filter').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            
            $('.haowiki-hook-marker').each(function() {
                var hookName = $(this).data('hook').toLowerCase();
                if (hookName.indexOf(value) > -1) {
                    $(this).removeClass('haowiki-hidden-hook');
                } else {
                    $(this).addClass('haowiki-hidden-hook');
                }
            });
        });
        
        // 点击钩子复制名称
        $(document).on('click', '.haowiki-hook-marker', function(e) {
            e.preventDefault();
            const code = $(this).data('code');
            copyToClipboard(code);
            
            // 添加动画效果
            $(this).addClass('copied');
            setTimeout(() => {
                $(this).removeClass('copied');
            }, 500);
        });
    });
    </script>
    <?php
}
