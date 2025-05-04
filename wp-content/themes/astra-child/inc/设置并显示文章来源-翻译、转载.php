<?php
/**
 * 功能名称: 设置并显示文章来源-翻译、转载
 * 
 * 描述: 这个文件实现了WordPress文章来源标签功能，允许编辑在发布文章时选择
 * 文章的来源类型（原创、翻译或转载）。系统会在文章标题前方显示一个彩色标签，
 * 清晰地标识文章的来源类型。
 * 
 * 功能包括:
 * 1. 在文章编辑页面添加"文章来源"选择框
 * 2. 保存文章来源信息到文章元数据
 * 3. 在文章页面标题前显示带颜色的来源标签
 * 4. 为不同来源类型设置不同颜色样式
 * 
* 
 * 使用方法:
 * 1. 将此文件包含到主题的functions.php中
 * 2. 在文章编辑页面右侧找到"文章来源"选择框
 * 3. 选择适当的来源类型（原创、翻译或转载）
 * 4. 更新或发布文章后，文章标题前会显示对应的来源标签
 * 
 * 注意: 此功能设计用于Astra主题，如需在其他主题使用，
 * 可能需要修改钩子（见文件底部的钩子说明）
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {
    exit;
}


// 添加文章来源meta box
function add_article_source_meta_box()
{
    add_meta_box(
        'article_source_box', // ID
        '文章来源', // 标题
        'article_source_callback', // 回调函数
        'post', // 文章类型
        'side', // 位置
        'high' // 优先级
    );
}
add_action('add_meta_boxes', 'add_article_source_meta_box');

// meta box回调函数
function article_source_callback($post)
{
    wp_nonce_field('article_source_save', 'article_source_nonce');
    $source = get_post_meta($post->ID, '_article_source', true);
?>
    <select name="article_source" id="article_source">
        <option value="">请选择来源</option>
        <option value="original" <?php selected($source, 'original'); ?>>原创</option>
        <option value="translated" <?php selected($source, 'translated'); ?>>翻译</option>
        <option value="repost" <?php selected($source, 'repost'); ?>>转载</option>
    </select>
<?php
}

// 保存meta数据
function save_article_source($post_id)
{
    if (!isset($_POST['article_source_nonce'])) return;
    if (!wp_verify_nonce($_POST['article_source_nonce'], 'article_source_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['article_source'])) {
        update_post_meta($post_id, '_article_source', $_POST['article_source']);
    }
}
add_action('save_post', 'save_article_source');




// 添加文章来源标签到标题前
function display_article_source_before_title() {
    if (is_single()) {
        $post_id = get_the_ID();
        $source = get_post_meta($post_id, '_article_source', true);

        $source_text = '';
        $source_class = '';

        switch ($source) {
            case 'original':
                $source_text = '原创';
                $source_class = 'source-original';
                break;
            case 'translated':
                $source_text = '翻译';
                $source_class = 'source-translated';
                break;
            case 'repost':
                $source_text = '转载';
                $source_class = 'source-repost';
                break;
        }

        if ($source) {
            $tag = sprintf('<span class="article-source-tag %s">%s</span> ', $source_class, $source_text);
            echo $tag;
        }
    }
}

// 使用Astra主题的钩子在标题前添加内容
add_action('astra_single_header_before', 'display_article_source_before_title', 15);
// 如果上述钩子不起作用，可以尝试以下备选钩子之一:
// add_action('astra_entry_top_before', 'display_article_source_before_title', 15);
// add_action('astra_entry_header_before', 'display_article_source_before_title', 15);



// 添加CSS样式
function article_source_styles() {
    ?>
    <style>
        .article-source-tag {
            display: inline-block;
            padding: 3px 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
        }
        .source-original {
            background-color: #28a745;
        }
        .source-translated {
            background-color: #17a2b8;
        }
        .source-repost {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
    <?php
}
add_action('wp_head', 'article_source_styles');