alert("hey brooo");
import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'ict-shop-look-block',
    label: 'Shop Look Slider Block',
    category: 'image',
    component: 'sw-cms-block-ict-shop-look',
    previewComponent: 'sw-cms-preview-ict-shop-look-block',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        content: 'ict-shop-look-slider'
    }
});