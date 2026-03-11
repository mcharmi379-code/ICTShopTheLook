import template from './sw-cms-el-config-annotationBannerSelection.html.twig';
import './sw-cms-el-config-annotationBannerSelection.scss';

const {Mixin} = Shopware;
const Criteria = Shopware.Data.Criteria;
export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    data() {
        return {
            test: null,
        }
    },

    computed: {
        productAnnotationBannerRepository() {
            return this.repositoryFactory.create('product_annotation_banner');
        },

        productAnnotationBannerCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('active', true));
            criteria.addFilter(Criteria.multi(
                'OR',
                [
                    Criteria.contains('productAnnotationBanners.selectType', 'product'),
                    Criteria.contains('productAnnotationBanners.selectType', 'category'),
                    Criteria.contains('productAnnotationBanners.selectType', 'manufacture'),
                ],
            ));
            return criteria;
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

        onAnnotationBannerChange(productId) {
            if (!productId) {
                this.element.config.productAnnotationBanner.value = null;
                this.element.config.productAnnotationBannerUrl.value = '/administration/static/img/cms/preview_mountain_large.jpg';
                return;
            }
            const getMediaCriteria = new Criteria(this.page, this.limit);
            getMediaCriteria.addAssociation('media');
            getMediaCriteria.addFilter(Criteria.equals('id', this.element.config.productAnnotationBanner.value))
            this.productAnnotationBannerRepository.search(getMediaCriteria, Shopware.Context.api)
                .then((result) => {
                    this.element.config.productAnnotationBannerUrl.value = result[0].media.url;
                });
        },
    },

}
