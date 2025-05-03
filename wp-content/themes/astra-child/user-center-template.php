
<?php
/*
Template Name: 用户中心
*/

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="user-center-container">
    <?php
    $user = wp_get_current_user();
    $favorites = get_user_meta($user->ID, 'user_favorites', true);
    ?>
    
    <div class="user-info">
        <h2>欢迎, <?php echo esc_html($user->display_name); ?></h2>
        <?php echo get_avatar($user->ID, 96); ?>
    </div>
    
    <div class="favorite-posts">
        <h3>我的收藏</h3>
        <?php
        if (!empty($favorites) && is_array($favorites)) {
            $args = array(
                'post__in' => $favorites,
                'post_type' => 'post',
                'posts_per_page' => -1
            );
            
            $favorite_query = new WP_Query($args);
            
            if ($favorite_query->have_posts()) {
                while ($favorite_query->have_posts()) {
                    $favorite_query->the_post();
                    ?>
                    <div class="favorite-post-item">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('thumbnail'); ?>
                            <?php endif; ?>
                            <h4><?php the_title(); ?></h4>
                        </a>
                        <button class="remove-favorite" data-post-id="<?php echo get_the_ID(); ?>">删除收藏</button>
                    </div>
                    <?php
                }
                wp_reset_postdata();
            }
        } else {
            echo '<p>暂无收藏文章</p>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>