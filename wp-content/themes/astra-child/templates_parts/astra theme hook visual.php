<?php


add_action( 'astra_header_before', 'my_custom_header_before_content' );
function my_custom_header_before_content() {
    echo '<div style="background-color: #f0f0f0; text-align: center; padding: 10px;">
        <strong>astra_header_before</strong><br>
        这个内容通过 astra_header_before 钩子添加到页面头部之前。
    </div>';
}

add_action( 'astra_content_before', 'my_custom_featured_content' );
function my_custom_featured_content() {
    echo '<div style="background-color: #e0f7fa; padding: 20px; margin-bottom: 20px;">
        <h2 style="color: #0277bd;">astra_content_before</h2>
        <p>这个特色内容区通过 astra_content_before 钩子添加到主内容区域之前。</p>
    </div>';
}

add_action( 'astra_content_after', 'my_custom_content_after' );
function my_custom_content_after() {
    echo '<div style="background-color: #e0f7fa; padding: 20px; margin-top: 20px;">
        <h2 style="color: #0277bd;">astra_content_after</h2>
        <p>这个内容区通过 astra_content_after 钩子添加在主内容区域之后。</p>
    </div>';
}





add_action( 'astra_entry_content_before', 'my_custom_entry_content_info' );
function my_custom_entry_content_info() {
    if ( is_single() || is_page() ) {
        echo '<div style="background-color: #ede8e8; border-left: 4px solid #0277bd; padding: 15px; margin-bottom: 20px;">
            <h4 style="margin: 0;">astra_entry_content_before</h4>
            <p>这个信息框通过 astra_entry_content_before 钩子添加到文章/页面内容之前。</p>
        </div>';
    }
}

add_action( 'astra_entry_content_after', 'my_custom_entry_content_after' );
function my_custom_entry_content_after() {
    if ( is_single() || is_page() ) {
        echo '<div style="background-color: #ede8e8; border-left: 4px solid #0277bd; padding: 15px; margin-bottom: 20px;">
            <h4 style="margin: 0;">astra_entry_content_after</h4>
            <p>这个信息框通过 astra_entry_content_after 钩子添加到文章/页面内容之后。</p>
        </div>';
    }
}


// astra_entry_before 和 astra_entry_after
add_action('astra_entry_before', 'my_custom_entry_before');
add_action('astra_entry_after', 'my_custom_entry_after');

function my_custom_entry_before() {
    if (is_single() || is_page()) {
        echo '<div style="background-color: #9C27B0; color: white; text-align: center; padding: 5px;">
            <strong>钩子: astra_entry_before</strong> - 在文章/页面入口之前
        </div>';
    }
}

function my_custom_entry_after() {
    if (is_single() || is_page()) {
        echo '<div style="background-color: #9C27B0; color: white; text-align: center; padding: 5px;">
            <strong>钩子: astra_entry_after</strong> - 在文章/页面入口之后
        </div>';
    }
}



// astra_entry_top 和 astra_entry_bottom
add_action('astra_entry_top', 'my_custom_entry_top');
add_action('astra_entry_bottom', 'my_custom_entry_bottom');

function my_custom_entry_top() {
    if (is_single() || is_page()) {
        echo '<div style="background-color: #6e9547b8; color: white; text-align: center; padding: 5px;">
            <strong>钩子: astra_entry_top</strong> - 在文章/页面入口顶部
        </div>';
    }
}

function my_custom_entry_bottom() {
    if (is_single() || is_page()) {
        echo '<div style="background-color: #6e9547b8; color: white; text-align: center; padding: 5px;">
            <strong>钩子: astra_entry_bottom</strong> - 在文章/页面入口底部
        </div>';
    }
}

add_action( 'astra_footer_content_top', 'my_custom_footer_content_top' );
function my_custom_footer_content_top() {
    echo '<div style="text-align: center; padding: 20px 0; background-color: #f0f0f0;">
        <h3>astra_footer_content_top</h3>
        <p>这个区域通过 astra_footer_content_top 钩子添加到页脚内容顶部。</p>
    </div>';
}


add_action( 'astra_footer_after', 'my_custom_footer_after' );
function my_custom_footer_after() {
    echo '<div style="background-color: #333; color: white; text-align: center; padding: 10px;">
        <strong>astra_footer_after</strong><br>
        这个内容通过 astra_footer_after 钩子添加到页脚之后。
    </div>';
}


add_action( 'astra_html_before', 'my_custom_html_before' );
function my_custom_html_before() {
    echo '<!-- astra_html_before -->
    <div style="display:none;">这个注释通过 astra_html_before 钩子添加在 HTML 标签之前</div>';
}


// add_action( 'astra_head_top', 'my_custom_head_top' );
// function my_custom_head_top() {
//     echo '<!-- astra_head_top -->
//     <meta name="description" content="这个 meta 标签通过 astra_head_top 钩子添加在 head 标签开始处">';
// }


