<?php
/**
 * 功能名称: 文章底部添加访客系统信息
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


/**
 * 使用 Astra 钩子添加访客浏览器信息 - 最小实现
 */
function haowiki_visitor_browser_info() {
    // 仅在单篇文章页面显示
    if (!is_single()) {
        return;
    }
    
    // 内联样式
    echo '<style>
    .hwk-visitor-info {
        margin-top: 2rem;
        padding: 1rem;
        background: #f7f7f7;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    .hwk-visitor-info h4 {
        margin-top: 0;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }
    .hwk-info-item {
        margin-bottom: 0.5rem;
    }
    .hwk-info-label {
        font-weight: bold;
        margin-right: 0.5rem;
    }
    </style>';
    
    // HTML结构
    echo '<div class="hwk-visitor-info">
        <h4>您的浏览信息</h4>
        <div class="hwk-info-item">
            <span class="hwk-info-label">浏览器:</span>
            <span id="hwk-browser">检测中...</span>
        </div>
        <div class="hwk-info-item">
            <span class="hwk-info-label">操作系统:</span>
            <span id="hwk-os">检测中...</span>
        </div>
        <div class="hwk-info-item">
            <span class="hwk-info-label">屏幕分辨率:</span>
            <span id="hwk-screen">检测中...</span>
        </div>
        <div class="hwk-info-item">
            <span class="hwk-info-label">设备类型:</span>
            <span id="hwk-device">检测中...</span>
        </div>

        <p> 由astra_entry_bottom钩子创建</P>
    </div>';
    
    // 内联JavaScript
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 获取浏览器信息
        var ua = navigator.userAgent;
        var browserInfo = "未知浏览器";
        
        if (ua.indexOf("Firefox") > -1) {
            browserInfo = "Firefox";
        } else if (ua.indexOf("Edge") > -1 || ua.indexOf("Edg") > -1) {
            browserInfo = "Edge";
        } else if (ua.indexOf("Chrome") > -1 && ua.indexOf("Safari") > -1) {
            browserInfo = "Chrome";
        } else if (ua.indexOf("Safari") > -1 && ua.indexOf("Chrome") === -1) {
            browserInfo = "Safari";
        } else if (ua.indexOf("MSIE") > -1 || ua.indexOf("Trident") > -1) {
            browserInfo = "IE";
        } else if (ua.indexOf("Opera") > -1 || ua.indexOf("OPR") > -1) {
            browserInfo = "Opera";
        }
        
        document.getElementById("hwk-browser").textContent = browserInfo;
        
        // 获取操作系统信息
        var osInfo = "未知系统";
        if (ua.indexOf("Win") > -1) osInfo = "Windows";
        else if (ua.indexOf("Mac") > -1) osInfo = "macOS";
        else if (ua.indexOf("iPhone") > -1) osInfo = "iOS";
        else if (ua.indexOf("iPad") > -1) osInfo = "iPadOS";
        else if (ua.indexOf("Android") > -1) osInfo = "Android";
        else if (ua.indexOf("Linux") > -1) osInfo = "Linux";
        
        document.getElementById("hwk-os").textContent = osInfo;
        
        // 获取屏幕信息
        document.getElementById("hwk-screen").textContent = window.screen.width + " x " + window.screen.height;
        
        // 获取设备类型
        var deviceType = "桌面设备";
        if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
            deviceType = "平板设备";
        } else if (/Mobile|Android|iPhone|iPod|IEMobile|BlackBerry|Opera Mini/i.test(ua)) {
            deviceType = "移动设备";
        }
        
        document.getElementById("hwk-device").textContent = deviceType;
    });
    </script>';
}

// 使用 Astra 钩子在文章底部添加访客信息
add_action('astra_entry_bottom', 'haowiki_visitor_browser_info');