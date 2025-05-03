wp.blocks.registerBlockType('csrwiki/warning', {
    title: '警告块',
    icon: 'warning',
    category: 'common',
    description: '添加带有警告背景的提示信息块',
    keywords: ['warning', '警告', '注意'],
    
    edit: function(props) {
        return wp.element.createElement(
            'div',
            { className: 'wp-block-csrwiki-warning' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'warning-header' },
                    [
                        wp.element.createElement(
                            'span',
                            { className: 'warning-icon' },
                            '⚠️'
                        ),
                        wp.element.createElement(
                            'span',
                            { className: 'warning-label' },
                            'WARNING'
                        )
                    ]
                ),
                wp.element.createElement(
                    'div',
                    { className: 'warning-content' },
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
            { className: 'wp-block-csrwiki-warning' },
            [
                wp.element.createElement(
                    'div',
                    { className: 'warning-header' },
                    [
                        wp.element.createElement(
                            'span',
                            { className: 'warning-icon' },
                            '⚠️'
                        ),
                        wp.element.createElement(
                            'span',
                            { className: 'warning-label' },
                            'WARNING'
                        )
                    ]
                ),
                wp.element.createElement(
                    'div',
                    { className: 'warning-content' },
                    wp.element.createElement(wp.blockEditor.InnerBlocks.Content)
                )
            ]
        );
    }
});