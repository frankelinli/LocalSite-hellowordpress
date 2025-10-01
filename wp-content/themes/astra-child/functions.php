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



// // 通过钩子astra_content_top,实现single post三栏布局。左侧是docsidebar，中间是主内容，右侧也加载了侧边栏
// //注意astra我设置的是默认右侧边栏，此处代码是注入左侧边栏
// function display_left_doc_sidebar()
// {
//     // 检查是否是单篇文章页面,只在单文章页面显示，且csr分类下的文章不显示。
//     if (is_single() && !has_category('csr')) {
//         if (is_active_sidebar('left-doc')) {
//             echo '<div class="left-doc-sidebar">';
//             dynamic_sidebar('left-doc');
//             echo '</div>'; // 关闭div，非常重要。如果注入top，则需要这个关闭div;如果注入bottom，则不需要这个关闭div
//         }
//     }
// }
// add_action('astra_content_top', 'display_left_doc_sidebar');



// // 注册新的侧边栏left_doc_sidebar，single post在用；
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




// // 注册自定义菜单位置
// function register_docs_menu() {
//     register_nav_menu('docs-menu',__( 'Docs Menu' ));
// }
// add_action( 'after_setup_theme', 'register_docs_menu' );

// // 把菜单显示在某个 Astra Hook 位置（比如内容区左边）
// add_action('astra_content_top','add_docs_sidebar');
// function add_docs_sidebar(){
//     if ( is_singular('post') || is_page() ) {   // 只在文章/页面里显示
//         echo '<aside class="docs-sidebar">';
//         wp_nav_menu(array(
//             'theme_location' => 'docs-menu',
//             'container' => false,
//             'menu_class' => 'docs-menu',
//         ));
//         echo '</aside>';
//     }
// }


//=================================================================================

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





//引入维护模式设置文件
require_once get_stylesheet_directory() . '/inc/maintenance-mode.php';



//====================================================================

//给全站代码高亮
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



// 修改摘要长度
function custom_excerpt_length($length) {
    return 120; // 设置你想要的摘要字数
}
add_filter('excerpt_length', 'custom_excerpt_length', 999);

// 可选：自定义摘要末尾显示的内容
function custom_excerpt_more($more) {
    return '>>>'; // 设置摘要结尾的显示内容
}
add_filter('excerpt_more', 'custom_excerpt_more');

//=======================================================================

// 在文章内容后添加阅读按钮
function add_read_button_after_excerpt() {
    if (is_archive() || is_home() || is_search()) {
        echo '<div class="read-more-button-wrap"><a href="' . esc_url(get_permalink()) . '" class="read-more-button">阅读</a></div>';
    }
}
add_action('astra_entry_content_after', 'add_read_button_after_excerpt', 10);


//===========================================================================


/**
 * 给inc目录下的小功能增加可视化的开启、关闭开关；方便快速测试、预览
 */
function haowiki_theme_settings_page() {
    add_menu_page(
        '主题功能设置',
        '主题功能',
        'manage_options',
        'haowiki-settings',
        'haowiki_render_settings_page',
        'dashicons-admin-generic',
        60
    );
}
add_action('admin_menu', 'haowiki_theme_settings_page');

/**
 * 渲染设置页面
 */