// add_action( 'astra_head_bottom', 'my_custom_head_bottom' );
// function my_custom_head_bottom() {
//     echo '<!-- astra_head_bottom -->
//     <style>
//         body::before {
//             content: "astra_head_bottom 钩子添加的样式";
//             // position: fixed;
//             top: 0;
//             left: 0;
//             background: #ff0000;
//             color: #ffffff;
//             padding: 5px;
//             z-index: 9999;
//         }
//     </style>';
// }


add_action( 'astra_masthead_top', 'my_custom_masthead_top' );
function my_custom_masthead_top() {
    echo '<div style="background-color: #795548; color: white; text-align: center; padding: 5px;">
        astra_masthead_top - 这个横幅通过 astra_masthead_top 钩子添加在页面头部顶部
    </div>';
}

add_action( 'astra_masthead_bottom', 'my_custom_masthead_bottom' );
function my_custom_masthead_bottom() {
    echo '<div style="background-color: #795548; color: white; text-align: center; padding: 5px;">
        astra_masthead_bottom - 这个内容通过 astra_masthead_bottom 钩子添加在页面头部底部
    </div>';
}

add_action( 'astra_main_header_bar_top', 'my_custom_main_header_bar_top' );
function my_custom_main_header_bar_top() {
    echo '<div style="background-color: #2196F3; color: white; text-align: center; padding: 5px;">
        astra_main_header_bar_top - 这个内容通过 astra_main_header_bar_top 钩子添加在主页头部顶部
    </div>';
}

add_action( 'astra_main_header_bar_bottom', 'my_custom_main_header_bar_bottom' );
function my_custom_main_header_bar_bottom() {
    echo '<div style="background-color: #2196F3; color: white; text-align: center; padding: 5px;">
        astra_main_header_bar_bottom - 这个内容通过 astra_main_header_bar_bottom 钩子添加在主页头部底部
    </div>';
}



add_action( 'astra_masthead_content', 'my_custom_masthead_content' );
function my_custom_masthead_content() {
    echo '<div style="background-color: #9C27B0; color: white; text-align: center; padding: 5px;">
        astra_masthead_content - 这个内容通过 astra_masthead_content 钩子添加在页面头部内容中
    </div>';
}

// astra_primary_content_top; astra_primary_content_bottom
add_action( 'astra_primary_content_top', 'my_custom_primary_content_top' );
function my_custom_primary_content_top() {
    echo '<div style="background-color: #E91E63; color: white; text-align: center; padding: 10px; margin-bottom: 20px;">
        <h3>astra_primary_content_top</h3>
        <p>这个内容区通过 astra_primary_content_top 钩子添加在主要内容区域顶部。</p>
    </div>';
}

add_action('astra_primary_content_bottom', 'my_custom_primary_content_bottom');
function my_custom_primary_content_bottom() {
    echo '<div style="background-color: #E91E63; color: white; text-align: center; padding: 10px; margin-bottom: 20px;">
        <h3>astra_primary_content_bottom</h3>
        <p>这个内容区通过 astra_primary_content_top 钩子添加在主要内容区域的底部。</p>
    </div>';
}



add_action( 'astra_content_top', 'my_custom_content_top' );
function my_custom_content_top() {
    echo '<div style="background-color: #009688; color: white; text-align: center; padding: 10px; margin-top: 20px;">
        <h3>astra_content_top</h3>
        <p>这个内容区通过 astra_content_top 钩子添加在主内容区域顶部。</p>
    </div>';
}

add_action( 'astra_content_bottom', 'my_custom_content_bottom' );
function my_custom_content_bottom() {
    echo '<div style="background-color: #009688; color: white; text-align: center; padding: 10px; margin-top: 20px;">
        <h3>astra_content_bottom</h3>
        <p>这个内容区通过 astra_content_bottom 钩子添加在主内容区域底部。</p>
    </div>';
}






function custom_content_filter($content) {
    $content .= '<p style="color: red; font-size: 22px"><strong>感谢您的阅读！</strong></p>';
    return $content;
}

add_filter('the_content', 'custom_content_filter');


function custom_title_filter($title) {
    if (is_single()) {
        return '特别：' . $title;
    }
    return $title;
}

add_filter('the_title', 'custom_title_filter');




add_action('transition_post_status', 'log_when_published', 10, 3);
function log_when_published($new_status, $old_status, $post) {
    // 只在文章状态从非"publish"变为"publish"时记录
    if ($old_status != 'publish' && $new_status == 'publish') {
        // 确保是文章类型（排除页面等其他内容类型）
        if ($post->post_type == 'post') {
            $post_log = get_stylesheet_directory() . '/post_log.txt';
            $message = get_the_title($post->ID) . ' was just published!';

            $file = fopen($post_log, 'a');
            if ($file) {
                fwrite($file, $message . "\n");
                fclose($file);
            } else {
                error_log("Unable to open or create log file: " . $post_log);
            }
        }
    }
}


?>
