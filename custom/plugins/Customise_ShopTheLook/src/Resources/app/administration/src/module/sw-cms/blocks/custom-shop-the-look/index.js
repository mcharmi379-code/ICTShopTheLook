import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'custom-shop-the-look',
    label: 'Custom Shop The Look Block',
    category: 'image',
    component: 'sw-cms-block-custom-shop-the-look',
    previewComponent: 'sw-cms-preview-custom-shop-the-look',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        one: {
            type: 'custom-shop-the-look'
        }
    }
});
