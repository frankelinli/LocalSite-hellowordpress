<?php


//面包屑
// 添加到functions.php
function add_custom_breadcrumb()
{
    // 只在单篇文章页面显示
    if (!is_single()) {
        return;
    }

    // 获取当前文章的分类
    $categories = get_the_category();
    $category = !empty($categories) ? $categories[0] : null;

    // 构建HTML
    $html = '<div class="custom-breadcrumb">';

    // 首页链接
    $html .= '<a href="' . home_url() . '">首页</a>';
    $html .= '<span class="separator"> > </span>';

    // 分类链接
    if ($category) {
        $html .= '<a href="' . get_category_link($category->term_id) . '">';
        $html .= $category->name;
        $html .= '</a>';
        $html .= '<span class="separator"> > </span>';
    }

    // 当前文章标题
    $html .= '<span class="current">' . get_the_title() . '</span>';

    $html .= '</div>';

    // 输出HTML
    echo $html;
}

// 添加样式
function add_breadcrumb_styles()
{
?>
    <style>
        .custom-breadcrumb {
            padding: 10px 0;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }

        .custom-breadcrumb a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .custom-breadcrumb a:hover {
            color: #9800ff;
        }

        .custom-breadcrumb .separator {
            margin: 0 8px;
            color: #999;
        }

        .custom-breadcrumb .current {
            color: #333;
            font-weight: 500;
        }
    </style>
<?php
}

// 使用Astra钩子添加面包屑
add_action('astra_content_before', 'add_custom_breadcrumb');
// 添加样式
add_action('astra_head_top', 'add_breadcrumb_styles');