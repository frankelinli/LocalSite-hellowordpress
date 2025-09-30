<?php
/**
 * Template Name: Test Template
 * Template Post Type: page
 *
 * @package Astra-Child
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<!-- 引入单独的测试样式文件 -->
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/test-template.css" />

<div class="tpl-container">
    <main class="tpl-main">
        <div class="recent-posts-list">
    <?php
    $recent_posts = get_posts([
        'numberposts' => 5,
        'post_status' => 'publish',
    ]);
    foreach ($recent_posts as $post) :
        setup_postdata($post);
        // 使用 get_the_post_thumbnail 输出更语义化的 img 标记和 srcset 支持
        $has_thumb = has_post_thumbnail($post->ID);
    ?>
    <div class="post-row">
        <div class="post-thumb">
            <?php if ($has_thumb): ?>
                <a href="<?php the_permalink(); ?>">
                    <?php echo get_the_post_thumbnail($post->ID, 'thumbnail', ['class' => 'thumb-img']); ?>
                </a>
            <?php else: ?>
                <div class="no-thumb">无图</div>
            <?php endif; ?>
        </div>
        <div class="post-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>
        <div class="post-date">
            <?php echo get_the_date('Y-m-d', $post->ID); ?>
        </div>
    </div>
    <?php
    endforeach;
    wp_reset_postdata();
    ?>
        </div>
    </main>
    <aside class="tpl-sidebar">
        <?php if (function_exists('get_sidebar')) { get_sidebar(); } else { dynamic_sidebar('sidebar-1'); } ?>
    </aside>
</div>




<?php 


get_footer(); ?>