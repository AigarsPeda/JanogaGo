/* Keep the native image editor. GLB selection and control copy live in its sidebar. */
(function (wp) {
  const el = wp.element.createElement;
  wp.hooks.addFilter('blocks.registerBlockType', 'janogago/model-image', function (settings, name) {
    if (name !== 'core/image') return settings;
    return Object.assign({}, settings, { attributes: Object.assign({}, settings.attributes, {
      jgModelId: { type: 'number', default: 0 },
      jgModelPosterId: { type: 'number', default: 0 },
      jgModelPause: { type: 'string', default: '' },
      jgModelResume: { type: 'string', default: '' },
      jgModelHint: { type: 'string', default: '' }
    }) });
  });
  wp.hooks.addFilter('editor.BlockEdit', 'janogago/model-image-controls', wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (props.name !== 'core/image') return el(BlockEdit, props);
      const attrs = props.attributes;
      return el(wp.element.Fragment, {}, el(BlockEdit, props),
        el(wp.blockEditor.InspectorControls, {},
          el(wp.components.PanelBody, { title: '3D machine / 3D automāts', initialOpen: Boolean(attrs.jgModelId) },
            el('p', {}, 'Use a matching 3D preview while loading. The original image is the error fallback. / Ielādes laikā izmantojiet atbilstošu 3D priekšskatījumu. Sākotnējais attēls ir rezerves attēls kļūdas gadījumā.'),
            el(wp.blockEditor.MediaUploadCheck, {}, el(wp.blockEditor.MediaUpload, {
              allowedTypes: ['model/gltf-binary'], value: attrs.jgModelId,
              onSelect: function (media) { props.setAttributes({ jgModelId: media.id, jgModelPosterId: 0 }); },
              render: function (control) { return el(wp.components.Button, { variant: 'secondary', onClick: control.open }, attrs.jgModelId ? 'Replace GLB / Mainīt GLB' : 'Choose GLB / Izvēlēties GLB'); }
            })),
            attrs.jgModelId ? el(wp.components.Button, { variant: 'tertiary', isDestructive: true, onClick: function () { props.setAttributes({ jgModelId: 0, jgModelPosterId: 0 }); } }, 'Remove 3D / Noņemt 3D') : null,
            attrs.jgModelId ? el('p', {}, 'Media ID: ' + attrs.jgModelId) : null,
            attrs.jgModelId ? el(wp.blockEditor.MediaUploadCheck, {}, el(wp.blockEditor.MediaUpload, {
              allowedTypes: ['image'], value: attrs.jgModelPosterId,
              onSelect: function (media) { props.setAttributes({ jgModelPosterId: media.id }); },
              render: function (control) { return el(wp.components.Button, { variant: 'secondary', onClick: control.open }, attrs.jgModelPosterId ? 'Replace 3D preview / Mainīt 3D priekšskatījumu' : 'Choose 3D preview / Izvēlēties 3D priekšskatījumu'); }
            })) : null,
            attrs.jgModelPosterId ? el(wp.components.Button, { variant: 'tertiary', onClick: function () { props.setAttributes({ jgModelPosterId: 0 }); } }, 'Remove preview / Noņemt priekšskatījumu') : null,
            ...[['jgModelPause', 'Pause accessible label / Apturēšanas pieejamības teksts'], ['jgModelResume', 'Resume accessible label / Turpināšanas pieejamības teksts']].map(function (field) {
              return el(wp.components.TextControl, { key: field[0], label: field[1], value: attrs[field[0]], onChange: function (value) { props.setAttributes({ [field[0]]: value }); } });
            })
          )
        )
      );
    };
  }, 'WithJanogaModel'));
})(window.wp);
