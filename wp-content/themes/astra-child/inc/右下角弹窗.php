<?Php


/*
如需弹窗始终显示（即便用户关闭后刷新依然出现），只需把
window.localStorage.setItem('cta_float_popup_closed', '1');
和
if(window.localStorage.getItem('cta_float_popup_closed')) return;
相关两行删掉即可！
*/


function my_cta_floating_popup()
{ ?>
    <style>
        #cta-float-popup {
            display: block;
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 99999;
            max-width: 340px;
            width: 92vw;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            padding: 18px 16px 14px 16px;
            text-align: center;
            font-family: "PingFang SC", "Helvetica Neue", Arial, sans-serif;
            transition: opacity .3s;
        }

        #cta-float-popup .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 22px;
            color: #888;
            cursor: pointer;
            line-height: 1;
            z-index: 10;
            background: #fff;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        #cta-float-popup img {
            max-width: 110px;
            margin: 12px auto 6px;
            display: block;
        }

        #cta-float-popup h2 {
            color: #157efb;
            font-size: 1.1em;
            margin-bottom: 8px;
            margin-top: 8px;
        }

        #cta-float-popup p {
            color: #555;
            font-size: 1em;
            line-height: 1.7;
        }

        #cta-float-popup .cta-phone {
            font-size: 1.05em;
            font-weight: bold;
            margin: 8px 0 4px;
            color: #157efb;
        }

        @media (max-width: 540px) {
            #cta-float-popup {
                right: 2vw;
                bottom: 2vw;
                max-width: 97vw;
                padding: 10px 4vw 8px 4vw;
            }

            #cta-float-popup img {
                max-width: 90px;
                margin: 8px auto 4px;
            }

            #cta-float-popup .close-btn {
                width: 22px;
                height: 22px;
                font-size: 18px;
                top: 4px;
                right: 6px;
            }
        }
    </style>
    <div id="cta-float-popup" style="display:none;">
        <span class="close-btn" onclick="closeCtaFloatPopup()" title="关闭">&times;</span>
        <h2>专业验厂辅导</h2>
        <p>助力企业通过企业认证、加速海外贸易！</p>
        <div class="cta-phone">电话：<a href="tel:13800138000">13800138000</a></div>
        <img src="https://你的域名.com/二维码图片地址.png" alt="微信二维码">
        <div>微信扫码咨询</div>
    </div>
    <script>
        function showCtaFloatPopup() {
            var pop = document.getElementById('cta-float-popup');
            if (!pop) return;
            // if(window.localStorage.getItem('cta_float_popup_closed')) return;
            pop.style.display = 'block';
            setTimeout(function() {
                pop.style.opacity = 1;
            }, 10);
        }

        function closeCtaFloatPopup() {
            var pop = document.getElementById('cta-float-popup');
            if (pop) {
                pop.style.opacity = 0;
                setTimeout(function() {
                    pop.style.display = 'none';
                }, 300);
                //   window.localStorage.setItem('cta_float_popup_closed', '1');
            }
        }
        // 页面加载完主动显示
        document.addEventListener('DOMContentLoaded', showCtaFloatPopup);
    </script>
<?php }
add_action('wp_footer', 'my_cta_floating_popup');
