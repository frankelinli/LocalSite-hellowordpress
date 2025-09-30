<?php
/**
 * 功能名称: AI自动生成特色图片
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


function auto_generate_ai_featured_image($post_id) {
    // 检查是否已有特色图片，避免重复设置
    if (has_post_thumbnail($post_id)) {
        return;
    }
    
    // 获取文章标题和摘要
    $post = get_post($post_id);
    $post_title = $post->post_title;
    $post_excerpt = has_excerpt($post_id) ? get_the_excerpt($post_id) : '';
    
    // 如果没有摘要，可以获取文章开头一部分内容
    if (empty($post_excerpt)) {
        $post_content = wp_strip_all_tags($post->post_content);
        $post_excerpt = substr($post_content, 0, 150);
    }
    
    // 构建完整的提示信息
    $prompt = "Create a colorful cartoon style illustration based on this article. Title: \"$post_title\"";
    if (!empty($post_excerpt)) {
        $prompt .= ". Summary: \"$post_excerpt\"";
    }
    
    // 调用AI API生成图片
    $image_url = generate_image_with_ai($prompt);
    
    // 如果成功获取图片URL，将其设置为特色图片
    if ($image_url) {
        set_featured_image_from_url($post_id, $image_url, $post_title);
        
        // 记录日志
        error_log("AI图片已生成并设置为特色图片: Post ID $post_id, 标题: $post_title");
        
        return true;
    }
    
    error_log("生成特色图片失败: Post ID $post_id, 标题: $post_title");
    return false;
}

// 使用AI API生成图片
function generate_image_with_ai($prompt) {
    // 使用OpenAI的DALL-E API
    $api_key = 'your_openai_api_key_here'; // 替换为您的API密钥
    
    // 添加一些风格指导
    $enhanced_prompt = $prompt . ". Make it vibrant, engaging, with clean design and cute cartoon style suitable for a blog featured image.";
    
    // API请求数据
    $data = array(
        'model' => 'dall-e-3', // 使用最新的DALL-E模型
        'prompt' => $enhanced_prompt,
        'n' => 1,
        'size' => '1024x1024', // 可以根据需要调整尺寸
        'quality' => 'standard', // 可选 'standard' 或 'hd'
        'response_format' => 'url'
    );
    
    // 设置API请求
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'body' => json_encode($data),
        'method' => 'POST',
        'timeout' => 60 // 给API足够的响应时间
    );
    
    // 发送请求
    $response = wp_remote_post('https://api.openai.com/v1/images/generations', $args);
    
    // 检查响应
    if (is_wp_error($response)) {
        error_log('AI API错误: ' . $response->get_error_message());
        return false;
    }
    
    // 解析响应
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    // 调试日志
    error_log('AI图片生成响应: ' . print_r($result, true));
    
    // 检查是否成功获取到图片URL
    if (isset($result['data'][0]['url'])) {
        return $result['data'][0]['url'];
    } elseif (isset($result['error'])) {
        error_log('AI API错误: ' . $result['error']['message']);
    }
    
    return false;
}

// 从URL设置特色图片
function set_featured_image_from_url($post_id, $image_url, $title) {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // 通过WordPress内置方法下载和导入图片
    $attach_id = media_sideload_image($image_url, $post_id, 'AI生成的特色图片 - ' . $title, 'id');
    
    if (is_wp_error($attach_id)) {
        error_log('下载特色图片错误: ' . $attach_id->get_error_message());
        return false;
    }
    
    // 设置为特色图片
    set_post_thumbnail($post_id, $attach_id);
    
    // 添加自定义元数据，标记这是AI生成的图片
    update_post_meta($attach_id, '_ai_generated', 'true');
    update_post_meta($attach_id, '_ai_prompt', 'Generated based on: ' . $title);
    
    return true;
}

// 在保存文章时触发自动生成特色图片
add_action('save_post', 'auto_ai_featured_image_handler', 20, 2);
function auto_ai_featured_image_handler($post_id, $post) {
    // 避免重复处理和其他检查
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_status != 'publish') return;
    if ($post->post_type != 'post') return; // 仅处理文章
    
    // 使用单独的方法跟踪已处理过的文章，避免重复生成
    if (get_post_meta($post_id, '_ai_image_processed', true)) {
        return;
    }
    
    // 生成特色图片
    $result = auto_generate_ai_featured_image($post_id);
    
    // 无论成功与否都标记为已处理，避免重复尝试
    update_post_meta($post_id, '_ai_image_processed', 'true');
    
    // 可选：如果想要允许将来重新生成，可以添加时间戳而不是布尔值
    // update_post_meta($post_id, '_ai_image_processed', time());
}

// 添加一个管理选项，允许重新生成特色图片
add_action('post_submitbox_misc_actions', 'add_regenerate_ai_image_button');
function add_regenerate_ai_image_button() {
    global $post;
    
    if (!$post || $post->post_type != 'post') {
        return;
    }
    
    // 添加重新生成按钮
    ?>
    <div class="misc-pub-section misc-pub-section-last">
        <button type="button" id="regenerate-ai-image" class="button">
            重新生成AI特色图片
        </button>
        <span class="spinner" id="ai-image-spinner" style="float: none; margin: 0 10px;"></span>
        <div id="ai-image-message"></div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#regenerate-ai-image').click(function() {
            var button = $(this);
            var spinner = $('#ai-image-spinner');
            var message = $('#ai-image-message');
            
            button.prop('disabled', true);
            spinner.addClass('is-active');
            message.html('');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'regenerate_ai_featured_image',
                    post_id: <?php echo $post->ID; ?>,
                    security: '<?php echo wp_create_nonce('regenerate_ai_image_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        message.html('<div style="color: green; margin-top: 10px;">图片已生成！请刷新页面查看。</div>');
                        // 刷新特色图片显示
                        if ($('#postimagediv').length) {
                            $('#postimagediv').find('.inside').html(response.data.html);
                        }
                    } else {
                        message.html('<div style="color: red; margin-top: 10px;">错误：' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    message.html('<div style="color: red; margin-top: 10px;">服务器错误，请稍后重试。</div>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
    });
    </script>
    <?php
}

// 处理AJAX请求，重新生成AI特色图片
add_action('wp_ajax_regenerate_ai_featured_image', 'handle_regenerate_ai_image');
function handle_regenerate_ai_image() {
    // 安全检查
    check_ajax_referer('regenerate_ai_image_nonce', 'security');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(['message' => '无效的文章ID']);
        return;
    }
    
    // 删除处理标记，允许重新生成
    delete_post_meta($post_id, '_ai_image_processed');
    
    // 如果已有特色图片，可以选择删除
    if (has_post_thumbnail($post_id)) {
        $attachment_id = get_post_thumbnail_id($post_id);
        // 删除特色图片关联（但不删除媒体库中的图片）
        delete_post_thumbnail($post_id);
        
        // 可选：如果要同时从媒体库删除图片，取消注释下面的代码
        // wp_delete_attachment($attachment_id, true);
    }
    
    // 生成新的特色图片
    $result = auto_generate_ai_featured_image($post_id);
    
    if ($result) {
        // 生成新的特色图片HTML
        $html = _wp_post_thumbnail_html(get_post_thumbnail_id($post_id), $post_id);
        wp_send_json_success(['message' => '特色图片已生成', 'html' => $html]);
    } else {
        wp_send_json_error(['message' => '生成特色图片失败，请查看错误日志']);
    }
}

// 可选：添加设置页面，配置API密钥和其他选项
add_action('admin_menu', 'add_ai_featured_image_settings');
function add_ai_featured_image_settings() {
    add_options_page(
        'AI特色图片设置', 
        'AI特色图片', 
        'manage_options', 
        'ai-featured-image-settings', 
        'ai_featured_image_settings_page'
    );
}

function ai_featured_image_settings_page() {
    // 处理表单提交
    if (isset($_POST['ai_settings_submit'])) {
        check_admin_referer('ai_settings_nonce');
        
        $api_key = sanitize_text_field($_POST['ai_api_key']);
        update_option('ai_featured_image_api_key', $api_key);
        
        $image_style = sanitize_text_field($_POST['ai_image_style']);
        update_option('ai_featured_image_style', $image_style);
        
        echo '<div class="notice notice-success"><p>设置已保存！</p></div>';
    }
    
    // 获取当前设置
    $api_key = get_option('ai_featured_image_api_key', '');
    $image_style = get_option('ai_featured_image_style', 'cartoon');
    
    // 显示设置表单
    ?>
    <div class="wrap">
        <h1>AI特色图片设置</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('ai_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ai_api_key">OpenAI API密钥</label></th>
                    <td>
                        <input type="text" name="ai_api_key" id="ai_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                        <p class="description">输入您的OpenAI API密钥，用于生成图片</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="ai_image_style">图片风格</label></th>
                    <td>
                        <select name="ai_image_style" id="ai_image_style">
                            <option value="cartoon" <?php selected($image_style, 'cartoon'); ?>>卡通风格</option>
                            <option value="watercolor" <?php selected($image_style, 'watercolor'); ?>>水彩画风格</option>
                            <option value="3d" <?php selected($image_style, '3d'); ?>>3D渲染风格</option>
                            <option value="minimal" <?php selected($image_style, 'minimal'); ?>>极简风格</option>
                            <option value="realistic" <?php selected($image_style, 'realistic'); ?>>写实风格</option>
                        </select>
                        <p class="description">选择生成图片的风格</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="ai_settings_submit" class="button button-primary" 
                       value="保存设置">
            </p>
        </form>
        
        <hr>
        
        <h2>测试图片生成</h2>
        <p>输入标题和摘要，测试图片生成效果：</p>
        
        <div id="test-ai-image-generator">
            <input type="text" id="test-title" placeholder="文章标题" class="regular-text"><br><br>
            <textarea id="test-excerpt" placeholder="文章摘要（可选）" rows="4" style="width: 100%; max-width: 500px;"></textarea><br><br>
            <button type="button" id="generate-test-image" class="button button-primary">生成测试图片</button>
            <span class="spinner" id="test-spinner" style="float: none; margin: 0 10px;"></span>
            
            <div id="test-result" style="margin-top: 20px;"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-test-image').click(function() {
                var button = $(this);
                var spinner = $('#test-spinner');
                var result = $('#test-result');
                
                var title = $('#test-title').val();
                if (!title) {
                    result.html('<div style="color: red;">请输入标题</div>');
                    return;
                }
                
                button.prop('disabled', true);
                spinner.addClass('is-active');
                result.html('<div>正在生成图片，这可能需要几秒钟...</div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'test_ai_image_generation',
                        title: title,
                        excerpt: $('#test-excerpt').val(),
                        security: '<?php echo wp_create_nonce('test_ai_image_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            result.html(
                                '<div style="color: green;">图片生成成功！</div>' +
                                '<div style="margin-top: 10px;"><img src="' + response.data.url + 
                                '" style="max-width: 100%; height: auto; border: 1px solid #ddd;"></div>'
                            );
                        } else {
                            result.html('<div style="color: red;">错误：' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        result.html('<div style="color: red;">服务器错误，请稍后重试。</div>');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        spinner.removeClass('is-active');
                    }
                });
            });
        });
        </script>
    </div>
    <?php
}

// 处理测试图片生成的AJAX请求
add_action('wp_ajax_test_ai_image_generation', 'handle_test_ai_image');
function handle_test_ai_image() {
    check_ajax_referer('test_ai_image_nonce', 'security');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足']);
        return;
    }
    
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $excerpt = isset($_POST['excerpt']) ? sanitize_textarea_field($_POST['excerpt']) : '';
    
    if (empty($title)) {
        wp_send_json_error(['message' => '标题不能为空']);
        return;
    }
    
    // 构建提示信息
    $prompt = "Create a colorful cartoon style illustration based on this article. Title: \"$title\"";
    if (!empty($excerpt)) {
        $prompt .= ". Summary: \"$excerpt\"";
    }
    
    // 获取用户设置的风格
    $style = get_option('ai_featured_image_style', 'cartoon');
    switch ($style) {
        case 'watercolor':
            $prompt .= ". Use watercolor painting style, soft colors, artistic look.";
            break;
        case '3d':
            $prompt .= ". Create in 3D rendered style, modern look with depth and dimension.";
            break;
        case 'minimal':
            $prompt .= ". Use minimal design, clean lines, limited color palette, modern aesthetic.";
            break;
        case 'realistic':
            $prompt .= ". Use realistic painting style with detailed textures and lighting.";
            break;
        default: // cartoon is default
            $prompt .= ". Make it vibrant, engaging, with clean design and cute cartoon style.";
    }
    
    // 调用API生成图片
    // 使用设置页面的API密钥
    $api_key = get_option('ai_featured_image_api_key', '');
    if (empty($api_key)) {
        wp_send_json_error(['message' => '未设置API密钥，请先在设置页面配置']);
        return;
    }
    
    // 复用之前的生成函数，但临时替换API密钥
    $temp_generate_image = function($prompt) use ($api_key) {
        // API请求数据
        $data = array(
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'standard',
            'response_format' => 'url'
        );
        
        // 设置API请求
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => json_encode($data),
            'method' => 'POST',
            'timeout' => 60
        );
        
        // 发送请求
        $response = wp_remote_post('https://api.openai.com/v1/images/generations', $args);
        
        // 检查响应
        if (is_wp_error($response)) {
            error_log('AI API错误: ' . $response->get_error_message());
            return false;
        }
        
        // 解析响应
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        // 检查是否成功获取到图片URL
        if (isset($result['data'][0]['url'])) {
            return $result['data'][0]['url'];
        } elseif (isset($result['error'])) {
            error_log('AI API错误: ' . print_r($result['error'], true));
            return ['error' => $result['error']['message']];
        }
        
        return false;
    };
    
    $result = $temp_generate_image($prompt);
    
    if (is_array($result) && isset($result['error'])) {
        wp_send_json_error(['message' => $result['error']]);
    } elseif ($result) {
        wp_send_json_success(['url' => $result]);
    } else {
        wp_send_json_error(['message' => '生成图片失败，请查看错误日志']);
    }
}