jQuery(document).ready(function ($) {
    // 点击子分类标题展开/折叠子分类文章列表
    $('.child-category-title').on('click', function () {
        var childCategoryId = $(this).data('child-category');
        var childCategoryList = $('#child-category-' + childCategoryId);

        if (childCategoryList.is(':visible')) {
            childCategoryList.slideUp(); // 折叠
            $(this).text($(this).text().replace('▲', '▼')); // 更新箭头
        } else {
            childCategoryList.slideDown(); // 展开
            $(this).text($(this).text().replace('▼', '▲')); // 更新箭头
        }
    });
});