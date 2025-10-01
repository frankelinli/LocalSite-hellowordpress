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
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/page-templates/test-template.css" />

<div class="tpl-container">
    <main class="tpl-main">
        <div class="recent-posts-list">
    <?php
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $posts_query = new WP_Query([
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'paged' => $paged
    ]);
    
    if ($posts_query->have_posts()) :
        while ($posts_query->have_posts()) : $posts_query->the_post();
            $has_thumb = has_post_thumbnail();
    ?>
    <div class="post-row">
        <div class="post-thumb">
            <?php if ($has_thumb): ?>
                <a href="<?php the_permalink(); ?>">
                    <?php echo get_the_post_thumbnail(get_the_ID(), 'thumbnail', ['class' => 'thumb-img']); ?>
                </a>
            <?php else: ?>
                <div class="no-thumb">无图</div>
            <?php endif; ?>
        </div>
        <div class="post-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>
        <div class="post-date">
            <?php echo get_the_date('Y-m-d'); ?>
        </div>
    </div>
    <?php
        endwhile;
    endif;
    ?>
        </div>

        <!-- 分页导航 -->
        <?php if ($posts_query->max_num_pages > 1) : ?>
        <div class="pagination-wrapper">
            <?php
            $big = 999999999;
            echo paginate_links([
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => max(1, $paged),
                'total' => $posts_query->max_num_pages,
                'prev_text' => '‹ 上一页',
                'next_text' => '下一页 ›',
                'show_all' => false,
                'end_size' => 2,
                'mid_size' => 1,
                'type' => 'list',
                'add_args' => false
            ]);
            ?>
        </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </main>
    <aside class="tpl-sidebar">
        <?php if (function_exists('get_sidebar')) { get_sidebar(); } else { dynamic_sidebar('sidebar-1'); } ?>
    </aside>
</div>




<?php 


get_footer(); ?>