import template from './sw-cms-el-ict-shop-the-look.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-ict-shop-the-look', {
    template,
    mixins: [Mixin.getByName('cms-element')],
    computed: {
        lookImageUrl() {
            return this.element?.config?.lookImage?.value?.url || null;
        },
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    },
    created() {
        this.initElementConfig('ict-shop-the-look');
    }
});
