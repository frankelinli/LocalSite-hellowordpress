<?php
/**
 * 功能名称: 自动根据标题生成特色图片
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 为已发表且没有特色图片的文章生成特色图片
 */
function haowiki_generate_featured_images_for_existing_posts() {
    // 创建单次使用的管理页面
    add_management_page(
        '生成特色图片',           // 页面标题
        '生成特色图片',           // 菜单标题
        'manage_options',         // 所需权限
        'generate-featured-images', // 菜单slug
        'haowiki_generate_featured_images_page' // 回调函数
    );
}
add_action('admin_menu', 'haowiki_generate_featured_images_for_existing_posts');

/**
 * 生成特色图片页面的回调函数
 */
function haowiki_generate_featured_images_page() {
    // 检查GD库是否可用
    if (!function_exists('imagecreatetruecolor')) {
        echo '<div class="notice notice-error"><p>错误：PHP GD库未启用，无法生成图片。请联系您的主机提供商启用GD库。</p></div>';
        return;
    }
    
    // 处理表单提交
    if (isset($_POST['generate_images']) && check_admin_referer('generate_featured_images_nonce')) {
        // 获取所有没有特色图片的文章
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS'
                ),
            )
        );
        
        $posts_without_thumbnails = get_posts($args);
        $count = 0;
        $errors = array();
        
        foreach ($posts_without_thumbnails as $post) {
            $result = haowiki_generate_featured_image_for_post($post->ID);
            if ($result === true) {
                $count++;
            } elseif (is_string($result)) {
                $errors[] = '文章 "' . get_the_title($post->ID) . '" 处理失败: ' . $result;
            }
        }
        
        echo '<div class="notice notice-success"><p>成功为 ' . $count . ' 篇文章生成了特色图片。</p></div>';
        
        if (!empty($errors)) {
            echo '<div class="notice notice-error"><p>处理过程中遇到以下错误：</p><ul>';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';
        }
    }
    
    // 显示页面内容
    ?>
    <div class="wrap">
        <h1>为已发表文章生成特色图片</h1>
        <p>点击下面的按钮，将为所有没有特色图片的已发表文章生成特色图片。</p>
        <form method="post" action="">
            <?php wp_nonce_field('generate_featured_images_nonce'); ?>
            <p class="submit">
                <input type="submit" name="generate_images" class="button button-primary" value="开始生成特色图片">
            </p>
        </form>
    </div>
    <?php
}

/**
 * 为指定文章生成特色图片
 * @return true on success, error message string on failure
 */
