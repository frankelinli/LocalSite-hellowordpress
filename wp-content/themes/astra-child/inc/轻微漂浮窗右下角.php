<?php
/**
 * 功能名称: 轻微漂浮窗右下角
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


function add_floating_tech_window() {
    ?>
    <style>
        /* 浮动窗口的样式 */
        .floating-window {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 300px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            z-index: 9999;
            transition: all 0.3s ease;
            transform: translateY(0);
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* 标题样式 */
        .floating-window-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .floating-window-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        /* 关闭按钮 */
        .close-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        /* 内容样式 */
        .floating-window-content {
            font-size: 14px;
            line-height: 1.5;
            color: #555;
        }
        
        .floating-window-content h3 {
            font-size: 15px;
            margin: 12px 0 8px;
            color: #444;
        }
        
        .floating-window-content ul {
            padding-left: 20px;
            margin: 8px 0;
        }
        
        .floating-window-content li {
            margin-bottom: 5px;
        }
        
        /* 最小化后的样式 */
        .floating-window.minimized {
            height: 40px;
            overflow: hidden;
            padding: 10px 20px;
        }
        
        .floating-window.minimized .floating-window-header {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .floating-window.minimized .floating-window-content {
            display: none;
        }
        
        /* 最小化/最大化按钮 */
        .minimize-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 18px;
            margin-right: 5px;
            transition: color 0.3s;
        }
        
        .minimize-btn:hover {
            color: #333;
        }
        
        /* 浮动效果 */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        .floating-window {
            animation: float 5s ease-in-out infinite;
        }
        
        .floating-window.minimized {
            animation: none;
        }
    </style>
    
    <div id="floating-tech-window" class="floating-window">
        <div class="floating-window-header">
            <h4 class="floating-window-title">技术实现</h4>
            <div>
                <button id="minimize-btn" class="minimize-btn" aria-label="最小化">-</button>
                <button id="close-btn" class="close-btn" aria-label="关闭">×</button>
            </div>
        </div>
        <div class="floating-window-content">
            <h3>前端技术</h3>
            <ul>
                <li>HTML5 + CSS3 响应式设计</li>
                <li>JavaScript & jQuery</li>
                <li>现代化动画效果</li>
            </ul>
            
            <h3>后端技术</h3>
            <ul>
                <li>WordPress 核心功能</li>
                <li>PHP 自定义功能</li>
                <li>数据库优化</li>
            </ul>
            
            <h3>性能优化</h3>
            <ul>
                <li>资源压缩与缓存</li>
                <li>懒加载技术</li>
                <li>CDN 加速</li>
            </ul>
        </div>
    </div>
    
    <script>
        jQuery(document).ready(function($) {
            // 检查cookie决定是否显示窗口
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }
            
            // 如果已经关闭过且在24小时内，则不显示
            const closedTime = getCookie('tech_window_closed');
            if (closedTime && (Date.now() - parseInt(closedTime)) < 24 * 60 * 60 * 1000) {
                $('#floating-tech-window').hide();
            }
            
            // 关闭按钮功能
            $('#close-btn').on('click', function() {
                $('#floating-tech-window').fadeOut(300);
                // 设置cookie记录关闭时间
                document.cookie = `tech_window_closed=${Date.now()};path=/;max-age=${60*60*24}`;
            });
            
            // 最小化/最大化功能
            let minimized = false;
            $('#minimize-btn').on('click', function() {
                if (minimized) {
                    $('#floating-tech-window').removeClass('minimized');
                    $(this).text('-');
                    minimized = false;
                } else {
                    $('#floating-tech-window').addClass('minimized');
                    $(this).text('+');
                    minimized = true;
                }
            });
            
            // 让浮动窗口可拖动
            let isDragging = false;
            let offsetX, offsetY;
            
            $('.floating-window-header').on('mousedown', function(e) {
                if ($(e.target).hasClass('close-btn') || $(e.target).hasClass('minimize-btn')) {
                    return;
                }
                
                isDragging = true;
                const floatingWindow = $('#floating-tech-window');
                
                // 暂停浮动动画
                floatingWindow.css('animation', 'none');
                
                // 计算鼠标指针与窗口的相对位置
                const windowRect = floatingWindow[0].getBoundingClientRect();
                offsetX = e.clientX - windowRect.left;
                offsetY = e.clientY - windowRect.top;
                
                // 防止文本选择
                $('body').css('user-select', 'none');
            });
            
            $(document).on('mousemove', function(e) {
                if (!isDragging) return;
                
                const floatingWindow = $('#floating-tech-window');
                
                // 设置新位置
                const left = e.clientX - offsetX;
                const top = e.clientY - offsetY;
                
                floatingWindow.css({
                    right: 'auto',
                    bottom: 'auto',
                    left: left + 'px',
                    top: top + 'px'
                });
            });
            
            $(document).on('mouseup', function() {
                isDragging = false;
                $('body').css('user-select', '');
            });
        });
    </script>
    <?php
}

// 在适当的钩子中添加该函数，例如wp_footer
add_action('wp_footer', 'add_floating_tech_window');