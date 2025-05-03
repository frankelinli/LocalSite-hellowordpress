wp.blocks.registerBlockType('csrwiki/tip', {
    title: '提示块',
    icon: 'lightbulb',
    category: 'common',
    description: '添加带有绿色背景的提示信息块',
    keywords: ['tip', '提示', '信息'],
    
    edit: function(props) {
        return wp.element.createElement(
            'div',
            { className: 'wp-block-csrwiki-tip' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'tip-header' },
                    [
                        wp.element.createElement(
                            'span',
                            { className: 'tip-icon' },
                            '💡'
                        ),
                        wp.element.createElement(
                            'span',
                            { className: 'tip-label' },
                            'TIP'
                        )
                    ]
                ),
                wp.element.createElement(
                    'div',
                    { className: 'tip-content' },
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
            { className: 'wp-block-csrwiki-tip' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'tip-header' },
                    [
                        wp.element.createElement(
                            'span',
                            { className: 'tip-icon' },
                            '💡'
                        ),
                        wp.element.createElement(
                            'span',
                            { className: 'tip-label' },
                            'TIP'
                        )
                    ]
                ),
                wp.element.createElement(
                    'div',
                    { className: 'tip-content' },
                    wp.element.createElement(wp.blockEditor.InnerBlocks.Content)
                )
            ]
        );
    }
});