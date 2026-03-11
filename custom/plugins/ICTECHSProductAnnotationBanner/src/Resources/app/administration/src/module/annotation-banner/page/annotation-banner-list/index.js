import template from './annotation-banner-list.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('annotation-banner-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            annotationBanner: null,
            isLoading: false,
            total: 0,
            pluginStatus: true
        };
    },

    created() {
        this.getAnnotationBanners();
    },

    computed: {
        annotationBannerRepository() {
            return this.repositoryFactory.create('product_annotation_banner');
        },
        columns() {
            return this.getColumns();
        }
    },

    methods: {
        getAnnotationBanners() {
            this.isLoading = true;
            this.annotationBannerRepository.search(new Criteria(), Shopware.Context.api).then((result) => {
                this.annotationBanner = result;
            });
            this.isLoading = false;
        },

        getColumns() {
            return [{
                property: 'name',
                dataIndex: 'name',
                label: this.$tc('annotation_banner.list.columnName.name'),
                routerLink: 'annotation.banner.detail',
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'bannerImage',
                dataIndex: 'media',
                label: this.$tc('annotation_banner.list.columnName.bannerImage'),
                sortable: false,
                allowResize: true,
            }, {
                property: 'active',
                dataIndex: 'active',
                label: this.$tc('annotation_banner.detail.field.activation'),
                inlineEdit: 'boolean',
                allowResize: true,
            }];
        },

        updateTotal({total}) {
            this.total = total;
        },
    }
});
