<?php
/**
 * CSR Wiki Payment System - Initialization
 * Version: 1.0.0
 */

// 确保直接访问时退出
if (!defined('ABSPATH')) {
    exit;
}

// 定义常量
define('CSRWIKI_PAYMENT_VERSION', '1.0.0');
define('CSRWIKI_PAYMENT_AMOUNT', 99); // 支付金额
define('CSRWIKI_MEMBER_ROLE', 'csrwiki_member');

/**
 * 初始化函数
 */
function csrwiki_payment_system_init() {
    // 创建会员角色
    add_role(
        CSRWIKI_MEMBER_ROLE,
        '会员',
        array(
            'read' => true,
            'read_member_content' => true
        )
    );

    // 创建支付页面
    $payment_page = get_page_by_path('member-payment');
    if (!$payment_page) {
        wp_insert_post(array(
            'post_title'    => '会员支付',
            'post_content'  => '[csrwiki_payment_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'member-payment'
        ));
    }

    // 创建支付状态
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'csrwiki_payments';

    // 检查表是否存在
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            order_number varchar(32) NOT NULL,
            amount decimal(10,2) NOT NULL,
            payment_time datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) NOT NULL DEFAULT 'pending',
            verified_by bigint(20) DEFAULT NULL,
            verified_time datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY order_number (order_number),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'csrwiki_payment_system_init');

/**
 * 会员内容保护短代码
 */
function csrwiki_member_content_shortcode($atts, $content = null) {
    if (!is_user_logged_in()) {
        return csrwiki_get_locked_content_html();
    }

    $user_id = get_current_user_id();
    $payment_status = csrwiki_get_user_payment_status($user_id);

    switch($payment_status) {
        case 'verified':
            return '<div class="member-content">' . do_shortcode($content) . '</div>';
        case 'pending':
            return '<div class="member-content-pending">
                <p class="member-notice">⌛ 您的会员申请正在审核中，请耐心等待</p>
            </div>';
        case 'rejected':
            return '<div class="member-content-rejected">
                <p class="member-notice">❌ 您的会员申请未通过，如有疑问请联系管理员</p>
            </div>';
        default:
            return csrwiki_get_locked_content_html();
    }
}
add_shortcode('member', 'csrwiki_member_content_shortcode');

/**
 * 获取用户支付状态
 */
function csrwiki_get_user_payment_status($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csrwiki_payments';
    
    $status = $wpdb->get_var($wpdb->prepare(
        "SELECT status FROM $table_name 
        WHERE user_id = %d 
        ORDER BY payment_time DESC 
        LIMIT 1",
        $user_id
    ));

    return $status ? $status : 'none';
}

/**
 * 获取锁定内容的HTML
 */
function csrwiki_get_locked_content_html() {
    return '<div class="member-content-locked">
        <p class="member-notice">🔒 此处内容仅会员可见</p>
        <a href="' . esc_url(site_url('/member-payment/')) . '" 
           class="button member-button">开通会员查看完整内容</a>
    </div>';
}

/**
 * 加载支付系统文件
 */
function csrwiki_load_payment_system() {
    $base_path = get_stylesheet_directory() . '/payment-system/';
    
    require_once $base_path . 'form.php';
    require_once $base_path . 'admin.php';
    require_once $base_path . 'styles.php';
    require_once $base_path . 'helpers.php';
}
add_action('after_setup_theme', 'csrwiki_load_payment_system');

/**
 * 检查会员过期
 */
function csrwiki_check_member_expiration() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csrwiki_payments';
    
    // 获取所有过期会员
    $expired_members = $wpdb->get_results(
        "SELECT user_id FROM $table_name 
        WHERE status = 'verified' 
        AND payment_time < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
    );

    foreach($expired_members as $member) {
        $user = new WP_User($member->user_id);
        $user->remove_role(CSRWIKI_MEMBER_ROLE);
        
        // 发送过期通知
        $subject = '会员已过期 - ' . get_bloginfo('name');
        $message = "您的会员已过期，如需继续使用会员功能，请重新开通会员。";
        wp_mail($user->user_email, $subject, $message);
    }
}
add_action('wp_scheduled_events', 'csrwiki_check_member_expiration');

// 添加定时任务
if (!wp_next_scheduled('wp_scheduled_events')) {
    wp_schedule_event(time(), 'daily', 'wp_scheduled_events');
}