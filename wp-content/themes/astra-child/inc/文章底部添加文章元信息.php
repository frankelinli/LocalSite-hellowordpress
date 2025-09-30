<?php
/**
 * 功能名称: 文章底部添加文章元信息
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


function haowiki_add_post_meta_footer() {
    // 只在单篇文章页面显示
    if (!is_single()) {
        return;
    }
    
    // 获取文章数据
    $post_id = get_the_ID();
    $author_id = get_post_field('post_author', $post_id);
    $author_name = get_the_author_meta('display_name', $author_id);
    $author_url = get_author_posts_url($author_id);
    $publish_date = get_the_date('Y-m-d');
    $modified_date = get_the_modified_date('Y-m-d');
    $categories = get_the_category();
    
    // 添加CSS样式
    echo '<style>
        .haowiki-post-meta {
            margin-top: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            font-size: 14px;
            color: #666;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .haowiki-post-meta-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .haowiki-post-meta-row:last-child {
            margin-bottom: 0;
        }
        .haowiki-post-meta-label {
            min-width: 100px;
            font-weight: 600;
        }
        .haowiki-post-meta a {
            color: #0073aa;
            text-decoration: none;
        }
        .haowiki-post-meta a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .haowiki-post-meta-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .haowiki-post-meta-label {
                margin-bottom: 4px;
            }
        }
    </style>';
    
    // 构建HTML输出
    $html = '<div class="haowiki-post-meta">';
    
    // 发布日期
    $html .= '<div class="haowiki-post-meta-row">';
    $html .= '<div class="haowiki-post-meta-label">发布日期：</div>';
    $html .= '<div>' . $publish_date . '</div>';
    $html .= '</div>';
    
    // 最后更新日期
    if ($publish_date != $modified_date) {
        $html .= '<div class="haowiki-post-meta-row">';
        $html .= '<div class="haowiki-post-meta-label">最后更新：</div>';
        $html .= '<div>' . $modified_date . '</div>';
        $html .= '</div>';
    }
    
    // 作者信息
    $html .= '<div class="haowiki-post-meta-row">';
    $html .= '<div class="haowiki-post-meta-label">作者：</div>';
    $html .= '<div><a href="' . esc_url($author_url) . '">' . esc_html($author_name) . '</a></div>';
    $html .= '</div>';
    
    // 分类信息
    if (!empty($categories)) {
        $html .= '<div class="haowiki-post-meta-row">';
        $html .= '<div class="haowiki-post-meta-label">分类：</div>';
        $html .= '<div>';
        
        $category_links = array();
        foreach ($categories as $category) {
            $category_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
        }
        $html .= implode('、', $category_links);
        
        $html .= '</div></div>';
    }
    
    $html .= '</div>';
    
    // 在文章内容后输出元信息
    echo $html;
}

// 添加到 the_content 钩子
function haowiki_append_post_meta_to_content($content) {
    if (is_single() && in_the_loop() && is_main_query()) {
        ob_start();
        haowiki_add_post_meta_footer();
        $meta_footer = ob_get_clean();
        $content .= $meta_footer;
    }
    return $content;
}
add_filter('the_content', 'haowiki_append_post_meta_to_content');