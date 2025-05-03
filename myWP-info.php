<?php
/**
 * WordPress站点统计信息
 * 
 * 此页面显示WordPress站点的全部统计信息，包括文章、页面、评论、用户、分类等数据
 */

// 加载WordPress环境
require_once('wp-load.php');

// 安全检查：只允许管理员访问
if (!current_user_can('manage_options')) {
    wp_die('抱歉，您没有权限访问此页面。');
}

// 获取WordPress全局变量
global $wpdb;

// 站点基本信息
$site_title = get_bloginfo('name');
$site_description = get_bloginfo('description');
$site_url = get_bloginfo('url');

// 获取当前活跃主题
$current_theme = wp_get_theme();
$theme_name = $current_theme->get('Name');
$theme_version = $current_theme->get('Version');

// 获取统计数据函数
function get_wp_stats() {
    global $wpdb;
    $stats = array();
    
    // 文章统计
    $stats['posts'] = array(
        'total' => wp_count_posts()->publish,
        'draft' => wp_count_posts()->draft,
        'pending' => wp_count_posts()->pending,
        'private' => wp_count_posts()->private,
        'future' => wp_count_posts()->future,
        'trash' => wp_count_posts()->trash,
    );
    
    // 页面统计
    $stats['pages'] = array(
        'total' => wp_count_posts('page')->publish,
        'draft' => wp_count_posts('page')->draft,
        'private' => wp_count_posts('page')->private,
        'trash' => wp_count_posts('page')->trash,
    );
    
    // 获取自定义文章类型
    $custom_post_types = get_post_types(array('_builtin' => false, 'public' => true), 'objects');
    $stats['custom_post_types'] = array();
    foreach ($custom_post_types as $post_type) {
        $count = wp_count_posts($post_type->name);
        $stats['custom_post_types'][$post_type->name] = array(
            'name' => $post_type->labels->name,
            'total' => $count->publish,
            'draft' => $count->draft,
        );
    }
    
    // 评论统计
    $comment_count = get_comment_count();
    $stats['comments'] = array(
        'total' => $comment_count['total_comments'],
        'approved' => $comment_count['approved'],
        'pending' => $comment_count['awaiting_moderation'],
        'spam' => $comment_count['spam'],
        'trash' => $comment_count['trash'],
    );
    
    // 用户统计
    $stats['users'] = array(
        'total' => count_users()['total_users'],
        'roles' => count_users()['avail_roles'],
    );
    
    // 分类统计
    $stats['taxonomies'] = array(
        'categories' => wp_count_terms('category'),
        'tags' => wp_count_terms('post_tag'),
    );
    
    // 获取自定义分类法
    $custom_taxonomies = get_taxonomies(array('_builtin' => false, 'public' => true), 'objects');
    $stats['custom_taxonomies'] = array();
    foreach ($custom_taxonomies as $taxonomy) {
        $stats['custom_taxonomies'][$taxonomy->name] = array(
            'name' => $taxonomy->labels->name,
            'count' => wp_count_terms($taxonomy->name),
        );
    }
    
    // 媒体统计
    $stats['media'] = array(
        'total' => wp_count_posts('attachment')->inherit,
    );
    
    // 插件统计
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', array());
    $stats['plugins'] = array(
        'total' => count($all_plugins),
        'active' => count($active_plugins),
        'inactive' => count($all_plugins) - count($active_plugins),
    );
    
    // WordPress版本
    global $wp_version;
    $stats['wp_version'] = $wp_version;
    
    // PHP版本
    $stats['php_version'] = phpversion();
    
    // MySQL版本
    $stats['mysql_version'] = $wpdb->get_var("SELECT VERSION()");
    
    // 数据库大小
    $db_size = 0;
    $db_size_results = $wpdb->get_results("SELECT table_name, table_rows, data_length, index_length 
                                          FROM information_schema.TABLES 
                                          WHERE table_schema = '" . DB_NAME . "'");
    foreach ($db_size_results as $result) {
        // 确保属性存在后再使用，防止出现undefined property错误
        $data_length = isset($result->data_length) ? $result->data_length : 0;
        $index_length = isset($result->index_length) ? $result->index_length : 0;
        $db_size += $data_length + $index_length;
    }
    $stats['db_size'] = $db_size;
    $stats['db_tables'] = count($db_size_results);
    
    // 上传目录大小
    $upload_dir = wp_upload_dir();
    $stats['upload_dir'] = $upload_dir['basedir'];
    
    // 文章发布时间分布
    $posts_by_date = $wpdb->get_results("
        SELECT YEAR(post_date) AS year, MONTH(post_date) AS month, COUNT(*) as count
        FROM {$wpdb->posts}
        WHERE post_type = 'post' AND post_status = 'publish'
        GROUP BY YEAR(post_date), MONTH(post_date)
        ORDER BY post_date DESC
        LIMIT 12
    ");
    $stats['posts_by_date'] = $posts_by_date;
    
    // 最活跃的作者
    $top_authors = $wpdb->get_results("
        SELECT u.display_name, COUNT(*) as post_count 
        FROM {$wpdb->posts} p
        JOIN {$wpdb->users} u ON p.post_author = u.ID
        WHERE p.post_type = 'post' AND p.post_status = 'publish'
        GROUP BY p.post_author
        ORDER BY post_count DESC
        LIMIT 10
    ");
    $stats['top_authors'] = $top_authors;
    
    // 最受欢迎的文章（评论最多）
    $popular_posts = $wpdb->get_results("
        SELECT p.ID, p.post_title, COUNT(c.comment_ID) AS comment_count, p.comment_count as total_comment_count
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->comments} c ON p.ID = c.comment_post_ID AND c.comment_approved = '1'
        WHERE p.post_type = 'post' AND p.post_status = 'publish'
        GROUP BY p.ID
        ORDER BY total_comment_count DESC
        LIMIT 10
    ");
    $stats['popular_posts'] = $popular_posts;
    
    // 最近发布的文章
    $recent_posts = $wpdb->get_results("
        SELECT ID, post_title, post_date
        FROM {$wpdb->posts}
        WHERE post_type = 'post' AND post_status = 'publish'
        ORDER BY post_date DESC
        LIMIT 10
    ");
    $stats['recent_posts'] = $recent_posts;
    
    // 评论最多的用户
    $top_commenters = $wpdb->get_results("
        SELECT comment_author, comment_author_email, COUNT(*) AS comment_count
        FROM {$wpdb->comments}
        WHERE comment_approved = '1'
        GROUP BY comment_author_email
        ORDER BY comment_count DESC
        LIMIT 10
    ");
    $stats['top_commenters'] = $top_commenters;
    
    return $stats;
}

// 获取全部统计数据
$wp_stats = get_wp_stats();

// 辅助函数：格式化文件大小
function format_size($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

// 辅助函数：获取文章阅读时间估计
function get_reading_time($post_id) {
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // 假设平均阅读速度为每分钟200字
    return $reading_time;
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress 站点统计信息 - <?php echo esc_html($site_title); ?></title>
    <?php wp_head(); ?>
    <style>
        /* 基本样式 */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #0073aa;
            font-size: 32px;
            margin: 0 0 10px;
        }
        h2 {
            color: #0073aa;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 40px;
        }
        h3 {
            color: #23282d;
        }
        .site-description {
            color: #666;
            font-style: italic;
        }
        .stat-card {
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-highlight {
            background-color: #f8f9fa;
            border-left: 4px solid #0073aa;
            padding: 15px;
            margin-bottom: 20px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .chart-container {
            margin: 20px 0;
            height: 300px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #0073aa;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: none;
        }
        /* 导航样式 */
        .nav-container {
            background: #23282d;
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-container nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .nav-container ul {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .nav-container li {
            margin: 0;
        }
        .nav-container a {
            display: block;
            color: #eee;
            padding: 15px;
            text-decoration: none;
            font-size: 14px;
        }
        .nav-container a:hover {
            background-color: #32373c;
        }
        /* 进度条样式 */
        .progress-container {
            margin-bottom: 20px;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 4px;
            height: 20px;
            overflow: hidden;
        }
        .progress {
            background-color: #0073aa;
            height: 100%;
            text-align: center;
            color: white;
            line-height: 20px;
            font-size: 12px;
        }
        /* 信息卡片 */
        .info-card {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .info-primary {
            background-color: #cce5ff;
            color: #004085;
        }
        .info-success {
            background-color: #d4edda;
            color: #155724;
        }
        .info-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .info-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a href="#" id="back-to-top" class="back-to-top">↑</a>

<header>
    <div class="container">
        <h1><?php echo esc_html($site_title); ?> - 站点统计信息</h1>
        <p class="site-description"><?php echo esc_html($site_description); ?></p>
    </div>
</header>

<div class="nav-container">
    <nav>
        <ul>
            <li><a href="#overview">总览</a></li>
            <li><a href="#content">内容统计</a></li>
            <li><a href="#users">用户统计</a></li>
            <li><a href="#comments">评论统计</a></li>
            <li><a href="#taxonomy">分类统计</a></li>
            <li><a href="#media">媒体统计</a></li>
            <li><a href="#plugins">插件统计</a></li>
            <li><a href="#system">系统信息</a></li>
            <li><a href="#top-content">热门内容</a></li>
        </ul>
    </nav>
</div>

<div class="container">
    <!-- 总览部分 -->
    <section id="overview">
        <h2>站点总览</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">文章数量</div>
                <div class="stat-number"><?php echo number_format($wp_stats['posts']['total']); ?></div>
                <div>已发布文章总数</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">页面数量</div>
                <div class="stat-number"><?php echo number_format($wp_stats['pages']['total']); ?></div>
                <div>已发布页面总数</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">评论数量</div>
                <div class="stat-number"><?php echo number_format($wp_stats['comments']['approved']); ?></div>
                <div>已批准评论总数</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">用户数量</div>
                <div class="stat-number"><?php echo number_format($wp_stats['users']['total']); ?></div>
                <div>注册用户总数</div>
            </div>
        </div>
        
        <div class="stat-highlight">
            <p><strong>当前主题:</strong> <?php echo esc_html($theme_name); ?> (版本 <?php echo esc_html($theme_version); ?>)</p>
            <p><strong>WordPress版本:</strong> <?php echo esc_html($wp_stats['wp_version']); ?></p>
            <p><strong>PHP版本:</strong> <?php echo esc_html($wp_stats['php_version']); ?></p>
            <p><strong>MySQL版本:</strong> <?php echo esc_html($wp_stats['mysql_version']); ?></p>
            <p><strong>数据库大小:</strong> <?php echo format_size($wp_stats['db_size']); ?> (<?php echo $wp_stats['db_tables']; ?> 个表)</p>
        </div>
    </section>

    <!-- 内容统计部分 -->
    <section id="content">
        <h2>内容统计</h2>
        
        <h3>文章状态分布</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>状态</th>
                    <th>数量</th>
                    <th>百分比</th>
                </tr>
                <tr>
                    <td>已发布</td>
                    <td><?php echo number_format($wp_stats['posts']['total']); ?></td>
                    <td><?php 
                        $total_posts = $wp_stats['posts']['total'] + $wp_stats['posts']['draft'] + 
                                      $wp_stats['posts']['pending'] + $wp_stats['posts']['private'] + 
                                      $wp_stats['posts']['future'] + $wp_stats['posts']['trash'];
                        echo ($total_posts > 0) ? round(($wp_stats['posts']['total'] / $total_posts) * 100, 1) . '%' : '0%';
                    ?></td>
                </tr>
                <tr>
                    <td>草稿</td>
                    <td><?php echo number_format($wp_stats['posts']['draft']); ?></td>
                    <td><?php echo ($total_posts > 0) ? round(($wp_stats['posts']['draft'] / $total_posts) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>等待审核</td>
                    <td><?php echo number_format($wp_stats['posts']['pending']); ?></td>
                    <td><?php echo ($total_posts > 0) ? round(($wp_stats['posts']['pending'] / $total_posts) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>私密</td>
                    <td><?php echo number_format($wp_stats['posts']['private']); ?></td>
                    <td><?php echo ($total_posts > 0) ? round(($wp_stats['posts']['private'] / $total_posts) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>定时发布</td>
                    <td><?php echo number_format($wp_stats['posts']['future']); ?></td>
                    <td><?php echo ($total_posts > 0) ? round(($wp_stats['posts']['future'] / $total_posts) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>回收站</td>
                    <td><?php echo number_format($wp_stats['posts']['trash']); ?></td>
                    <td><?php echo ($total_posts > 0) ? round(($wp_stats['posts']['trash'] / $total_posts) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <th>总计</th>
                    <th><?php echo number_format($total_posts); ?></th>
                    <th>100%</th>
                </tr>
            </table>
        </div>
        
        <h3>页面状态分布</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>状态</th>
                    <th>数量</th>
                    <th>百分比</th>
                </tr>
                <tr>
                    <td>已发布</td>
                    <td><?php echo number_format($wp_stats['pages']['total']); ?></td>
                    <td><?php 
                        $total_pages = $wp_stats['pages']['total'] + $wp_stats['pages']['draft'] + 
                                      $wp_stats['pages']['private'] + $wp_stats['pages']['trash'];
                        echo ($total_pages > 0) ? round(($wp_stats['pages']['total'] / $total_pages) * 100, 1) . '%' : '0%';
                    ?></td>
                </tr>
                <tr>
                    <td>草稿</td>
                    <td><?php echo number_format($wp_stats['pages']['draft']); ?></td>
                    <td><?php echo ($total_pages > 0) ? round(($wp_stats['pages']['draft'] / $total_pages) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>私密</td>
                    <td><?php echo number_format($wp_stats['pages']['private']); ?></td>
                    <td><?php echo ($total_pages > 0) ? round(($wp_stats['pages']['private'] / $total_pages) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <td>回收站</td>
                    <td><?php echo number_format($wp_stats['pages']['trash']); ?></td>
                    <td><?php echo ($total_pages > 0) ? round(($wp_stats['pages']['trash'] / $total_pages) * 100, 1) . '%' : '0%'; ?></td>
                </tr>
                <tr>
                    <th>总计</th>
                    <th><?php echo number_format($total_pages); ?></th>
                    <th>100%</th>
                </tr>
            </table>
        </div>
        
        <?php if (!empty($wp_stats['custom_post_types'])): ?>
        <h3>自定义文章类型统计</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>类型名称</th>
                    <th>已发布</th>
                    <th>草稿</th>
                    <th>总计</th>
                </tr>
                <?php foreach ($wp_stats['custom_post_types'] as $type): ?>
                <tr>
                    <td><?php echo esc_html($type['name']); ?></td>
                    <td><?php echo number_format($type['total']); ?></td>
                    <td><?php echo number_format($type['draft']); ?></td>
                    <td><?php echo number_format($type['total'] + $type['draft']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <h3>文章发布时间分布（最近12个月）</h3>
        <div class="stat-card">
            <?php if (!empty($wp_stats['posts_by_date'])): ?>
            <table>
                <tr>
                    <th>年月</th>
                    <th>文章数量</th>
                    <th>比例</th>
                </tr>
                <?php 
                $total_in_period = 0;
                foreach ($wp_stats['posts_by_date'] as $date_stat) {
                    $total_in_period += $date_stat->count;
                }
                
                foreach ($wp_stats['posts_by_date'] as $date_stat): 
                    $month_name = date_i18n('Y年n月', strtotime($date_stat->year . '-' . $date_stat->month . '-01'));
                    $percentage = ($total_in_period > 0) ? round(($date_stat->count / $total_in_period) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo esc_html($month_name); ?></td>
                    <td><?php echo number_format($date_stat->count); ?></td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"><?php echo $percentage; ?>%</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>没有找到文章发布时间数据。</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- 用户统计部分 -->
    <section id="users">
        <h2>用户统计</h2>
        
        <h3>用户角色分布</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>用户角色</th>
                    <th>用户数量</th>
                    <th>百分比</th>
                </tr>
                <?php 
                $roles = $wp_stats['users']['roles'];
                foreach ($roles as $role => $count): 
                    $role_names = array(
                        'administrator' => '管理员',
                        'editor' => '编辑',
                        'author' => '作者',
                        'contributor' => '贡献者',
                        'subscriber' => '订阅者'
                    );
                    $role_display = isset($role_names[$role]) ? $role_names[$role] : $role;
                    $percentage = ($wp_stats['users']['total'] > 0) ? round(($count / $wp_stats['users']['total']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo esc_html($role_display); ?></td>
                    <td><?php echo number_format($count); ?></td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"><?php echo $percentage; ?>%</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <th>总计</th>
                    <th><?php echo number_format($wp_stats['users']['total']); ?></th>
                    <th>100%</th>
                </tr>
            </table>
        </div>
        
        <h3>最活跃的作者（按发布文章数）</h3>
        <div class="stat-card">
            <?php if (!empty($wp_stats['top_authors'])): ?>
            <table>
                <tr>
                    <th>排名</th>
                    <th>作者</th>
                    <th>文章数量</th>
                    <th>占比</th>
                </tr>
                <?php 
                $rank = 1;
                foreach ($wp_stats['top_authors'] as $author): 
                    $percentage = ($wp_stats['posts']['total'] > 0) ? round(($author->post_count / $wp_stats['posts']['total']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo esc_html($author->display_name); ?></td>
                    <td><?php echo number_format($author->post_count); ?></td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"><?php echo $percentage; ?>%</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>没有发现活跃作者数据。</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- 评论统计部分 -->
    <section id="comments">
        <h2>评论统计</h2>
        
        <h3>评论状态分布</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>状态</th>
                    <th>数量</th>
                    <th>百分比</th>
                </tr>
                <tr>
                    <td>已批准</td>
                    <td><?php echo number_format($wp_stats['comments']['approved']); ?></td>
                    <td><?php 
                        echo ($wp_stats['comments']['total'] > 0) ? 
                             round(($wp_stats['comments']['approved'] / $wp_stats['comments']['total']) * 100, 1) . '%' : 
                             '0%';
                    ?></td>
                </tr>
                <tr>
                    <td>等待审核</td>
                    <td><?php echo number_format($wp_stats['comments']['pending']); ?></td>
                    <td><?php 
                        echo ($wp_stats['comments']['total'] > 0) ? 
                             round(($wp_stats['comments']['pending'] / $wp_stats['comments']['total']) * 100, 1) . '%' : 
                             '0%';
                    ?></td>
                </tr>
                <tr>
                    <td>垃圾评论</td>
                    <td><?php echo number_format($wp_stats['comments']['spam']); ?></td>
                    <td><?php 
                        echo ($wp_stats['comments']['total'] > 0) ? 
                             round(($wp_stats['comments']['spam'] / $wp_stats['comments']['total']) * 100, 1) . '%' : 
                             '0%';
                    ?></td>
                </tr>
                <tr>
                    <td>回收站</td>
                    <td><?php echo number_format($wp_stats['comments']['trash']); ?></td>
                    <td><?php 
                        echo ($wp_stats['comments']['total'] > 0) ? 
                             round(($wp_stats['comments']['trash'] / $wp_stats['comments']['total']) * 100, 1) . '%' : 
                             '0%';
                    ?></td>
                </tr>
                <tr>
                    <th>总计</th>
                    <th><?php echo number_format($wp_stats['comments']['total']); ?></th>
                    <th>100%</th>
                </tr>
            </table>
        </div>
        
        <h3>评论最多的用户</h3>
        <div class="stat-card">
            <?php if (!empty($wp_stats['top_commenters'])): ?>
            <table>
                <tr>
                    <th>排名</th>
                    <th>用户名</th>
                    <th>评论数量</th>
                </tr>
                <?php 
                $rank = 1;
                foreach ($wp_stats['top_commenters'] as $commenter): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo esc_html($commenter->comment_author); ?></td>
                    <td><?php echo number_format($commenter->comment_count); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>没有找到评论用户数据。</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- 分类统计部分 -->
    <section id="taxonomy">
        <h2>分类统计</h2>
        
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">分类目录</div>
                <div class="stat-number"><?php echo number_format($wp_stats['taxonomies']['categories']); ?></div>
                <div>分类目录总数</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">标签</div>
                <div class="stat-number"><?php echo number_format($wp_stats['taxonomies']['tags']); ?></div>
                <div>标签总数</div>
            </div>
        </div>
        
        <?php
        // 获取热门分类
        $popular_categories = get_categories(array(
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => 10
        ));
        
        if (!empty($popular_categories)): ?>
        <h3>文章最多的分类</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>排名</th>
                    <th>分类名称</th>
                    <th>文章数量</th>
                </tr>
                <?php 
                $rank = 1;
                foreach ($popular_categories as $category): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo esc_html($category->name); ?></td>
                    <td><?php echo number_format($category->count); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <?php
        // 获取热门标签
        $popular_tags = get_tags(array(
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => 10
        ));
        
        if (!empty($popular_tags)): ?>
        <h3>使用最多的标签</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>排名</th>
                    <th>标签名称</th>
                    <th>文章数量</th>
                </tr>
                <?php 
                $rank = 1;
                foreach ($popular_tags as $tag): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo esc_html($tag->name); ?></td>
                    <td><?php echo number_format($tag->count); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($wp_stats['custom_taxonomies'])): ?>
        <h3>自定义分类法统计</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>分类法名称</th>
                    <th>项目数量</th>
                </tr>
                <?php foreach ($wp_stats['custom_taxonomies'] as $taxonomy): ?>
                <tr>
                    <td><?php echo esc_html($taxonomy['name']); ?></td>
                    <td><?php echo number_format($taxonomy['count']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- 媒体统计部分 -->
    <section id="media">
        <h2>媒体统计</h2>
        
        <div class="stat-card">
            <div class="stat-label">媒体文件</div>
            <div class="stat-number"><?php echo number_format($wp_stats['media']['total']); ?></div>
            <div>媒体库中的附件总数</div>
        </div>
        
        <?php
        // 获取媒体文件类型分布
        $media_types = $wpdb->get_results("
            SELECT 
                SUBSTRING_INDEX(meta_value, '/', 1) AS type,
                COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '_wp_attachment_metadata' 
            AND p.post_type = 'attachment'
            GROUP BY type
            ORDER BY count DESC
        ");
        
        if (!empty($media_types)): ?>
        <h3>媒体类型分布</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>媒体类型</th>
                    <th>文件数量</th>
                    <th>占比</th>
                </tr>
                <?php 
                foreach ($media_types as $type): 
                    $percentage = ($wp_stats['media']['total'] > 0) ? 
                                  round(($type->count / $wp_stats['media']['total']) * 100, 1) : 0;
                    
                    // 媒体类型名称转换
                    $type_names = array(
                        'image' => '图像',
                        'video' => '视频',
                        'audio' => '音频',
                        'application' => '文档/应用',
                        'text' => '文本'
                    );
                    $type_display = isset($type_names[$type->type]) ? $type_names[$type->type] : $type->type;
                ?>
                <tr>
                    <td><?php echo esc_html($type_display); ?></td>
                    <td><?php echo number_format($type->count); ?></td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"><?php echo $percentage; ?>%</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <?php
        // 上传目录大小信息，如果函数可用
        if (function_exists('get_dirsize')): 
            $upload_dir = wp_upload_dir();
            $upload_dir_size = get_dirsize($upload_dir['basedir']);
        ?>
        <h3>上传目录大小</h3>
        <div class="stat-card">
            <div class="stat-label">总大小</div>
            <div class="stat-number"><?php echo format_size($upload_dir_size); ?></div>
            <p>上传目录路径: <?php echo esc_html($upload_dir['basedir']); ?></p>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- 插件统计部分 -->
    <section id="plugins">
        <h2>插件统计</h2>
        
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">总插件数</div>
                <div class="stat-number"><?php echo number_format($wp_stats['plugins']['total']); ?></div>
                <div>已安装的插件总数</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">已激活插件</div>
                <div class="stat-number"><?php echo number_format($wp_stats['plugins']['active']); ?></div>
                <div>当前激活的插件数量</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">未激活插件</div>
                <div class="stat-number"><?php echo number_format($wp_stats['plugins']['inactive']); ?></div>
                <div>当前未激活的插件数量</div>
            </div>
        </div>
        
        <?php
        // 获取已激活的插件列表
        $active_plugins = get_option('active_plugins');
        if (!empty($active_plugins)): ?>
        <h3>已激活的插件</h3>
        <div class="stat-card">
            <table>
                <tr>
                    <th>插件名称</th>
                    <th>版本</th>
                    <th>作者</th>
                </tr>
                <?php 
                foreach ($active_plugins as $plugin_path): 
                    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
                    if (!empty($plugin_data['Name'])):
                ?>
                <tr>
                    <td><?php echo esc_html($plugin_data['Name']); ?></td>
                    <td><?php echo esc_html($plugin_data['Version']); ?></td>
                    <td><?php echo wp_kses_post($plugin_data['Author']); ?></td>
                </tr>
                <?php 
                    endif;
                endforeach;
                ?>
            </table>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- 系统信息部分 -->
    <section id="system">
        <h2>系统信息</h2>
        
        <div class="stat-card">
            <h3>WordPress环境</h3>
            <table>
                <tr>
                    <th>参数</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td>WordPress版本</td>
                    <td><?php echo esc_html($wp_stats['wp_version']); ?></td>
                </tr>
                <tr>
                    <td>站点URL</td>
                    <td><?php echo esc_url($site_url); ?></td>
                </tr>
                <tr>
                    <td>主题</td>
                    <td><?php echo esc_html($theme_name); ?> v<?php echo esc_html($theme_version); ?></td>
                </tr>
                <tr>
                    <td>多站点</td>
                    <td><?php echo is_multisite() ? '是' : '否'; ?></td>
                </tr>
                <tr>
                    <td>调试模式</td>
                    <td><?php echo defined('WP_DEBUG') && WP_DEBUG ? '开启' : '关闭'; ?></td>
                </tr>
                <tr>
                    <td>WP 内存限制</td>
                    <td><?php echo defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : '默认'; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="stat-card">
            <h3>服务器环境</h3>
            <table>
                <tr>
                    <th>参数</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td>PHP 版本</td>
                    <td><?php echo esc_html($wp_stats['php_version']); ?></td>
                </tr>
                <tr>
                    <td>MySQL 版本</td>
                    <td><?php echo esc_html($wp_stats['mysql_version']); ?></td>
                </tr>
                <tr>
                    <td>服务器软件</td>
                    <td><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? esc_html($_SERVER['SERVER_SOFTWARE']) : '未知'; ?></td>
                </tr>
                <tr>
                    <td>操作系统</td>
                    <td><?php echo PHP_OS; ?></td>
                </tr>
                <tr>
                    <td>PHP 内存限制</td>
                    <td><?php echo ini_get('memory_limit'); ?></td>
                </tr>
                <tr>
                    <td>最大执行时间</td>
                    <td><?php echo ini_get('max_execution_time'); ?> 秒</td>
                </tr>
                <tr>
                    <td>最大上传大小</td>
                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                </tr>
                <tr>
                    <td>PHP POST 最大大小</td>
                    <td><?php echo ini_get('post_max_size'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="stat-card">
            <h3>数据库信息</h3>
            <table>
                <tr>
                    <th>参数</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td>数据库名称</td>
                    <td><?php echo defined('DB_NAME') ? esc_html(DB_NAME) : '未知'; ?></td>
                </tr>
                <tr>
                    <td>数据库主机</td>
                    <td><?php echo defined('DB_HOST') ? esc_html(DB_HOST) : '未知'; ?></td>
                </tr>
                <tr>
                    <td>表前缀</td>
                    <td><?php echo esc_html($wpdb->prefix); ?></td>
                </tr>
                <tr>
                    <td>数据库表数量</td>
                    <td><?php echo number_format($wp_stats['db_tables']); ?></td>
                </tr>
                <tr>
                    <td>数据库大小</td>
                    <td><?php echo format_size($wp_stats['db_size']); ?></td>
                </tr>
                <tr>
                    <td>数据库字符集</td>
                    <td><?php echo esc_html($wpdb->charset); ?></td>
                </tr>
            </table>
        </div>
    </section>
    
    <!-- 热门内容部分 -->
    <section id="top-content">
        <h2>热门内容</h2>
        
        <h3>评论最多的文章</h3>
        <div class="stat-card">
            <?php if (!empty($wp_stats['popular_posts'])): ?>
            <table>
                <tr>
                    <th>排名</th>
                    <th>文章标题</th>
                    <th>评论数</th>
                    <th>链接</th>
                </tr>
                <?php 
                $rank = 1;
                foreach ($wp_stats['popular_posts'] as $post): ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td><?php echo esc_html($post->post_title); ?></td>
                    <td><?php echo number_format($post->total_comment_count); ?></td>
                    <td><a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank">查看</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>没有找到热门文章数据。</p>
            <?php endif; ?>
        </div>
        
        <h3>最近发布的文章</h3>
        <div class="stat-card">
            <?php if (!empty($wp_stats['recent_posts'])): ?>
            <table>
                <tr>
                    <th>文章标题</th>
                    <th>发布日期</th>
                    <th>链接</th>
                </tr>
                <?php foreach ($wp_stats['recent_posts'] as $post): ?>
                <tr>
                    <td><?php echo esc_html($post->post_title); ?></td>
                    <td><?php echo date_i18n('Y年n月j日', strtotime($post->post_date)); ?></td>
                    <td><a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank">查看</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>没有找到最近文章数据。</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- 页脚部分 -->
    <div class="footer">
        <p>统计生成时间: <?php echo date_i18n('Y年n月j日 H:i:s'); ?></p>
        <p><a href="<?php echo esc_url(home_url()); ?>">返回网站首页</a></p>
    </div>
</div>

<?php wp_footer(); ?>
<script>
    // 返回顶部按钮脚本
    document.addEventListener('DOMContentLoaded', function() {
        var backToTopButton = document.getElementById('back-to-top');
        
        // 当用户滚动超过300px时显示按钮
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        // 点击按钮返回顶部
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    });
</script>
</body>
</html>