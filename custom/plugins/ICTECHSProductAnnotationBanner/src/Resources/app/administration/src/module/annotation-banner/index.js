import './page/annotation-banner-list';
import './page/annotation-banner-detail';
import './page/annotation-banner-create';
import './component/annotation-banner-product';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const {Module} = Shopware;

Module.register('annotation-banner', {
    type: 'plugin',
    name: 'ProductAnnotationBanner',
    title: 'annotation_banner.general.title',
    description: 'annotation_banner.general.description',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#002eff',
    icon: 'regular-plug',
    inject: ['systemConfigApiService'],
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'annotation-banner-list',
            path: 'list'
        },
        detail: {
            component: 'annotation-banner-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'annotation.banner.list'
            },
        },
        create: {
            component: 'annotation-banner-create',
            path: 'create',
            meta: {
                parentPath: 'annotation.banner.list'
            }
        }
    },

    navigation: [{
        label: 'annotation_banner.general.mainMenuItemGeneral',
        color: '#189eff',
        path: 'annotation.banner.list',
        icon: 'default-text-code',
        parent: 'sw-catalogue',
        position: 100
    }]
});




