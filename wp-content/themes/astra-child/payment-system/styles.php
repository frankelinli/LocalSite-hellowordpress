<?php
/**
 * CSR Wiki Payment System - Styles
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 添加前端样式
 */
function csrwiki_payment_styles() {
    wp_enqueue_style('dashicons');
    ?>
    <style>
        /* 支付容器 */
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* 支付信息区 */
        .payment-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .benefits {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .benefits ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .benefits li {
            margin: 10px 0;
            color: #444;
        }

        .benefits .dashicons {
            color: #2271b1;
            margin-right: 10px;
        }

        /* 价格信息 */
        .price-info {
            font-size: 18px;
            margin: 20px 0;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            color: #e4393c;
        }

        .price-period {
            color: #666;
            font-size: 14px;
        }

        /* 二维码区域 */
        .payment-qrcode {
            text-align: center;
            margin: 30px 0;
        }

        .qr-image {
            max-width: 200px;
            height: auto;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 4px;
        }

        .qr-notice {
            color: #666;
            margin: 10px 0;
            font-size: 14px;
        }

        .qr-tip {
            color: #e4393c;
            font-size: 14px;
            margin: 5px 0;
        }

        /* 表单样式 */
        .register-payment-form {
            max-width: 500px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }

        .form-tip {
            display: block;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* 提交按钮 */
        .submit-button {
            width: 100%;
            padding: 12px;
            background: #2271b1;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            position: relative;
        }

        .submit-button:hover {
            background: #135e96;
        }

        .submit-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* 加载动画 */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            position: absolute;
            right: 10px;
            top: 50%;
            margin-top: -10px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 消息提示 */
        .payment-message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            text-align: center;
        }

        .payment-message.success {
            background: #e7f7ed;
            color: #0a7b2d;
            border: 1px solid #a3d9b6;
        }

        .payment-message.error {
            background: #ffe5e5;
            color: #d63638;
            border: 1px solid #ffb1b1;
        }

        .payment-notice {
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
        }

        .payment-notice.success {
            background: #e7f7ed;
            color: #0a7b2d;
        }

        .payment-notice.info {
            background: #e5f5fa;
            color: #135e96;
        }

        /* 会员内容样式 */
        .member-content-locked {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }

        .member-notice {
            margin: 0 0 15px;
            color: #666;
        }

        .member-button {
            display: inline-block;
            padding: 8px 16px;
            background: #2271b1;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .member-button:hover {
            background: #135e96;
            color: #fff;
        }
    </style>
    <?php
}
add_action('wp_head', 'csrwiki_payment_styles');


