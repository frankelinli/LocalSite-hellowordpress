<?php
/**
 * 功能名称: 侧边栏钩子显示页面技术信息
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


function haowiki_add_page_info_sidebar() {
    // 只在管理员登录时显示
    if (!current_user_can('administrator')) {
        return;
    }
    
    // 只在单页面和文章页面显示
    if (!is_singular()) {
        return;
    }
    
    // 获取当前页面/文章对象
    global $post, $template;
    
    // 获取分类信息
    $categories = array();
    if (is_singular('post')) {
        $cats = get_the_category($post->ID);
        if (!empty($cats)) {
            foreach ($cats as $cat) {
                $categories[] = $cat->name;
            }
        }
    }
    
    // 获取标签信息
    $tags = array();
    if (is_singular('post')) {
        $post_tags = get_the_tags($post->ID);
        if (!empty($post_tags)) {
            foreach ($post_tags as $tag) {
                $tags[] = $tag->name;
            }
        }
    }
    
    // 收集页面信息
    $page_info = array(
        '页面ID' => $post->ID,
        '页面标题' => get_the_title(),
        '发布日期' => get_the_date(),
        '最后修改' => get_the_modified_date(),
        '作者' => get_the_author(),
        '页面类型' => get_post_type(),
        '评论数量' => get_comments_number(),
        '固定链接' => get_permalink(),
        '所在分类' => !empty($categories) ? implode(', ', $categories) : '无',
        '标签' => !empty($tags) ? implode(', ', $tags) : '无',
        '当前模板' => basename($template),
        '模板路径' => str_replace(ABSPATH, '', $template),
        '父级模板' => get_template(),
        '子主题' => get_stylesheet() !== get_template() ? get_stylesheet() : '无',
        '当前路径' => $_SERVER['REQUEST_URI'],
        '查询字符串' => !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '无',
        '页面状态' => get_post_status(),
        '加载时间' => timer_stop(0, 3) . '秒'
    );
    
    // 元字段(自定义字段)
    $meta_keys = get_post_custom_keys($post->ID);
    $meta_fields = array();
    if (!empty($meta_keys)) {
        foreach ($meta_keys as $key) {
            // 跳过WordPress内部使用的元字段
            if (strpos($key, '_') === 0) continue;
            $values = get_post_custom_values($key, $post->ID);
            if (!empty($values)) {
                $meta_fields[$key] = implode(', ', $values);
            }
        }
    }
    
    // 创建技术信息面板
    ob_start();
    ?>
    <style>
        #haowiki-page-info {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        #haowiki-page-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            color: #0073aa;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        #haowiki-page-info h4 {
            margin: 15px 0 5px;
            font-size: 15px;
            color: #23282d;
        }
        #haowiki-page-info .info-item {
            margin-bottom: 5px;
            word-break: break-word;
        }
        #haowiki-page-info .info-label {
            font-weight: bold;
        }
        #haowiki-page-info .resource-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #eee;
            padding: 5px;
            background: #fff;
            font-family: monospace;
            font-size: 12px;
        }
        #haowiki-page-info .toggle-button {
            background: #0073aa;
            color: white;
            border: none;
            padding: 3px 8px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
        }
        #haowiki-page-info .toggle-button:hover {
            background: #005a87;
        }
        .haowiki-page-hidden {
            display: none;
        }
    </style>
    
    <div id="haowiki-page-info">
        <h3>页面技术信息</h3>
        
        <h4>基本信息</h4>
        <?php foreach ($page_info as $label => $value): ?>
            <div class="info-item">
                <span class="info-label"><?php echo $label; ?>:</span> 
                <span class="info-value"><?php echo $value; ?></span>
            </div>
        <?php endforeach; ?>
        
        <?php if (!empty($meta_fields)): ?>
        <h4>自定义字段</h4>
        <?php foreach ($meta_fields as $key => $value): ?>
            <div class="info-item">
                <span class="info-label"><?php echo $key; ?>:</span> 
                <span class="info-value"><?php echo $value; ?></span>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
        
        <button id="toggle-resources" class="toggle-button">显示资源信息</button>
        
        <div id="resources-container" class="haowiki-page-hidden">
            <h4>加载的CSS文件</h4>
            <div class="resource-list">
                <?php
                global $wp_styles;
                if (!empty($wp_styles->queue)) {
                    foreach ($wp_styles->queue as $handle) {
                        echo $handle . ': ' . $wp_styles->registered[$handle]->src . '<br>';
                    }
                } else {
                    echo '没有加载的CSS文件';
                }
                ?>
            </div>
            
            <h4>加载的JS文件</h4>
            <div class="resource-list">
                <?php
                global $wp_scripts;
                if (!empty($wp_scripts->queue)) {
                    foreach ($wp_scripts->queue as $handle) {
                        echo $handle . ': ' . $wp_scripts->registered[$handle]->src . '<br>';
                    }
                } else {
                    echo '没有加载的JS文件';
                }
                ?>
            </div>
            
            <h4>页面查询</h4>
            <div class="resource-list">
                <?php
                global $wp_query;
                echo '<pre>';
                print_r($wp_query->query);
                echo '</pre>';
                ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#toggle-resources').on('click', function() {
                $('#resources-container').toggleClass('haowiki-page-hidden');
                $(this).text(function(i, text) {
                    return text === "显示资源信息" ? "隐藏资源信息" : "显示资源信息";
                });
            });
        });
        </script>
    </div>
    <?php
    $page_info_widget = ob_get_clean();
    
    // 将信息面板添加到侧边栏
    echo $page_info_widget;
}

// 使用侧边栏挂钩添加我们的面板
add_action('astra_sidebars_before', 'haowiki_add_page_info_sidebar');