<?php
/**
 * CSR Wiki Payment System - Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 添加管理菜单
 */
function csrwiki_add_payment_admin_menu() {
    add_menu_page(
        '会员验证',
        '会员验证',
        'manage_options',
        'member-verification',
        'csrwiki_render_verification_page',
        'dashicons-tickets',
        30
    );
}
add_action('admin_menu', 'csrwiki_add_payment_admin_menu');

/**
 * 渲染验证页面
 */
function csrwiki_render_verification_page() {
    // 处理验证操作
    if (isset($_POST['verify_action']) && check_admin_referer('verify_member')) {
        $user_id = intval($_POST['user_id']);
        $action = sanitize_text_field($_POST['verify_action']);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        
        $result = csrwiki_process_verification($user_id, $action, $notes);
        
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>' . esc_html($result) . '</p></div>';
        }
    }
    
    // 获取待验证列表
    global $wpdb;
    $table_name = $wpdb->prefix . 'csrwiki_payments';
    
    $payments = $wpdb->get_results(
        "SELECT p.*, u.user_login, u.user_email 
        FROM $table_name p 
        JOIN {$wpdb->users} u ON p.user_id = u.ID 
        ORDER BY p.payment_time DESC 
        LIMIT 50"
    );
    
    ?>
    <div class="wrap">
        <h1>会员验证</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="payment-status-filter">
                    <option value="">所有状态</option>
                    <option value="pending">待验证</option>
                    <option value="verified">已验证</option>
                    <option value="rejected">已拒绝</option>
                </select>
                <button class="button" id="filter-button">筛选</button>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>订单号</th>
                    <th>支付时间</th>
                    <th>金额</th>
                    <th>状态</th>
                    <th>备注</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8">暂无记录</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="payment-row status-<?php echo esc_attr($payment->status); ?>">
                            <td><?php echo esc_html($payment->user_login); ?></td>
                            <td><?php echo esc_html($payment->user_email); ?></td>
                            <td><?php echo esc_html($payment->order_number); ?></td>
                            <td><?php echo esc_html($payment->payment_time); ?></td>
                            <td><?php echo esc_html($payment->amount); ?>元</td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($payment->status); ?>">
                                    <?php echo csrwiki_get_status_text($payment->status); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($payment->notes); ?></td>
                            <td>
                                <?php if ($payment->status === 'pending'): ?>
                                    <form method="post" class="verify-form">
                                        <?php wp_nonce_field('verify_member'); ?>
                                        <input type="hidden" name="user_id" value="<?php echo esc_attr($payment->user_id); ?>">
                                        <textarea name="notes" placeholder="验证备注" class="verification-notes"></textarea>
                                        <button type="submit" name="verify_action" value="approve" class="button button-primary">
                                            通过
                                        </button>
                                        <button type="submit" name="verify_action" value="reject" class="button button-secondary">
                                            拒绝
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <?php if ($payment->verified_time): ?>
                                        <small>
                                            处理时间：<?php echo esc_html($payment->verified_time); ?><br>
                                            处理人：<?php echo esc_html(get_user_by('id', $payment->verified_by)->user_login); ?>
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <style>
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-badge.status-pending {
            background: #fff8e5;
            color: #b88217;
        }
        .status-badge.status-verified {
            background: #e7f7ed;
            color: #0a7b2d;
        }
        .status-badge.status-rejected {
            background: #ffe5e5;
            color: #d63638;
        }
        .verification-notes {
            width: 100%;
            margin-bottom: 5px;
        }
        .verify-form {
            margin-bottom: 0;
        }
        .verify-form .button {
            margin: 2px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        $('#filter-button').on('click', function() {
            var status = $('#payment-status-filter').val();
            $('.payment-row').show();
            if (status) {
                $('.payment-row').not('.status-' + status).hide();
            }
        });
    });
    </script>
    <?php
}

/**
 * 处理验证操作
 */
function csrwiki_process_verification($user_id, $action, $notes) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csrwiki_payments';
    
    $user = get_user_by('id', $user_id);
    if (!$user) {
        return new WP_Error('invalid_user', '用户不存在');
    }
    
    $current_admin_id = get_current_user_id();
    
    if ($action === 'approve') {
        // 更新支付状态
        $wpdb->update(
            $table_name,
            array(
                'status' => 'verified',
                'verified_by' => $current_admin_id,
                'verified_time' => current_time('mysql'),
                'notes' => $notes
            ),
            array('user_id' => $user_id, 'status' => 'pending'),
            array('%s', '%d', '%s', '%s'),
            array('%d', '%s')
        );
        
        // 添加会员角色
        $user->add_role(CSRWIKI_MEMBER_ROLE);
        
        // 发送通知邮件
        $subject = '会员验证通过 - ' . get_bloginfo('name');
        $message = "尊敬的{$user->user_login}：\n\n";
        $message .= "恭喜您！您的会员身份已验证通过。\n";
        $message .= "现在您可以访问所有会员专属内容了。\n\n";
        if ($notes) {
            $message .= "验证备注：{$notes}\n\n";
        }
        $message .= "感谢您的支持！\n";
        $message .= get_bloginfo('name') . "团队";
        
        wp_mail($user->user_email, $subject, $message);
        
        return '已通过会员验证';
        
    } elseif ($action === 'reject') {
        // 更新支付状态
        $wpdb->update(
            $table_name,
            array(
                'status' => 'rejected',
                'verified_by' => $current_admin_id,
                'verified_time' => current_time('mysql'),
                'notes' => $notes
            ),
            array('user_id' => $user_id, 'status' => 'pending'),
            array('%s', '%d', '%s', '%s'),
            array('%d', '%s')
        );
        
        // 发送通知邮件
        $subject = '会员验证未通过 - ' . get_bloginfo('name');
        $message = "尊敬的{$user->user_login}：\n\n";
        $message .= "很抱歉，您的会员验证未能通过。\n";
        if ($notes) {
            $message .= "原因：{$notes}\n\n";
        }
        $message .= "如有疑问，请联系管理员。\n";
        $message .= get_bloginfo('name') . "团队";
        
        wp_mail($user->user_email, $subject, $message);
        
        return '已拒绝会员验证';
    }
    
    return new WP_Error('invalid_action', '无效的操作');
}

/**
 * 获取状态文本
 */
function csrwiki_get_status_text($status) {
    $status_texts = array(
        'pending' => '待验证',
        'verified' => '已验证',
        'rejected' => '已拒绝'
    );
    
    return $status_texts[$status] ?? $status;
}

/**
 * 添加用户列表会员状态列
 */
function csrwiki_add_user_member_column($columns) {
    $columns['member_status'] = '会员状态';
    return $columns;
}
add_filter('manage_users_columns', 'csrwiki_add_user_member_column');

/**
 * 显示用户列表会员状态
 */
function csrwiki_show_user_member_status($value, $column_name, $user_id) {
    if ($column_name !== 'member_status') {
        return $value;
    }
    
    $payment_status = csrwiki_get_user_payment_status($user_id);
    $status_text = csrwiki_get_status_text($payment_status);
    
    return sprintf(
        '<span class="status-badge status-%s">%s</span>',
        esc_attr($payment_status),
        esc_html($status_text)
    );
}
add_filter('manage_users_custom_column', 'csrwiki_show_user_member_status', 10, 3);