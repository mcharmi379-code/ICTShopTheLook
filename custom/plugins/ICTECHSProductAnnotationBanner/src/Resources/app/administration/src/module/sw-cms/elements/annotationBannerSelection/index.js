/**
 * @private
 * @package content
 */
Shopware.Component.register('sw-cms-el-preview-annotationBannerSelection', () => import('./preview'));
/**
 * @private
 * @package content
 */
Shopware.Component.register('sw-cms-el-config-annotationBannerSelection', () => import('./config'));
/**
 * @private
 * @package content
 */
Shopware.Component.register('sw-cms-el-annotationBannerSelection', () => import('./component'));


Shopware.Service('cmsService').registerCmsElement({
    name: 'annotationBannerSelection',
    label: 'cms-annotation-banner.general.mainMenuItemGeneral',
    component: 'sw-cms-el-annotationBannerSelection',
    configComponent: 'sw-cms-el-config-annotationBannerSelection',
    previewComponent: 'sw-cms-el-preview-annotationBannerSelection',
    defaultConfig: {
        productAnnotationBanner: {
            source: 'static',
            value: null,
            required: false,
            entity: {
                name: 'product_annotation_banner',
            },
        },
        LayoutType: {
            source: 'static',
            value: 'imageWithText',
        },
        productAnnotationBannerUrl: {
            source: 'static',
            value: '/administration/static/img/cms/preview_mountain_large.jpg',
        },
        annotationBannerText: {
            source: 'static',
            value: `
                <h2>Lorem Ipsum dolor sit amet</h2>
                <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, 
                sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, 
                sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. 
                Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. 
                Lorem ipsum dolor sit amet, consetetur sadipscing elitr, 
                sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. 
                At vero eos et accusam et justo duo dolores et ea rebum. 
                Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
            `.trim(),
        }
    },
});
