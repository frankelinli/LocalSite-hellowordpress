<?php
/**
 * 自定义帖子类型模板名称: 更新日志 (Changelog)
 * 
 * 此模板用于以日期降序显示更新日志的文章列表。
 * 每个更新日志条目包含标题、内容以及更新日期。
 * 
 * 功能特点:
 * - 获取并显示自定义文章类型 'changelog' 的所有文章。
 * - 点击标题时平滑滚动到对应的更新日志条目。
 * - 更新浏览器的 URL 哈希值而无需刷新页面。
 * - 应用自定义样式，提供简洁现代的设计。
 * 
 * 使用的主要技术:
 * - WordPress 模板层级结构
 * - 使用 WP_Query 获取自定义文章类型
 * - JavaScript 实现平滑滚动和哈希值操作
 * - PHP 动态渲染内容
 * - CSS 用于更新日志条目的样式设计
 */

get_header();
?>

<div class="changelog-container">
<div class="changelog-wrapper">
    <?php
    $changelogs = new WP_Query(array(
        'post_type' => 'changelog',
        'posts_per_page' => 3,
        'orderby' => 'date',
        'order' => 'DESC'
    ));

    if ($changelogs->have_posts()) :
        while ($changelogs->have_posts()) : $changelogs->the_post();
            $anchor_id = sanitize_title(get_the_title());
            $post_date = get_the_date('Y-m-d'); // Full date
    ?>
            <article id="<?php echo esc_attr($anchor_id); ?>" class="changelog-entry">
                <div class="changelog-header">
                    <div class="title-wrapper">
                        <h2><a href="#<?php echo esc_attr($anchor_id); ?>"><?php the_title(); ?></a></h2>
                    </div>
                </div>

                <div class="changelog-content">
                    <?php the_content(); ?>
                    <p class="changelog-date">
                        与 <?php echo esc_html($post_date); ?> 日期更新
                    </p>
                </div>
            </article>
    <?php
        endwhile;
        wp_reset_postdata();
    endif;
    ?>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.changelog-entry h2 a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    window.history.pushState(null, '', `#${targetId}`);
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        if (window.location.hash) {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                setTimeout(() => {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
        }
    });
</script>

<?php get_footer(); ?>