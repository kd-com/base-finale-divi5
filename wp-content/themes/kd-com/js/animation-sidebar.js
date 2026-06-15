(function(wp) {
    var addFilter = wp.hooks.addFilter;
    var addAction = wp.hooks.addAction;
    var coreBlocks = [
        'core/paragraph', 'core/heading', 'core/image', 'core/list', 'core/quote', 'core/gallery', 'core/audio', 'core/cover', 'core/file', 'core/video', 'core/table', 'core/group', 'core/button', 'core/buttons', 'core/separator', 'core/spacer', 'core/html', 'core/code', 'core/preformatted', 'core/pullquote', 'core/media-text', 'core/more', 'core/nextpage', 'core/page-break', 'core/embed'
    ];
    addFilter(
        'blocks.registerBlockType',
        'kd-com/animation-aos-attributes',
        function(settings, name) {
            if (coreBlocks.indexOf(name) !== -1) {
                settings.attributes = Object.assign({}, settings.attributes, {
                    'data-aos': {
                        type: 'string',
                        default: ''
                    },
                    'data-aos-delay': {
                        type: 'string',
                        default: ''
                    }
                });
            }
            return settings;
        }
    );
})(window.wp);
(function(wp) {
    var addFilter = wp.hooks.addFilter;
    // Ajoute les attributs data-aos et data-aos-delay au HTML du block lors de la sauvegarde
    addFilter(
        'blocks.getSaveContent.extraProps',
        'kd-com/animation-aos-save-props',
        function(extraProps, blockType, attributes) {
            if (blockType.name && !blockType.name.startsWith('acf/')) {
                if (attributes['data-aos']) {
                    extraProps['data-aos'] = attributes['data-aos'];
                }
                if (attributes['data-aos-delay']) {
                    extraProps['data-aos-delay'] = attributes['data-aos-delay'];
                }
            }
            return extraProps;
        }
    );
})(window.wp);
(function(wp) {
    var addFilter = wp.hooks.addFilter;
    var el = wp.element.createElement;
    var SelectControl = wp.components.SelectControl;
    var InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : wp.editor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var animationTypes = [
        { label: 'Aucune animation', value: 'none' },
        { label: 'Fondu montant', value: 'fade-up' },
        { label: 'Fondu descendant', value: 'fade-down' },
        { label: 'Fondu depuis la gauche', value: 'fade-left' },
        { label: 'Fondu depuis la droite', value: 'fade-right' },
        { label: 'Zoom avant', value: 'zoom-in' },
        { label: 'Zoom arrière', value: 'zoom-out' },
    ];
    var animationDelays = [
        { label: 'Pas de délai', value: '0' },
        { label: 'Court (0.1s)', value: '100' },
        { label: 'Moyen (0.2s)', value: '200' },
        { label: 'Long (0.3s)', value: '300' },
    ];
    function addAOSControls(BlockEdit) {
        return function(props) {
            if (props.name && props.name.startsWith('acf/')) return el(BlockEdit, props);
            return el(wp.element.Fragment, null,
                el(BlockEdit, props),
                props.isSelected && el(InspectorControls, null,
                    el(PanelBody, { title: 'Animation (AOS)', initialOpen: true },
                        el(SelectControl, {
                            label: "Type d'animation",
                            value: props.attributes['data-aos'] || 'none',
                            options: animationTypes,
                            onChange: function(value) {
                                props.setAttributes({ 'data-aos': value });
                            }
                        }),
                        el(SelectControl, {
                            label: "Délai entre les animations",
                            value: props.attributes['data-aos-delay'] || '0',
                            options: animationDelays,
                            onChange: function(value) {
                                props.setAttributes({ 'data-aos-delay': value });
                            }
                        })
                    )
                )
            );
        };
    }
    addFilter(
        'editor.BlockEdit',
        'kd-com/animation-aos-controls',
        addAOSControls
    );
})(window.wp);
