<?php

/**
 * Astra Child Theme functions and definitions 
 */
define('CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0');

//排队加载 css和JS
function child_enqueue_styles()
{

    wp_enqueue_style('astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all');
    //加载通用自定义custom.js文件
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), '1.0', true);
    //加载fontawesome css库
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    // 添加ajaxurl到前端
    wp_localize_script('custom-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);


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


// add_action( 'astra_content_top', 'my_custom_content_top' );
// function my_custom_content_top() {
//     if ( is_single() ) {
//         echo '<div class="injected_top"
//             <h3 style="color: #333; margin-bottom: 15px;">Arduino 项目</h3>
//             <div style="color: #666;">' . 
//             do_shortcode('[catlist categorypage="yes"]') . 
//             '</div>
//         </div>';
//     }
// }


// 注册自定义文章类型changlog
function register_changelog_post_type()
{
    register_post_type(
        'changelog',
        array(
            'labels' => array(
                'name' => __('Changelog'),
                'singular_name' => __('Changelog Entry'),
                'add_new' => __('Add New Entry'),
                'add_new_item' => __('Add New Changelog Entry'),
                'edit_item' => __('Edit Changelog Entry'),
            ),
            'public' => true,
            'publicly_queryable' => false, // 禁用单个文章页面访问
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array('title', 'editor', 'author'),
            'menu_icon' => 'dashicons-backup',
            'show_in_rest' => true, // 启用古腾堡编辑器支持
        )
    );
}
add_action('init', 'register_changelog_post_type');
// 可选：添加自定义区块样式
function changelog_block_styles()
{
    wp_enqueue_style(
        'changelog-styles',
        get_template_directory_uri() . '/assets/css/changelog.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'changelog_block_styles');


// 在内容顶部加载自定义侧边栏
// add_action('astra_content_top', 'load_doc_sidebar');
// function load_doc_sidebar() {
//     if (is_single() && !has_category('gushici') ){ // 仅在单篇文章页面加载
//         // 加载 docsidebar.php 文件
//         get_template_part('docsidebar');
//     }
// }



function enqueue_docsidebar_scripts()
{
    wp_enqueue_script(
        'docsidebar-js',
        get_stylesheet_directory_uri() . '/js/docsidebar.js', // 使用子主题目录
        array('jquery'), // 依赖 jQuery
        null,
        true // 在页面底部加载
    );
}
add_action('wp_enqueue_scripts', 'enqueue_docsidebar_scripts');






// / 使用 ACF字段和Astra 钩子在文章内容顶部显示作者头像
add_action('astra_entry_top', 'display_custom_author_avatar');

function display_custom_author_avatar()
{
    // 仅在单篇文章页面显示
    if (!is_single()) {
        return;
    }

    // 获取文章作者的 ID
    $author_id = get_the_author_meta('ID');

    // 获取作者头像字段（ACF）
    $author_avatar = get_field('custom_author_avatar', 'user_' . $author_id);

    // 如果设置了自定义头像，显示头像；否则显示默认 Gravatar
    if ($author_avatar) {
        echo '<div class="custom-author-avatar" style="text-align: center; margin-bottom: 20px;">';
        echo '<img src="' . esc_url($author_avatar['url']) . '" alt="作者头像" style="width: 100px; height: 100px; border-radius: 50%;">';
        echo '</div>';
    } else {
        // 显示默认 Gravatar
        echo '<div class="custom-author-avatar" style="text-align: center; margin-bottom: 20px;">';
        echo get_avatar($author_id, 100);
        echo '</div>';
    }
}



// 获取ACF当前分类的特色图像, 使用 Astra 钩子在文章内容底部显示分类特色图像
function get_current_category_featured_image()
{
    if (is_single()) { // 确保只在单篇文章页面执行
        // 获取当前文章的分类
        $categories = get_the_category();
        if (!empty($categories)) {
            // 获取第一个分类（或主分类）
            $category_id = $categories[0]->term_id;

            // 从 ACF 获取分类的特色图像
            $category_featured_image = get_field('category_featured_image', 'category_' . $category_id);

            // 如果存在分类特色图像，返回 HTML
            if ($category_featured_image && isset($category_featured_image['url'])) {
                return '<div class="category-featured-image">
                            <img src="' . esc_url($category_featured_image['url']) . '" alt="' . esc_attr($categories[0]->name) . '" style="width: 100%; max-width: 800px; height: auto;">
                        </div>';
            }
        }
    }
    return ''; // 如果没有图片或不在单篇文章页面，返回空
}
add_action('astra_entry_bottom', 'display_category_featured_image');
function display_category_featured_image()
{
    // 获取并输出分类的特色图像
    echo get_current_category_featured_image();
}


// 创建收藏按钮动作
function add_favorite_button()
{
    if (is_single() && is_user_logged_in()) {
        $post_id = get_the_ID();
        $user_id = get_current_user_id();
        $favorites = get_user_meta($user_id, 'user_favorites', true);

        if (!is_array($favorites)) {
            $favorites = array();
        }

        $is_favorited = in_array($post_id, $favorites);
        $button_text = $is_favorited ? '取消收藏' : '收藏文章';
        $button_class = $is_favorited ? 'favorited' : '';

        echo '<button class="favorite-button ' . $button_class . '" data-post-id="' . $post_id . '">' . $button_text . '</button>';
    }
}
add_action('astra_entry_content_after', 'add_favorite_button');
// 处理AJAX收藏请求
function handle_favorite_action()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('请先登录');
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);

    if (!is_array($favorites)) {
        $favorites = array();
    }

    if (in_array($post_id, $favorites)) {
        $favorites = array_diff($favorites, array($post_id));
        $action = 'removed';
    } else {
        $favorites[] = $post_id;
        $action = 'added';
    }

    update_user_meta($user_id, 'user_favorites', $favorites);

    wp_send_json_success(array(
        'action' => $action,
        'message' => $action === 'added' ? '收藏成功' : '已取消收藏'
    ));
}
add_action('wp_ajax_handle_favorite', 'handle_favorite_action');



// 在文章编辑后台右侧显示自定义字段设置
function show_is_doc_meta_box()
{
    // 确保自定义字段模块可见
    add_post_type_support('post', 'custom-fields');

    add_meta_box(
        'is_doc_meta_box',           // ID
        '文档类型设置',              // 标题
        'is_doc_meta_box_callback',  // 回调函数
        'post',                       // 显示位置
        'side',                      // 设置为side表示显示在右侧
        // 'high'                       // 优先级设为high，让它显示在较上方

    );
}
add_action('add_meta_boxes', 'show_is_doc_meta_box');
// meta box的内容
function is_doc_meta_box_callback($post)
{
    wp_nonce_field('is_doc_save', 'is_doc_nonce');
    $value = get_post_meta($post->ID, 'is_doc', true);
?>
    <label>
        <input type="checkbox" name="is_doc" value="true" <?php checked($value, 'true'); ?> />
        这是一篇文档类文章
    </label>
<?php
}
// 保存meta值
function save_is_doc_meta($post_id)
{
    if (
        !isset($_POST['is_doc_nonce']) ||
        !wp_verify_nonce($_POST['is_doc_nonce'], 'is_doc_save')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['is_doc'])) {
        update_post_meta($post_id, 'is_doc', 'true');
    } else {
        delete_post_meta($post_id, 'is_doc');
    }
}
add_action('save_post', 'save_is_doc_meta');