function haowiki_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>主题功能设置</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('haowiki_theme_options');
            do_settings_sections('haowiki-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * 获取inc目录中的所有PHP文件
 */
function haowiki_get_inc_files() {
    $inc_dir = get_stylesheet_directory() . '/inc';
    $files = array();
    
    if (is_dir($inc_dir)) {
        $dir_contents = scandir($inc_dir);
        
        foreach ($dir_contents as $file) {
            $file_path = $inc_dir . '/' . $file;
            if (is_file($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'php') {
                // 获取不带扩展名的文件名作为功能ID
                $module_id = pathinfo($file, PATHINFO_FILENAME);
                
                // 读取文件头部注释以获取功能名称
                $file_content = file_get_contents($file_path);
                $module_name = $module_id; // 默认名称
                
                // 尝试从文件注释中提取功能名称
                if (preg_match('/\*\s*功能名称:\s*(.+)$/mi', $file_content, $matches)) {
                    $module_name = trim($matches[1]);
                }
                
                $files[$module_id] = array(
                    'name' => $module_name,
                    'path' => $file_path
                );
            }
        }
    }
    
    return $files;
}

/**
 * 注册设置选项
 */
function haowiki_register_settings() {
    register_setting('haowiki_theme_options', 'haowiki_enabled_modules');
    
    add_settings_section(
        'haowiki_modules_section',
        '可用功能模块',
        function() { echo '选择要启用的功能模块:'; },
        'haowiki-settings'
    );
    
    // 自动获取模块列表
    $modules = haowiki_get_inc_files();
    $enabled_modules = get_option('haowiki_enabled_modules', array());
    
    // 添加复选框
    foreach ($modules as $id => $module) {
        add_settings_field(
            'module_' . $id,
            $module['name'],
            function() use ($id, $enabled_modules) {
                $checked = in_array($id, (array)$enabled_modules) ? 'checked' : '';
                echo '<input type="checkbox" id="' . $id . '" name="haowiki_enabled_modules[]" value="' . $id . '" ' . $checked . '>';
            },
            'haowiki-settings',
            'haowiki_modules_section'
        );
    }
}
add_action('admin_init', 'haowiki_register_settings');

/**
 * 加载已启用的模块
 */
function haowiki_load_enabled_modules() {
    $enabled_modules = get_option('haowiki_enabled_modules', array());
    $available_modules = haowiki_get_inc_files();
    
    // 加载启用的模块
    foreach ((array)$enabled_modules as $module_id) {
        if (isset($available_modules[$module_id])) {
            require_once $available_modules[$module_id]['path'];
        }
    }
}
add_action('after_setup_theme', 'haowiki_load_enabled_modules');

//=======================================================================

//==============================================================================

// 在媒体设置页面显示所有注册的图片尺寸
add_action('admin_notices', function () {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'options-media') {
        global $_wp_additional_image_sizes;

        echo '<div style="background:#111;color:#0f0;padding:10px;margin-bottom:20px;"><pre style="margin:0;">';
        echo "=== WordPress本身已注册的图片尺寸 ===\n";

        // 核心默认尺寸
        echo "thumbnail: " . get_option('thumbnail_size_w') . "x" . get_option('thumbnail_size_h') . " (crop: " . (get_option('thumbnail_crop') ? 'true' : 'false') . ")\n";
        echo "medium: " . get_option('medium_size_w') . "x" . get_option('medium_size_h') . "\n";
        echo "medium_large: " . get_option('medium_large_size_w') . "x" . get_option('medium_large_size_h') . "\n";
        echo "large: " . get_option('large_size_w') . "x" . get_option('large_size_h') . "\n";

        // 额外注册的尺寸
        if (!empty($_wp_additional_image_sizes)) {
            foreach ($_wp_additional_image_sizes as $name => $size) {
                echo $name . ': ' . $size['width'] . 'x' . $size['height'] . ' (crop: ' . ($size['crop'] ? 'true' : 'false') . ")\n";
            }
        } else {
            echo "没有额外注册的尺寸\n";
        }

        echo '</pre></div>';
    }
});

// remove_image_size 只能移除通过 add_image_size 注册的尺寸，无法移除核心默认的尺寸
// 所以我们通过两个步骤来禁用不需要的尺寸：
// 1. 使用 remove_image_size 移除通过 add_image_size 注册的尺寸（如果有的话）
// 2. 使用 intermediate_image_sizes_advanced 过滤器来阻止生成这些尺寸
// 3. 禁用大图裁剪功能，防止生成 2560px 以上的大图尺寸  
add_action( 'init', function() {
    // remove_image_size( 'large' );
    remove_image_size( '1536x1536' );
    remove_image_size( '2048x2048' );
}, 20 );
// disable generation of large, 1536x1536, and 2048x2048 sizes
add_filter( 'intermediate_image_sizes_advanced', function( $sizes ) {
    // unset( $sizes['large'] );
    unset( $sizes['1536x1536'] );
    unset( $sizes['2048x2048'] );
    return $sizes;
} );

add_filter( 'big_image_size_threshold', '__return_false' );


// 注册微信公众号风格的大封面图（居中裁剪）
// add_image_size( 'wechat-large-cover', 900, 383, true );

// // 注册微信公众号风格的小封面图（居中裁剪）
// add_image_size( 'wechat-square-cover', 383, 383, true );





// 强制指定特色图像大小为 wechat-large-cover
// add_filter('post_thumbnail_size', 'use_wechat_large_cover_size');
// function use_wechat_large_cover_size($size) {
//     if (is_single()) {
//         return 'wechat-large-cover';
//     }
//     return $size;
// }

// 强制指定文章正文的特色图像采用medium尺寸
// add_filter('post_thumbnail_size', 'use_medium_size');
// function use_medium_size($size) {
//     if (is_single()) {
//         return 'medium_large'; // 或者 'medium'，根据你的需求选择
//     }
//     return $size;
// }