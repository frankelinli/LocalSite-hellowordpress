<?php
/**
 * Payment Form Shortcode
 */

if (!defined('ABSPATH')) {
    exit;
}

function csrwiki_payment_form_shortcode() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $payment_status = csrwiki_get_user_payment_status($user_id);
        
        if ($payment_status === 'verified') {
            return '<div class="payment-notice success">您已是正式会员，无需重复开通。</div>';
        } elseif ($payment_status === 'pending') {
            return '<div class="payment-notice info">您的会员申请正在审核中，请耐心等待。</div>';
        }
    }
    
    ob_start();
    ?>
    <script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>

    <div class="payment-container">
        <div class="payment-info">
            <h2>开通会员</h2>
            <div class="price-info">
                会员价格：<span class="price">99元</span>
                <small class="price-period">/年</small>
            </div>
        </div>

        <div class="payment-qrcode">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/payment-qr.jpg" 
                 alt="支付二维码" class="qr-image">
            <p class="qr-notice">请使用微信或支付宝扫码支付</p>
            <p class="qr-tip">支付完成后请填写以下信息</p>
        </div>

        <form class="register-payment-form" id="registerPaymentForm">
            <?php wp_nonce_field('register_payment_nonce', 'payment_nonce'); ?>
            
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required
                       pattern="[a-zA-Z0-9_]{4,20}"
                       title="4-20位字母、数字或下划线">
                <span class="form-tip">4-20位字母、数字或下划线</span>
            </div>

            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" required>
                <span class="form-tip">请填写真实邮箱，用于接收通知</span>
            </div>

            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required
                       minlength="8">
                <span class="form-tip">至少8位密码</span>
            </div>

            <div class="form-group">
                <label for="order_number">支付订单号后4位</label>
                <input type="text" id="order_number" name="order_number" required
                       pattern="[0-9]{4}"
                       title="请输入支付订单号后4位数字">
                <span class="form-tip">请查看支付记录中的订单号后4位</span>
            </div>

            <div class="payment-message" style="display: none;"></div>

            <button type="submit" class="submit-button">
                提交验证
                <span class="loading-spinner"></span>
            </button>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#registerPaymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $button = $form.find('button[type="submit"]');
            const $message = $form.find('.payment-message');
            const $spinner = $form.find('.loading-spinner');
            
            $button.prop('disabled', true);
            $spinner.show();
            $message.removeClass('success error').hide();
            
            const formData = {
                action: 'register_and_verify_payment',
                payment_nonce: $('#payment_nonce').val(),
                username: $('#username').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                order_number: $('#order_number').val()
            };
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Success response:', response);
                    if (response.success) {
                        $message.addClass('success')
                                .html(response.data.message)
                                .show();
                        $form[0].reset();
                        
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    } else {
                        $message.addClass('error')
                                .html(response.data?.message || '提交失败，请稍后重试')
                                .show();
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    $message.addClass('error')
                            .html('网络错误，请稍后重试 [' + status + ']')
                            .show();
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.hide();
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('csrwiki_payment_form', 'csrwiki_payment_form_shortcode');

function csrwiki_register_and_verify_payment() {
    if (!check_ajax_referer('register_payment_nonce', 'payment_nonce', false)) {
        wp_send_json_error(['message' => '安全验证失败，请刷新页面重试']);
        return;
    }
    
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $order_number = sanitize_text_field($_POST['order_number']);
    
    // 验证数据
    if (empty($username) || empty($email) || empty($password) || empty($order_number)) {
        wp_send_json_error(['message' => '请填写所有必填项']);
        return;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        wp_send_json_error(['message' => '用户名格式不正确']);
        return;
    }
    
    if (!is_email($email)) {
        wp_send_json_error(['message' => '邮箱格式不正确']);
        return;
    }
    
    if (strlen($password) < 8) {
        wp_send_json_error(['message' => '密码长度至少8位']);
        return;
    }
    
    if (!preg_match('/^[0-9]{4}$/', $order_number)) {
        wp_send_json_error(['message' => '订单号格式不正确']);
        return;
    }
    
    if (username_exists($username)) {
        wp_send_json_error(['message' => '用户名已被注册']);
        return;
    }
    
    if (email_exists($email)) {
        wp_send_json_error(['message' => '邮箱已被注册']);
        return;
    }
    
    // 创建用户
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
        return;
    }
    
    // 添加支付记录
    update_user_meta($user_id, 'payment_status', 'pending');
    update_user_meta($user_id, 'order_number', $order_number);
    
    // 发送邮件通知
    $admin_email = get_option('admin_email');
    $subject = '新会员注册待验证';
    $message = "新用户注册信息：\n\n";
    $message .= "用户名：$username\n";
    $message .= "邮箱：$email\n";
    $message .= "订单号：$order_number\n";
    
    wp_mail($admin_email, $subject, $message);
    
    wp_send_json_success(['message' => '注册成功！请等待管理员验证，验证通过后将发送邮件通知。']);
}

add_action('wp_ajax_register_and_verify_payment', 'csrwiki_register_and_verify_payment');
add_action('wp_ajax_nopriv_register_and_verify_payment', 'csrwiki_register_and_verify_payment');