//在后台文章列表增加一列，显示文档还是普通，增加快速编辑时设置is doc
// 在文章列表添加自定义列（已有）
function add_is_doc_column($columns)
{
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['is_doc'] = __('文档类型', 'csrwiki');
        }
    }
    return $new_columns;
}
add_filter('manage_posts_columns', 'add_is_doc_column');
// 显示自定义列的内容（已有）
function show_is_doc_column_content($column, $post_id)
{
    if ($column === 'is_doc') {
        $is_doc = get_post_meta($post_id, 'is_doc', true);
        if ($is_doc) {
            echo '<span style="color: #2271b1;">📚 文档</span>';
        } else {
            echo '<span style="color: #666;">📰 普通</span>';
        }
    }
}
add_action('manage_posts_custom_column', 'show_is_doc_column_content', 10, 2);
// 添加快速编辑字段
function add_quick_edit_is_doc($column_name, $post_type)
{
    if ($column_name !== 'is_doc') return;
?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label class="alignleft">
                <input type="checkbox" name="is_doc" value="true">
                <span class="checkbox-title"><?php _e('标记为文档类文章', 'csrwiki'); ?></span>
            </label>
        </div>
    </fieldset>
<?php
}
add_action('quick_edit_custom_box', 'add_quick_edit_is_doc', 10, 2);
// 保存快速编辑的值
function save_quick_edit_is_doc($post_id)
{
    // 安全检查
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // 保存is_doc值
    if (isset($_POST['is_doc'])) {
        update_post_meta($post_id, 'is_doc', 'true');
    } else {
        delete_post_meta($post_id, 'is_doc');
    }
}
add_action('save_post', 'save_quick_edit_is_doc');
// 添加必要的JavaScript来处理快速编辑
function add_quick_edit_js()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 保存原始的inlineEditPost.edit函数
            var $wp_inline_edit = inlineEditPost.edit;

            // 重写inlineEditPost.edit函数
            inlineEditPost.edit = function(id) {
                // 调用原始的edit函数
                $wp_inline_edit.apply(this, arguments);

                // 获取post ID
                var post_id = 0;
                if (typeof(id) == 'object') {
                    post_id = parseInt(this.getId(id));
                }

                // 获取行数据并设置复选框
                if (post_id > 0) {
                    var edit_row = $('#edit-' + post_id);
                    var post_row = $('#post-' + post_id);

                    // 检查是否是文档
                    var is_doc = post_row.find('.is_doc span').text().indexOf('文档') !== -1;

                    // 设置复选框状态
                    edit_row.find('input[name="is_doc"]').prop('checked', is_doc);
                }
            };
        });
    </script>
