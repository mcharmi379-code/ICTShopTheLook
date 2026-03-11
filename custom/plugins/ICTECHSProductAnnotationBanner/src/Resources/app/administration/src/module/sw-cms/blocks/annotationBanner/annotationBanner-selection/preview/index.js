import template from './sw-cms-preview-annotationBanner-selection.html.twig';
import './sw-cms-preview-annotationBanner-selection.scss';

export default {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    },
};
