// admin-only-block.js
wp.blocks.registerBlockType('csrwiki/admin-only', {
    title: '仅管理员可见',
    icon: 'lock',
    category: 'common',
    description: '此区块中的内容仅管理员可见',
    
    edit: function(props) {
        return wp.element.createElement(
            'div',
            { className: 'wp-block-csrwiki-admin-only' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'admin-only-header' },
                    '🔒 仅管理员可见'
                ),
                wp.element.createElement(
                    'div',
                    { className: 'admin-only-content' },
                    wp.element.createElement(
                        wp.blockEditor.InnerBlocks,
                        {
                            template: [
                                ['core/paragraph', {}]
                            ],
                            allowedBlocks: true,
                            templateLock: false
                        }
                    )
                )
            ]
        );
    },
    
    save: function(props) {
        return wp.element.createElement(
            'div',
            { className: 'wp-block-csrwiki-admin-only' },
            wp.element.createElement(wp.blockEditor.InnerBlocks.Content)
        );
    }
});