<?php
}
add_action('admin_footer-edit.php', 'add_quick_edit_js');


// 在文章前端标题后显示，测试字段是否正常
add_action('astra_entry_content_before', function () {
    if (is_single() && current_user_can('administrator')) {
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0;">';
        echo '文章ID: ' . get_the_ID() . '<br>';
        echo 'is_doc值: ' . get_post_meta(get_the_ID(), 'is_doc', true);
        echo '</div>';
    }
});



// 注册"仅管理员可见"的区块================================================================
// function register_admin_only_block()
// {
//     wp_register_script(
//         'admin-only-block',
//         get_stylesheet_directory_uri() . '/js/admin-only-block.js',
//         array('wp-blocks', 'wp-element', 'wp-editor')
//     );


//     register_block_type('csrwiki/admin-only', array(
//         'editor_script' => 'admin-only-block',
//         'render_callback' => 'render_admin_only_block'
//     ));
// }
// add_action('init', 'register_admin_only_block');

// 渲染回调函数
function render_admin_only_block($attributes, $content)
{
    if (current_user_can('administrator')) {
        return $content;
    }
    return ''; // 非管理员看不到内容
}

// 加载编辑器CSS和JS，专供管理后台使用的。与前端页面无关
add_action('enqueue_block_editor_assets', 'my_editor_scripts');
function my_editor_scripts()
{
    wp_enqueue_script(
        'my-editor-custom-js',
        get_stylesheet_directory_uri() . '/js/editor-custom.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        '1.0',
        true
    );
}

// // 处理前端显示逻辑
// add_filter('render_block', 'filter_admin_only_blocks', 10, 2);
// function filter_admin_only_blocks($block_content, $block)
// {
//     // 如果块没有设置adminOnly属性，直接返回原内容
//     if (empty($block['attrs']) || !isset($block['attrs']['adminOnly'])) {
//         return $block_content;
//     }

//     // 只有设置了adminOnly时才检查权限
//     if ($block['attrs']['adminOnly'] && !current_user_can('manage_options')) {
//         return '';
//     }

//     return $block_content;
// }


// 首先注册样式
add_action('wp_head', 'add_admin_only_block_styles');
function add_admin_only_block_styles() {
    if (current_user_can('manage_options')) {
        echo '<style>
            .admin-only-block {
                position: relative;
                border: 2px dashed #ff6b6b;
                padding: 15px;
                margin: 10px 0;
                background-color: rgba(255, 107, 107, 0.05);
            }
            .admin-only-notice {
                position: absolute;
                top: -10px;
                right: 10px;
                background-color: #ff6b6b;
                color: white;
                font-size: 12px;
                padding: 2px 8px;
                border-radius: 3px;
            }
        </style>';
    }
}

