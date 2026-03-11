import template from './sw-cms-el-annotationBannerSelection.html.twig';
import './sw-cms-el-annotationBannerSelection.scss';

const {Mixin} = Shopware;
const {Criteria} = Shopware.Data;

export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    computed: {
        assetFilter(){
            return Shopware.Filter.getByName('asset');
        },
        productAnnotationBannerRepository() {
            return this.repositoryFactory.create('product_annotation_banner');
        },

        LayoutTypeValue() {
            return this.element.config.LayoutType.value;
        },
        getMediaImage() {
            if (this.element.config.productAnnotationBanner.value) {
                const getMediaCriteria = new Criteria(this.page, this.limit);
                getMediaCriteria.addAssociation('media');
                getMediaCriteria.addFilter(Criteria.equals('id', this.element.config.productAnnotationBanner.value))
                this.productAnnotationBannerRepository.search(getMediaCriteria, Shopware.Context.api)
                    .then((result) => {
                        this.element.config.productAnnotationBannerUrl.value = result[0].media.url;
                    });
            } else {
                    this.element.config.productAnnotationBannerUrl.value = this.assetFilter(`administration/administration/static/img/cms/preview_mountain_large.jpg`);
            }

            return this.element.config.productAnnotationBannerUrl.value;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('annotationBannerSelection');
        },

        onBlur(content) {
            this.emitChanges(content);
        },

        onInput(content) {
            this.emitChanges(content);
        },

        emitChanges(content) {
            if (content !== this.element.config.annotationBannerText.value) {
                this.element.config.annotationBannerText.value = content;
            }
        },
    },

}
