<?php

// ---------------------后台"维护模式"------------------------------------------
// 后台设置里、添加维护模式功能
function csrwiki_maintenance_mode()
{
    // 添加设置菜单 - 放到"设置"下
    add_action('admin_menu', function () {
        add_options_page(
            '维护模式设置',
            '维护模式',
            'manage_options',
            'maintenance-settings',
            'csrwiki_maintenance_settings_page'
        );
    });

    // 注册设置
    add_action('admin_init', function () {
        register_setting('maintenance-settings-group', 'maintenance_mode_active');
    });

    // 在wp加载早期检查维护模式
    add_action('wp', 'csrwiki_check_maintenance_mode');
}

/**
 * 检查维护模式状态并显示精美的维护页面
 */
function csrwiki_check_maintenance_mode()
{
    // 如果维护模式已启用且当前用户不是管理员
    if (get_option('maintenance_mode_active') && !current_user_can('manage_options')) {
        // 确保不是wp-admin页面
        if (!is_admin() && !wp_doing_ajax()) {

            // 设置503状态码
            status_header(503);
            // 设置Retry-After头，告诉搜索引擎多久后再来检查（例如3600秒/1小时）
            header('Retry-After: 3600');
            // 设置内容类型
            header('Content-Type: text/html; charset=utf-8');
            $site_name = get_bloginfo('name');

            $custom_logo_id = get_theme_mod('custom_logo');
            $logo_url = '';
            if ($custom_logo_id) {
                $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
                $logo_url = $logo_image[0];
            } else {
                // 如果没有设置自定义 Logo，使用默认图片
                $logo_url = get_stylesheet_directory_uri() . '/assets/images/default-logo.png';
            }

            $maintenance_html = '
            <!DOCTYPE html>
            <html lang="zh-CN">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>网站维护中 - ' . $site_name . '</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: "Segoe UI", "Microsoft YaHei", sans-serif;
                        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
                        color: #333;
                        height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        line-height: 1.6;
                    }
                    
                    .maintenance-container {
                        background: rgba(255, 255, 255, 0.95);
                        border-radius: 12px;
                        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                        padding: 50px;
                        width: 90%;
                        max-width: 600px;
                        text-align: center;
                        animation: fadeIn 1s ease-in-out;
                    }
                    
                    .logo {
                        width: 80px;
                        height: auto;
                        margin-bottom: 20px;
                    }
                    
                    h1 {
                        font-size: 32px;
                        margin-bottom: 20px;
                        color: #2c3e50;
                    }
                    
                    .message {
                        font-size: 18px;
                        margin-bottom: 30px;
                        color: #5d6d7e;
                    }
                    
                    .countdown {
                        font-size: 16px;
                        color: #7f8c8d;
                        margin-top: 10px;
                    }
                    
                    .icons {
                        margin-top: 40px;
                        display: flex;
                        justify-content: center;
                        gap: 20px;
                    }
                    
                    .icon {
                        font-size: 24px;
                        color: #3498db;
                        animation: pulse 2s infinite;
                    }
                    
                    .footer {
                        margin-top: 40px;
                        font-size: 14px;
                        color: #95a5a6;
                    }


                    .wechat-section {
    margin-top: 30px;
    text-align: center;
}

                    
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(-20px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                        100% { transform: scale(1); }
                    }
                    
                    @media (max-width: 768px) {
                        .maintenance-container {
                            padding: 40px 20px;
                        }
                        
                        h1 {
                            font-size: 26px;
                        }
                        
                        .message {
                            font-size: 16px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="maintenance-container">
                    <img src="' . $logo_url . '" alt="' . $site_name . '" class="logo">
                    <h1>网站维护中......<h1>
                    <div class="message">
                        
                       
                    </div>
                    <div class="icons">
                        <i class="fas fa-cog icon" style="animation-delay: 0s;"></i>
                        <i class="fas fa-tools icon" style="animation-delay: 0.3s;"></i>
                        <i class="fas fa-server icon" style="animation-delay: 0.6s;"></i>
                    </div>
                   <div class="wechat-section">
    
    

    
    <img src="' . get_stylesheet_directory_uri() . '/assets/images/wechatgroup.jpg" alt="微信公众号二维码" class="wechat-qr">
</div>
                    <div class="footer">
                        &copy; ' . date('Y') . ' ' . $site_name . ' - 企业社会责任与可持续发展专业知识平台
                    </div>
                </div>
            </body>
            </html>';

            // 输出维护页面并终止
            echo $maintenance_html;
            exit;
        }
    }
}
// 设置页面HTML
function csrwiki_maintenance_settings_page()
{
?>
    <div class="wrap">
        <h1>维护模式设置</h1>
        <form method="post" action="options.php">
            <?php settings_fields('maintenance-settings-group'); ?>
            <?php do_settings_sections('maintenance-settings-group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">维护模式状态</th>
                    <td>
                        <label>
                            <input type="checkbox" name="maintenance_mode_active" value="1" <?php checked(1, get_option('maintenance_mode_active'), true); ?> />
                            启用维护模式
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

// 初始化维护模式功能
csrwiki_maintenance_mode();

// ---------------------后台"维护模式"------------------------------------------