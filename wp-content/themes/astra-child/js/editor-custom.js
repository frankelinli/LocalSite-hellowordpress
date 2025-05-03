// editor-custom.js
wp.domReady(() => {
    // 获取所有块类型
    const blockTypes = wp.blocks.getBlockTypes();
    
    blockTypes.forEach(blockType => {
        // 扩展现有的块属性
        wp.blocks.unregisterBlockType(blockType.name);
        
        const settings = {
            ...blockType,
            attributes: {
                ...blockType.attributes,
                adminOnly: {
                    type: 'boolean',
                    default: false,
                }
            },
            edit: function(props) {
                const { attributes, setAttributes } = props;
                
                // 创建原始的编辑界面
                const originalEdit = blockType.edit;
                
                return wp.element.createElement(
                    wp.element.Fragment,
                    null,
                    originalEdit(props),
                    wp.element.createElement(
                        wp.blockEditor.InspectorControls,
                        {},
                        wp.element.createElement(
                            wp.components.PanelBody,
                            { title: '访问控制' },
                            wp.element.createElement(
                                wp.components.ToggleControl,
                                {
                                    label: '仅管理员可见',
                                    checked: attributes.adminOnly,
                                    onChange: (value) => setAttributes({ adminOnly: value })
                                }
                            )
                        )
                    )
                );
            }
        };
        
        wp.blocks.registerBlockType(blockType.name, settings);
    });
});