// 然后使用过滤器处理块
add_filter('render_block', 'filter_admin_only_blocks', 10, 2);
function filter_admin_only_blocks($block_content, $block)
{
    // 如果块没有设置adminOnly属性，直接返回原内容
    if (empty($block['attrs']) || !isset($block['attrs']['adminOnly'])) {
        return $block_content;
    }

    // 只有设置了adminOnly时才检查权限
    if ($block['attrs']['adminOnly'] && !current_user_can('manage_options')) {
        return '';
    }

    // 为管理员添加视觉标识，包装内容并添加样式
    if (current_user_can('manage_options')) {
        $admin_notice = '<div class="admin-only-notice">仅管理员可见</div>';
        $styled_content = '<div class="admin-only-block">' . $admin_notice . $block_content . '</div>';
        return $styled_content;
    }

    return $block_content;
}

// ============================================================================================



// 通过钩子astra_content_top,实现single post三栏布局。左侧是docsidebar，中间是主内容，右侧也加载了侧边栏
//注意astra我设置的是默认右侧边栏，此处代码是注入左侧边栏
function display_left_doc_sidebar()
{
    // 检查是否是单篇文章页面,只在单文章页面显示，且csr分类下的文章不显示。
    if (is_single() && !has_category('csr')) {
        if (is_active_sidebar('left-doc')) {
            echo '<div class="left-doc-sidebar">';
            dynamic_sidebar('left-doc');
            echo '</div>'; // 关闭div，非常重要。如果注入top，则需要这个关闭div;如果注入bottom，则不需要这个关闭div
        }
    }
}
add_action('astra_content_top', 'display_left_doc_sidebar');


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


// 注册新的侧边栏left_doc_sidebar，single post在用；
// function register_left_doc_sidebar() {
//     register_sidebar(array(
//         'name'          => 'Left Doc Sidebar',
//         'id'            => 'left-doc',
//         'description'   => 'This is the Left Doc sidebar, displayed at the left of single post.',
//         'before_widget' => '<div id="%1$s" class="widget %2$s">',
//         'after_widget'  => '</div>',
//         'before_title'  => '<h2 class="widgettitle">',
//         'after_title'   => '</h2>',
//     ));
// }
// add_action('widgets_init', 'register_left_doc_sidebar');










// 添加到functions.php
function add_custom_breadcrumb()
{
    // 只在单篇文章页面显示
    if (!is_single()) {
        return;
    }

    // 获取当前文章的分类
    $categories = get_the_category();
    $category = !empty($categories) ? $categories[0] : null;

    // 构建HTML
    $html = '<div class="custom-breadcrumb">';

    // 首页链接
    $html .= '<a href="' . home_url() . '">首页</a>';
    $html .= '<span class="separator"> > </span>';

    // 分类链接
    if ($category) {
        $html .= '<a href="' . get_category_link($category->term_id) . '">';
        $html .= $category->name;
        $html .= '</a>';
        $html .= '<span class="separator"> > </span>';
    }

    // 当前文章标题
    $html .= '<span class="current">' . get_the_title() . '</span>';

    $html .= '</div>';

    // 输出HTML
    echo $html;
}

// 添加样式
function add_breadcrumb_styles()
{
?>
    <style>
        .custom-breadcrumb {
            padding: 10px 0;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }

        .custom-breadcrumb a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .custom-breadcrumb a:hover {
            color: #9800ff;
        }

        .custom-breadcrumb .separator {
            margin: 0 8px;
            color: #999;
        }

        .custom-breadcrumb .current {
            color: #333;
            font-weight: 500;
        }
    </style>
<?php
}

// 使用Astra钩子添加面包屑
add_action('astra_content_before', 'add_custom_breadcrumb');
// 添加样式
add_action('astra_head_top', 'add_breadcrumb_styles');


// 支付测试
/**
 * Load Payment System
 */
require_once get_stylesheet_directory() . '/payment-system/init.php';


add_action('wp_ajax_nopriv_register_and_verify_payment', 'csrwiki_register_and_verify_payment');


// 在functions.php中添加以下函数
function csrwiki_display_post_tags()
{
    // 只在单篇文章页面显示
    if (is_single()) {
        $post_tags = get_the_tags();
        if ($post_tags) {
            echo '<div class="csrwiki-post-tags">';
            echo '<span class="tags-title">标签：</span>';
            foreach ($post_tags as $tag) {
                echo '<a href="' . get_tag_link($tag->term_id) . '" class="tag-link">' . $tag->name . '</a>';
            }
            echo '</div>';
        }
    }
}
add_action('astra_entry_bottom', 'csrwiki_display_post_tags');


