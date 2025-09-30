jQuery(document).ready(function($) {
    $('.like-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var post_id = button.data('post-id');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'like_post',
                post_id: post_id,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.likes-count[data-post-id="' + post_id + '"]').text(response.likes);
                    button.prop('disabled', true).text('已点赞');
                } else {
                    alert(response.message);
                }
            }
        });
    });

    // 页面加载时检查并禁用已点赞的按钮
    $('.like-button').each(function() {
        var post_id = $(this).data('post-id');
        if (getCookie('liked_post_' + post_id)) {
            $(this).prop('disabled', true).text('已点赞');
        }
    });

    // 获取 cookie 的辅助函数
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }


















    
});


jQuery(document).ready(function($) {
    $('.favorite-button').click(function() {
        var button = $(this);
        var postId = button.data('post-id');
        
        $.ajax({
            url: ajax_object.ajax_url, // 修改这里
            type: 'POST',
            data: {
                action: 'handle_favorite',
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.action === 'added') {
                        button.addClass('favorited');
                        button.text('取消收藏');
                    } else {
                        button.removeClass('favorited');
                        button.text('收藏文章');
                    }
                }
            }
        });
    });
});
