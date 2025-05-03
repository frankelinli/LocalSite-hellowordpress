wp.blocks.registerBlockType('csrwiki/info', {
    title: '信息块',
    icon: 'info',
    category: 'common',
    description: '添加带有信息背景的提示区块',
    keywords: ['info', '信息', '提示'],
    
    edit: function(props) {
        return wp.element.createElement(
            'div',
            { className: 'wp-block-csrwiki-info' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'info-header' },
                    [
                        wp.element.createElement(
                            'span',
                            { className: 'info-icon' },
                            'ℹ️'
                        ),
                        wp.element.createElement(
                            'span',
                            { className: 'info-label' },
                            'INFO'
                        )
                    ]
                ),
                wp.element.createElement(
                    'div',
                    { className: 'info-content' },
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
            { className: 'wp-block-csrwiki-info' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'info-header' },
                    [
                        wp.element.createElement(
                            'span',
                            { className: 'info-icon' },
                            'ℹ️'
                        ),
                        wp.element.createElement(
                            'span',
                            { className: 'info-label' },
                            'INFO'
                        )
                    ]
                ),
                wp.element.createElement(
                    'div',
                    { className: 'info-content' },
                    wp.element.createElement(wp.blockEditor.InnerBlocks.Content)
                )
            ]
        );
    }
});