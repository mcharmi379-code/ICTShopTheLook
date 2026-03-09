const Application = Shopware.Application;
import './component';
import './config';
const Criteria = Shopware.Data.Criteria;
const criteria = new Criteria();
criteria.addAssociation('options.group');
criteria.addAssociation('cover');

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlCmsShopTheLook',
    icon: 'default-shopping-cart',
    color: '#eebecd',
    previewComponent: true,
    name: 'custom-shop-the-look',
    label: 'Custom Shop The Look Element',
    component: 'sw-cms-el-custom-shop-the-look',
    configComponent: 'sw-cms-el-config-custom-shop-the-look',
    defaultConfig: {
        products: {
            source: 'static',
            value: [],
            required: true,
            entity: {
                name: 'product',
                criteria: criteria
            }
        },
        media: {
            source: 'static',
            value: null,
            required: false,
            entity: {
                name: 'media',
                criteria: new Criteria()
            }
        },
        videoActive: {
            source: 'static',
            value: false
        },
        custom1: {
            source: 'static',
            value: ""
        },
        custom2: {
            source: 'static',
            value: ""
        },
        custom3: {
            source: 'static',
            value: ""
        },
        custom4: {
            source: 'static',
            value: ""
        },
        hoverCardHideButton: {
            source: 'static',
            value: false
        },
        productMediaHotspots: {
            source: 'static',
            value: {}
        },
        style: {
            source: 'static',
            value: 'image-products'
        },
        productsWidth: {
            source: 'static',
            value: '380px'
        },
        enableList: {
            source: 'static',
            value: true
        },
        enablePrices: {
            source: 'static',
            value: true
        },
        enableAddToCart: {
            source: 'static',
            value: true
        },
        enableVariantSwitch: {
            source: 'static',
            value: true
        },
        enableAddToCartAll: {
            source: 'static',
            value: true
        },
        enableAddToCartSingle: {
            source: 'static',
            value: true
        },
        enableDirectUrl: {
            source: 'static',
            value: false
        },
        enableAnimatedHotspots: {
            source: 'static',
            value: true
        },
        emptyText: {
            source: 'static',
            value: 'No products available'
        }
    },
    collect: function collect(elem) {
        const context = Object.assign(
            {},
            Shopware.Context.api,
            { inheritance: true },
        );

        const criteriaList = {};

        Object.keys(elem.config).forEach((configKey) => {
            if (elem.config[configKey].source === 'mapped') {
                return;
            }

            if (elem.config[configKey].source === 'product_stream') {
                return;
            }

            const entity = elem.config[configKey].entity;

            if (entity && elem.config[configKey].value) {
                const entityKey = entity.name;
                const entityData = getEntityData(elem, configKey);

                entityData.searchCriteria.setIds(entityData.value);
                entityData.context = context;

                criteriaList[`entity-${entityKey}`] = entityData;
            }
        });

        return criteriaList;
    },
});

function getEntityData(element, configKey) {
    const entity = element.config[configKey].entity;
    const configValue = element.config[configKey].value;
    let entityData = {};

    // if multiple entities are given in a slot
    if (Array.isArray(configValue)) {
        const entityIds = [];

        if (configValue.length && configValue[0].mediaId) {
            configValue.forEach((val) => {
                entityIds.push(val.mediaId);
            });
        } else {
            entityIds.push(...configValue);
        }

        entityData = {
            value: entityIds,
            key: configKey,
            ...entity,
        };
    } else {
        entityData = {
            value: [configValue],
            key: configKey,
            ...entity,
        };
    }

    entityData.searchCriteria = entity.criteria ? entity.criteria : new Criteria();

    return entityData;
}
