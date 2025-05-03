<?php
/**
 * Template Name: Navigation Page
 * Template Post Type: page
 *
 * @package Astra-Child
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div id="primary" <?php astra_primary_class(); ?>>
    <main id="main" class="site-main">
        <div class="ast-container">
            <div class="navigation-wrapper">
                <?php
                // 获取所有分类
                $categories = get_categories(array(
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_empty' => false
                ));

                // 遍历分类
                foreach ($categories as $category) {
                    // 获取分类下的文章
                    $posts = get_posts(array(
                        'category' => $category->term_id,
                        'numberposts' => 10,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                    ?>
                    <div class="nav-block">
                        <div class="nav-block-header">
                            <h2><?php echo esc_html($category->name); ?></h2>
                            <span class="post-count"><?php echo esc_html($category->count); ?> 篇文章</span>
                        </div>
                        <div class="nav-block-content">
                            <?php if ($posts) : ?>
                                <ul class="post-list">
                                    <?php foreach ($posts as $post) : ?>
                                        <li>
                                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" title="<?php echo esc_attr($post->post_title); ?>">
                                                <?php echo esc_html($post->post_title); ?>
                                            </a>
                                            <span class="post-date"><?php echo get_the_date('Y-m-d', $post->ID); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if ($category->count > 10) : ?>
                                    <div class="more-link">
                                        <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                                            查看更多 →
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else : ?>
                                <p class="no-posts">暂无文章</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>
</div>

<style>
.navigation-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.nav-block {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.nav-block:hover {
    transform: translateY(-3px);
}

.nav-block-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-block-header h2 {
    margin: 0;
    font-size: 1.2em;
    color: #333;
}

.post-count {
    font-size: 0.9em;
    color: #666;
}

.nav-block-content {
    padding: 15px 20px;
}

.post-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.post-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px dashed #eee;
}

.post-list li:last-child {
    border-bottom: none;
}

.post-list a {
    color: #333;
    text-decoration: none;
    flex: 1;
    margin-right: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.post-list a:hover {
    color: #0073aa;
}

.post-date {
    font-size: 0.85em;
    color: #999;
}

.more-link {
    text-align: right;
    margin-top: 10px;
}

.more-link a {
    color: #0073aa;
    text-decoration: none;
    font-size: 0.9em;
}

.more-link a:hover {
    text-decoration: underline;
}

.no-posts {
    color: #666;
    text-align: center;
    margin: 10px 0;
}

@media (max-width: 768px) {
    .navigation-wrapper {
        grid-template-columns: 1fr;
    }
    
    .nav-block-header {
        padding: 12px 15px;
    }
    
    .nav-block-content {
        padding: 12px 15px;
    }
}
</style>

<?php get_footer(); ?>