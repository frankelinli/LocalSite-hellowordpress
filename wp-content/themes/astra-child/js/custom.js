

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
