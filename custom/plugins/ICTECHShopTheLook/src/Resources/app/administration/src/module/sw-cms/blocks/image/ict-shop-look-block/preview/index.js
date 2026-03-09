import template from './sw-cms-preview-ict-shop-look-block.html.twig';
import './sw-cms-preview-ict-shop-look-block.scss';

Shopware.Component.register('sw-cms-preview-ict-shop-look-block', {
    template,
    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
