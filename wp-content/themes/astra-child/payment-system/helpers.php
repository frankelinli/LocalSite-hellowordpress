<?php
/**
 * CSR Wiki Payment System - Helper Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 生成随机订单号
 */
function csrwiki_generate_order_number() {
    return date('YmdHis') . mt_rand(1000, 9999);
}

/**
 * 格式化金额
 */
function csrwiki_format_amount($amount) {
    return number_format($amount, 2, '.', ',');
}

/**
 * 获取支付状态徽章HTML
 */
function csrwiki_get_status_badge($status) {
    $status_classes = [
        'pending' => 'status-pending',
        'verified' => 'status-verified',
        'rejected' => 'status-rejected'
    ];
    
    $status_texts = [
        'pending' => '待验证',
        'verified' => '已验证',
        'rejected' => '已拒绝'
    ];
    
    $class = $status_classes[$status] ?? 'status-pending';
    $text = $status_texts[$status] ?? '未知状态';
    
    return sprintf(
        '<span class="status-badge %s">%s</span>',
        esc_attr($class),
        esc_html($text)
    );
}

/**
 * 记录支付日志
 */
function csrwiki_log_payment($user_id, $action, $data = []) {
    $log_dir = WP_CONTENT_DIR . '/payment-logs';
    
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    
    $log_file = $log_dir . '/payment-' . date('Y-m') . '.log';
    $time = current_time('mysql');
    
    $log_data = array_merge([
        'time' => $time,
        'user_id' => $user_id,
        'action' => $action
    ], $data);
    
    $log_line = json_encode($log_data, JSON_UNESCAPED_UNICODE) . "\n";
    
    error_log($log_line, 3, $log_file);
}

/**
 * 检查用户是否是有效会员
 */
function csrwiki_is_active_member($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $payment_status = csrwiki_get_user_payment_status($user_id);
    return $payment_status === 'verified';
}

/**
 * 获取会员过期时间
 */
function csrwiki_get_member_expiry($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csrwiki_payments';
    
    $payment_time = $wpdb->get_var($wpdb->prepare(
        "SELECT payment_time 
        FROM $table_name 
        WHERE user_id = %d 
        AND status = 'verified' 
        ORDER BY payment_time DESC 
        LIMIT 1",
        $user_id
    ));
    
    if (!$payment_time) {
        return false;
    }
    
    return strtotime('+1 year', strtotime($payment_time));
}

/**
 * 格式化剩余天数
 */
function csrwiki_format_days_remaining($expiry_time) {
    if (!$expiry_time) {
        return '未开通';
    }
    
    $now = time();
    $days_remaining = ceil(($expiry_time - $now) / (24 * 60 * 60));
    
    if ($days_remaining < 0) {
        return '已过期';
    }
    
    return sprintf('%d天', $days_remaining);
}

/**
 * 检查并发送会员到期提醒
 */
function csrwiki_check_expiration_notifications() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csrwiki_payments';
    
    // 获取即将到期的会员（7天内）
    $expiring_members = $wpdb->get_results(
        "SELECT DISTINCT p.user_id, u.user_email, u.user_login
        FROM $table_name p
        JOIN {$wpdb->users} u ON p.user_id = u.ID
        WHERE p.status = 'verified'
        AND p.payment_time <= DATE_SUB(NOW(), INTERVAL 358 DAY)
        AND p.payment_time > DATE_SUB(NOW(), INTERVAL 365 DAY)"
    );
    
    foreach ($expiring_members as $member) {
        $expiry_date = csrwiki_get_member_expiry($member->user_id);
        $days_remaining = ceil(($expiry_date - time()) / (24 * 60 * 60));
        
        $subject = sprintf(
            '【会员即将到期】还剩%d天 - %s',
            $days_remaining,
            get_bloginfo('name')
        );
        
        $message = "亲爱的{$member->user_login}：\n\n";
        $message .= "您的会员即将在{$days_remaining}天后到期。\n";
        $message .= "为确保您能继续享受会员权益，请及时续费。\n\n";
        $message .= "续费链接：" . site_url('/member-payment/') . "\n\n";
        $message .= get_bloginfo('name') . "团队";
        
        wp_mail($member->user_email, $subject, $message);
        
        csrwiki_log_payment($member->user_id, 'expiration_notice', [
            'days_remaining' => $days_remaining
        ]);
    }
}
add_action('csrwiki_daily_tasks', 'csrwiki_check_expiration_notifications');

// 确保每日任务钩子存在
if (!wp_next_scheduled('csrwiki_daily_tasks')) {
    wp_schedule_event(time(), 'daily', 'csrwiki_daily_tasks');
}