function haowiki_generate_featured_image_for_post($post_id) {
    // 检查文章是否已有特色图片
    if (has_post_thumbnail($post_id)) {
        return "文章已有特色图片";
    }
    
    // 获取文章标题
    $title = get_the_title($post_id);
    
    // 如果标题为空，使用默认文本
    if (empty($title)) {
        $title = 'HaoWiki Article #' . $post_id;
    }
    
    // 创建图片
    $image_width = 800;
    $image_height = 600;
    
    // 创建空白图片
    $image = imagecreatetruecolor($image_width, $image_height);
    if (!$image) {
        return "无法创建图像资源";
    }
    
    // 设置背景颜色（渐变背景，从深蓝到浅蓝）
    $dark_blue = imagecolorallocate($image, 41, 128, 185);
    $light_blue = imagecolorallocate($image, 52, 152, 219);
    
    // 填充背景（使用纯色，确保背景不是透明的）
    imagefill($image, 0, 0, $dark_blue);
    
    // 绘制渐变效果（简单实现）
    for ($i = 0; $i < $image_height; $i++) {
        $ratio = $i / $image_height;
        $r = 41 + (52 - 41) * $ratio;
        $g = 128 + (152 - 128) * $ratio;
        $b = 185 + (219 - 185) * $ratio;
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $i, $image_width, $i, $color);
    }
    
    // 设置文本颜色（白色文本）
    $text_color = imagecolorallocate($image, 255, 255, 255);
    if ($text_color === false) {
        imagedestroy($image);
        return "无法分配文本颜色";
    }
    
    // 添加边框（可选）
    imagerectangle($image, 0, 0, $image_width - 1, $image_height - 1, $text_color);
    
    // 首先尝试使用默认字体绘制文本
    // 截取标题，避免太长
    $max_length = 50;
    if (strlen($title) > $max_length) {
        $title = substr($title, 0, $max_length) . '...';
    }
    
    // 使用默认字体
    $font_size = 5; // 最大默认字体
    // 获取文本尺寸
    $text_width = imagefontwidth($font_size) * strlen($title);
    $text_height = imagefontheight($font_size);
    
    // 计算居中位置
    $x = ($image_width - $text_width) / 2;
    $y = ($image_height - $text_height) / 2;
    
    // 在图像上绘制文本
    imagestring($image, $font_size, $x, $y, $title, $text_color);
    
    // 添加网站名称做为水印
    $site_name = get_bloginfo('name');
    $site_name_width = imagefontwidth(3) * strlen($site_name);
    $site_name_x = ($image_width - $site_name_width) / 2;
    $site_name_y = $y + $text_height + 30; // 在标题下方
    
    imagestring($image, 3, $site_name_x, $site_name_y, $site_name, $text_color);
    
    // 临时图片文件路径
    $upload_dir = wp_upload_dir();
    $filename = 'featured-' . sanitize_title($title) . '-' . time() . '.jpg';
    $file_path = $upload_dir['path'] . '/' . $filename;
    
    // 确保目录存在且可写
    if (!wp_mkdir_p($upload_dir['path'])) {
        imagedestroy($image);
        return "无法创建上传目录: " . $upload_dir['path'];
    }
    
    // 保存图片到文件
    $result = imagejpeg($image, $file_path, 90);
    imagedestroy($image);
    
    if (!$result) {
        return "无法保存图像到文件: " . $file_path;
    }
    
    // 检查文件是否真的创建了
    if (!file_exists($file_path)) {
        return "文件未能创建: " . $file_path;
    }
    
    // 获取文件信息
    $filetype = wp_check_filetype($filename, null);
    
    // 准备图片附件数据
    $attachment = array(
        'post_mime_type' => $filetype['type'],
        'post_title'     => $title . ' - 自动生成的特色图片',
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    
    // 将图片插入媒体库
    $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
    
    if (is_wp_error($attach_id)) {
        return "插入附件失败: " . $attach_id->get_error_message();
    }
    
    // 确保包含image.php文件
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // 生成附件的元数据
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    wp_update_attachment_metadata($attach_id, $attach_data);
    
    // 将图片设置为文章的特色图片
    $result = set_post_thumbnail($post_id, $attach_id);
    
    if (!$result) {
        return "设置特色图片失败";
    }
    
    return true;
}

/**
 * 为没有特色图片的新文章动态生成基于标题的图片
 */
function haowiki_auto_generate_featured_image() {
    // 仅在单篇文章/页面时运行
    if (!is_singular()) {
        return;
    }
    
    global $post;
    
    // 调用生成特色图片函数
    haowiki_generate_featured_image_for_post($post->ID);
}

// 在文章内容准备好后执行此函数，用于新发布的文章
add_action('wp', 'haowiki_auto_generate_featured_image');

/**
 * 添加调试功能：在后台文章编辑页面添加一个按钮，用于手动生成特色图片
 */
function haowiki_add_generate_thumbnail_button() {
    global $post;
    
    // 仅在文章编辑页面显示
    if (get_post_type($post) !== 'post') {
        return;
    }
    
    // 如果已有特色图片，则不显示按钮
    if (has_post_thumbnail($post->ID)) {
        return;
    }
    
    echo '<div id="generate-thumbnail-box" class="postbox">';
    echo '<h2 class="hndle">生成特色图片</h2>';
    echo '<div class="inside">';
    echo '<p>点击下面的按钮为此文章生成特色图片：</p>';
    echo '<a href="' . wp_nonce_url(admin_url('admin.php?action=haowiki_generate_thumbnail&post_id=' . $post->ID), 'haowiki_generate_thumbnail') . '" class="button button-primary">生成特色图片</a>';
    echo '</div>';
    echo '</div>';
}
add_action('add_meta_boxes', function() {
    add_meta_box('haowiki-generate-thumbnail', '生成特色图片', 'haowiki_add_generate_thumbnail_button', 'post', 'side');
});

/**
 * 处理生成特色图片的AJAX请求
 */
function haowiki_handle_generate_thumbnail() {
    if (!isset($_GET['post_id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'haowiki_generate_thumbnail')) {
        wp_die('无效请求');
    }
    
    $post_id = intval($_GET['post_id']);
    
    $result = haowiki_generate_featured_image_for_post($post_id);
    
    if ($result === true) {
        wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit&message=1'));
    } else {
        wp_die('生成特色图片失败: ' . $result);
    }
    
    exit;
}
add_action('admin_action_haowiki_generate_thumbnail', 'haowiki_handle_generate_thumbnail');