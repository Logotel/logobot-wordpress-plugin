( function( blocks, element, blockEditor, components ) {
    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var TextControl = components.TextControl;

    blocks.registerBlockType( 'logobot-wp/logobot-block', {
        title: 'Logobot', // Titolo del blocco
        icon: 'format-status', // Icona del blocco
        category: 'widgets', // Categoria in cui aggiungere il blocco
        attributes: { // Aggiungi qui gli attributi
            wrapperId: {
                type: 'string',
                default: 'logobot-wrapper'
            }
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            function onChangeDivId( newValue ) {
                setAttributes( { wrapperId: newValue } );
            }

            return el( 
                'div',
                useBlockProps(),
                el(
                    TextControl,
                    {
                        label: 'Logobot wrapper ID',
                        value: attributes.wrapperId,
                        onChange: onChangeDivId,
                    }
                ),
                el(
                    'p',
                    {},
                    'Questo è il blocco per embeddare il Logobot in pagina. Puoi cambiare l’id del div qui.'
                )
            );
        },

        save: function() {
            return null;  // Il rendering viene gestito dal PHP
        },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);