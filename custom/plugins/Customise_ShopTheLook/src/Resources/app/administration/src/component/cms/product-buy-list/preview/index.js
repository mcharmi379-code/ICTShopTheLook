const {Component} = Shopware;
import template from './sw-cms-el-preview-custom-product-buy-list.html.twig';
import './sw-cms-el-preview-custom-product-buy-list.scss';

Component.register('sw-cms-el-preview-custom-product-buy-list', {
    template,
    data() {
        return {
            label: 'sw-cms.elements.custom-product-buy-list.name'
        }
    }
});
