import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'ict-shop-the-look',
    label: 'Shop The Look',
    component: 'sw-cms-el-ict-shop-the-look',
    configComponent: 'sw-cms-el-config-ict-shop-the-look',
    previewComponent: 'sw-cms-el-preview-ict-shop-the-look',

    defaultConfig: {
        lookImage: {
            source: 'static',
            value: null
        },

        products: {
            source: 'static',
            value: []
        },

        hotspots: {
            source: 'static',
            value: []
        },

        showPrices: {
            source: 'static',
            value: true
        },

        layoutStyle: {
            source: 'static',
            value: 'side-by-side'
        }
    }
});