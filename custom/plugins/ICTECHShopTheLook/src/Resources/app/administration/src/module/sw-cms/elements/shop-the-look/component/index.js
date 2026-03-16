import template from './sw-cms-el-ict-shop-the-look.html.twig';
import './sw-cms-el-ict-shop-the-look.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-ict-shop-the-look', {
    template,
    mixins: [Mixin.getByName('cms-element')],
    computed: {
        lookImageUrl() {
            return this.element?.config?.lookImage?.value?.url || null;
        },
        hotspots() {
            return this.element?.config?.hotspots?.value || [];
        },
        layoutStyle() {
            return this.element?.config?.layoutStyle?.value || 'image-products';
        },
        showImage() {
            return ['image-products', 'products-image', 'only-image'].includes(this.layoutStyle);
        },
        showProducts() {
            return ['image-products', 'products-image', 'only-products'].includes(this.layoutStyle);
        },
        addSingleProduct() {
            return this.element?.config?.addSingleProduct?.value !== false;
        },
        addAllToCart() {
            return this.element?.config?.addAllToCart?.value !== false;
        },
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    },
    created() {
        this.initElementConfig('ict-shop-the-look');
    }
});