// 在functions.php中添加以下代码
function register_book_post_type()
{
    $labels = array(
        'name' => '书籍',
        'singular_name' => '书籍',
        'menu_name' => '书籍'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array(
            'slug' => 'books',           // URL会变成 csrwiki.com/books/文章名
            'with_front' => false        // 禁用默认固定链接前缀
        )
    );

    register_post_type('book', $args);
}
add_action('init', 'register_book_post_type');


// 添加文章来源meta box
function add_article_source_meta_box()
{
    add_meta_box(
        'article_source_box', // ID
        '文章来源', // 标题
        'article_source_callback', // 回调函数
        'post', // 文章类型
        'side', // 位置
        'high' // 优先级
    );
}
add_action('add_meta_boxes', 'add_article_source_meta_box');

// meta box回调函数
function article_source_callback($post)
{
    wp_nonce_field('article_source_save', 'article_source_nonce');
    $source = get_post_meta($post->ID, '_article_source', true);
?>
    <select name="article_source" id="article_source">
        <option value="">请选择来源</option>
        <option value="original" <?php selected($source, 'original'); ?>>原创</option>
        <option value="translated" <?php selected($source, 'translated'); ?>>翻译</option>
        <option value="repost" <?php selected($source, 'repost'); ?>>转载</option>
    </select>
<?php
}

// 保存meta数据
function save_article_source($post_id)
{
    if (!isset($_POST['article_source_nonce'])) return;
    if (!wp_verify_nonce($_POST['article_source_nonce'], 'article_source_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['article_source'])) {
        update_post_meta($post_id, '_article_source', $_POST['article_source']);
    }
}
add_action('save_post', 'save_article_source');



function display_article_source($content)
{
    if (is_single()) {
        $post_id = get_the_ID();
        $source = get_post_meta($post_id, '_article_source', true);

        $source_text = '';
        $source_class = '';

        switch ($source) {
            case 'original':
                $source_text = '原创';
                $source_class = 'source-original';
                break;
            case 'translated':
                $source_text = '翻译';
                $source_class = 'source-translated';
                break;
            case 'repost':
                $source_text = '转载';
                $source_class = 'source-repost';
                break;
        }

        if ($source) {
            $tag = sprintf('<span class="article-source-tag %s">%s</span>', $source_class, $source_text);
            return $tag . $content;
        }
    }
    return $content;
}
add_filter('the_content', 'display_article_source');




//引入维护模式设置文件
require_once get_stylesheet_directory() . '/inc/maintenance-mode.php';

// 引入右下角弹窗.php
// require_once get_stylesheet_directory() . '/inc/右下角弹窗.php';



//====================================================================


function enqueue_highlightjs_cdn() {
    // 加载 Highlight.js 的 CSS（可以更换为你喜欢的样式主题）
    wp_enqueue_style('highlightjs-style', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css');
    
    // 加载 Highlight.js 的 JS 文件
    wp_enqueue_script('highlightjs-script', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js', array(), null, true);
    
    // 初始化 Highlight.js
    wp_add_inline_script('highlightjs-script', 'hljs.highlightAll();');
}
add_action('wp_enqueue_scripts', 'enqueue_highlightjs_cdn');



//====================================================================

/**
 * 为主菜单添加登录/退出按钮 
 */
function add_login_logout_menu_item($items, $args) {
    // 仅在主菜单添加按钮
    if ($args->theme_location != 'primary') {
        return $items;
    }
    
    // 获取当前用户状态
    $is_logged_in = is_user_logged_in();
    
    // 设置按钮文本和URL
    if ($is_logged_in) {
        $button_text = '退出';
        $button_url = wp_logout_url(home_url());
        $button_class = 'menu-button logout-button';
    } else {
        $button_text = '登录';
        $button_url = wp_login_url(get_permalink());
        $button_class = 'menu-button login-button';
    }
    
    // 创建菜单项HTML
    $button_item = '<li>';
    $button_item .= '<a href="' . esc_url($button_url) . '" class="' . esc_attr($button_class) . '">' . esc_html($button_text) . '</a>';
    $button_item .= '</li>';
    
    // 添加到菜单末尾
    $items .= $button_item;
    
    return $items;
}
add_filter('wp_nav_menu_items', 'add_login_logout_menu_item', 10, 2);


//====================================================================
