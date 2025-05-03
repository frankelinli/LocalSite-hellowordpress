wp.blocks.registerBlockType('csrwiki/simple-note', {
    title: '文末注释',
    icon: 'text',
    category: 'common',
    attributes: {
        content: {
            type: 'string',
            default: ''
        }
    },
    
    edit: function(props) {
        return wp.element.createElement(
            wp.components.TextareaControl,
            {
                value: props.attributes.content,
                onChange: function(newContent) {
                    props.setAttributes({ content: newContent });
                },
                placeholder: '输入注释内容...',
                className: 'simple-note-editor'
            }
        );
    },
    
    save: function(props) {
        return wp.element.createElement(
            'div',
            { className: 'simple-note' },
            props.attributes.content
        );
    }
});