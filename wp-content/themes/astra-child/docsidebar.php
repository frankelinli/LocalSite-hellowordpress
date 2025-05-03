<?php
// 获取当前文章的分类
$categories = get_the_category();

if (!empty($categories)) {
    // 获取当前文章的第一个分类
    $current_category = $categories[0];

    // 获取当前文章的 ID
    $current_post_id = get_the_ID();

    // 获取主分类（最顶层分类）
    $main_category = $current_category;
    while ($main_category->parent != 0) {
        $main_category = get_category($main_category->parent);
    }

    // 获取主分类的文章（不排除当前文章）
    $main_category_args = array(
        'category__in' => array($main_category->term_id), // 主分类
        'post__not_in' => array(),                       // 包括当前文章
        'posts_per_page' => -1,                          // 获取所有文章
    );
    $main_query = new WP_Query($main_category_args);

    // 获取主分类的子分类
    $child_categories = get_categories(array(
        'parent' => $main_category->term_id, // 主分类的子分类
        'hide_empty' => false,               // 包括空子分类
    ));

    echo '<div class="category-sidebar">';

    // 显示主分类标题
    echo '<h3>' . esc_html($main_category->name) . '</h3>';

    // 显示主分类的文章列表
    if ($main_query->have_posts()) {
        echo '<ul class="category-post-list">';
        while ($main_query->have_posts()) {
            $main_query->the_post();
            $post_id = get_the_ID();

            // 判断是否为当前文章
            $is_current_post = ($post_id == $current_post_id);

            echo '<li>';
            echo '<a href="' . get_the_permalink() . '"';
            if ($is_current_post) {
                echo ' style="font-weight: bold; color: #0073aa;"'; // 高亮当前文章
            }
            echo '>';
            // 添加 Font Awesome 图标
            echo '<i class="fa ' . ($is_current_post ? 'fa-bookmark' : 'fa-file-alt') . '" aria-hidden="true"></i> '; // 当前文章显示书签图标，其他文章显示文件图标
            echo get_the_title();
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>主分类暂无文章。</p>';
    }

    // 显示子分类及其文章
    if (!empty($child_categories)) {
        foreach ($child_categories as $child_category) {
            // 检查当前文章是否属于这个子分类
            $is_current_child = ($current_category->term_id == $child_category->term_id);

            // 获取子分类的文章
            $child_args = array(
                'category__in' => array($child_category->term_id), // 子分类
                'posts_per_page' => -1,                           // 获取所有文章
            );
            $child_query = new WP_Query($child_args);

            echo '<div class="child-category">';

            // 子分类标题
            echo '<h4 class="child-category-title" data-child-category="' . $child_category->term_id . '"';
            if ($is_current_child) {
                echo ' style="font-weight: bold; color: #0073aa;"'; // 高亮当前子分类
            }
            echo '>';
            echo esc_html($child_category->name) . ($is_current_child ? ' ▲' : ' ▼'); // 当前子分类展开，其他子分类折叠
            echo '</h4>';

            // 子分类文章列表
            echo '<ul class="child-category-post-list" id="child-category-' . $child_category->term_id . '"';
            if (!$is_current_child) {
                echo ' style="display: none;"'; // 默认隐藏非当前子分类的文章列表
            }
            echo '>';
            if ($child_query->have_posts()) {
                while ($child_query->have_posts()) {
                    $child_query->the_post();
                    $post_id = get_the_ID();

                    // 判断是否为当前文章
                    $is_current_post = ($post_id == $current_post_id);

                    echo '<li>';
                    echo '<a href="' . get_the_permalink() . '"';
                    if ($is_current_post) {
                        echo ' style="font-weight: bold; color: #0073aa;"'; // 高亮当前文章
                    }
                    echo '>';
                    // 添加 Font Awesome 图标
                    echo '<i class="fa ' . ($is_current_post ? 'fa-bookmark' : 'fa-file-alt') . '" aria-hidden="true"></i> '; // 当前文章显示书签图标，其他文章显示文件图标
                    echo get_the_title();
                    echo '</a>';
                    echo '</li>';
                }
            } else {
                echo '<p>此子分类暂无文章。</p>';
            }
            echo '</ul>';

            echo '</div>';
        }
    }

    echo '</div>'; // 主分类容器结束
    wp_reset_postdata();
} else {
    echo '<p>当前文章没有分类。</p>';
}
?>