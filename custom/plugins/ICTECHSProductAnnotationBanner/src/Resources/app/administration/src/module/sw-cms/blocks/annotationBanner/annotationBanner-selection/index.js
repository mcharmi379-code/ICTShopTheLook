Shopware.Component.register('sw-cms-block-annotationBanner-selection', () => import('./component'));
Shopware.Component.register('sw-cms-preview-annotationBanner-selection', () => import('./preview'));

Shopware.Service('cmsService').registerCmsBlock({
    name: 'annotationBanner-selection',
    label: 'cms-annotation-banner.general.mainMenuItemGeneral',
    category: 'text-image',
    component: 'sw-cms-block-annotationBanner-selection',
    previewComponent: 'sw-cms-preview-annotationBanner-selection',
    allowedPageTypes: ['product_list','landingpage','page'],
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'full_width',
    },

    slots: {
        annotationBannerSelection: {
            type: 'annotationBannerSelection',
        }
    }
});
