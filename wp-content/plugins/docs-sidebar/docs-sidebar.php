<?php
/**
 * Plugin Name: Docs Sidebar
 * Description: 在文章页显示同分类下带 doc 标签的文档列表。
 * Version: 1.0
 * Author: Jerry
 */

if ( ! defined( 'ABSPATH' ) ) exit; // 防止直接访问

// 添加侧边栏
add_action('astra_content_top', 'add_docs_sidebar');
function add_docs_sidebar(){
    if ( is_singular('post') ) {
        echo '<aside class="docs-sidebar">';
        
        $categories = get_the_category();
        if (!empty($categories)) {
            $main_category = $categories[0];
            
            $doc_posts = get_posts(array(
                'category__in' => array($main_category->term_id),
                'tag' => 'doc',
                'numberposts' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
                'post_status' => 'publish'
            ));
            
            if (!empty($doc_posts)) {
                echo '<div class="doc-menu-container">';
                echo '<h3 class="doc-menu-title">' . esc_html($main_category->name) . ' 文档</h3>';
                echo '<ul class="doc-menu-list">';
                
                $current_post_id = get_the_ID();
                
                foreach ($doc_posts as $post) {
                    setup_postdata($post);
                    $is_current = ($post->ID == $current_post_id) ? 'current-doc' : '';
                    
                    echo '<li class="doc-menu-item ' . esc_attr($is_current) . '">';
                    echo '<a href="' . esc_url(get_permalink($post->ID)) . '">';
                    echo esc_html(get_the_title($post->ID));
                    echo '</a>';
                    echo '</li>';
                }
                
                wp_reset_postdata();
                echo '</ul></div>';
            } else {
                echo '<div class="no-docs-message">';
                echo '<p>该分类暂无文档</p>';
                echo '</div>';
            }
        }
        
        echo '</aside>';
    }
}

// 插件 CSS
add_action('wp_enqueue_scripts', 'docs_sidebar_styles');
function docs_sidebar_styles() {
    $css_url = plugins_url('docs-sidebar.css', __FILE__);
    wp_enqueue_style('docs-sidebar-style', $css_url, array(), '1.0');
}