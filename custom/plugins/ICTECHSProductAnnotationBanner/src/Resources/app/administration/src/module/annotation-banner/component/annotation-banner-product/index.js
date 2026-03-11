import template from './annotation-banner-product.html.twig';
import './annotation-banner-product.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {Context} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('annotation-banner-product', {
    template,

    inject: [
        'repositoryFactory',
        'cacheApiService'
    ],

    props: {
        bannerImageUrl: {
            type: Object,
            required: false,
        }
    },

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            bundleProductElements: null,
            bundleArray: [],
            xAxisPosition: 50,
            yAxisPosition: 50,
            openModelForAddProduct: false,
            variantNames: {},
            htmlAppend: '',
            selectTypeBox: ''
        }
    },

    created() {
        this.getAnnotationBannerAxisData();

        this.productRepository.search(this.variantCriteria, {...Context.api, inheritance: true}).then((variants) => {
            const variantNames = {};
            variants.forEach((variant) => {
                variantNames[variant.id] = variant.translated.name;
            });
            this.variantNames = variantNames;
        });
    },

    computed: {
        columns() {
            return [{
                property: 'selectType',
                dataIndex: 'selectType',
                label: this.$t('annotation_banner.detail.addProductComponent.list.type'),
                allowResize: true,
                primary: true
            },{
                property: 'itemName',
                dataIndex: 'itemName',
                label: this.$t('annotation_banner.detail.addProductComponent.list.item'),
                allowResize: true,
                primary: true
            }, {
                property: 'xAxis',
                dataIndex: 'xAxis',
                label: this.$t('annotation_banner.detail.addProductComponent.list.xAxis'),
                allowResize: true
            }, {
                property: 'yAxis',
                dataIndex: 'yAxis',
                label: this.$t('annotation_banner.detail.addProductComponent.list.yAxis'),
                allowResize: true
            }];
        },
        productAnnotationBannerAxisRepository() {
            return this.repositoryFactory.create('product_annotation_banner_axis');
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        },
        manufacturerRepository() {
            return this.repositoryFactory.create('product_manufacturer');
        },

        productCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('options.group');
            criteria.addFilter(Criteria.equals('active', 1));
            return criteria;
        },
        categoryCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('active', 1));
            return criteria;
        },

        style() {
            return "margin-top: " + this.yAxisPosition + "%;margin-left: " + this.xAxisPosition + "%;";
        },

        variantProductIds() {
            const variantProductIds = [];
            this.bundleArray.forEach((item) => {
                if (!item.product.parentId || item.product.translated.name || item.product.name) {
                    return;
                }
                variantProductIds.push(item.product.id);
            });

            return variantProductIds;
        },

        variantCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.setIds(this.variantProductIds);

            return criteria;
        },

        ...mapPropertyErrors(
            'bundleProductElements', [
                'productId'
            ]
        ),

    },

    methods: {
        getAnnotationBannerAxisData() {
            this.isLoading = true;
            this.htmlAppend = '';
            this.bundleProductElements = this.productAnnotationBannerAxisRepository.create(Shopware.Context.api);
            const customFormCriteria = new Criteria(this.page, this.limit);
            customFormCriteria.addAssociation('product.options.group');
            customFormCriteria.addAssociation('category');
            customFormCriteria.addAssociation('manufacturer');
            if(this.$route.params.id) {                
                customFormCriteria.addFilter(Criteria.equals('productAnnotationBannerId', this.$route.params.id)).addSorting(Criteria.sort('createdAt', 'DESC', true))
            }
            this.productAnnotationBannerAxisRepository.search(customFormCriteria, Shopware.Context.api)
                .then(async (result) => {
                    for (let i = 0; i < result.length; i++) {
                        this.htmlAppend += '<div class="annotation-banner-input-type-pointer-preview">' +
                            '<span type="radio" class="pointer_slide-on_xy_axis" style="margin-top: ' + result[i].yAxis + '%; margin-left: ' + result[i].xAxis + '%;"></span>' +
                            '</div>';
                    }
                    this.bundleArray = result;
                    this.isLoading = false;
                });
            this.cacheApiService.clear();
        },

        onChangeItem(SelectedId) {
            if (SelectedId === null) {
                this.bundleProductElements.productId = null;
                this.bundleProductElements.categoryId = null;
                this.bundleProductElements.productManufacturerId = null;
                return 0;
            }
            if(this.selectTypeBox === 'product') {
                this.bundleProductElements.productId = SelectedId;
            } else if(this.selectTypeBox === 'category') {
                this.bundleProductElements.categoryId = SelectedId;
            } else {
                this.bundleProductElements.productManufacturerId = SelectedId;
            }
        },

        onChangeSelectType(item) {
            this.bundleProductElements.productId = null;
            this.bundleProductElements.categoryId = null;
            this.bundleProductElements.productManufacturerId = null;
        },

        OpenProductForm() {
            this.getAnnotationBannerAxisData();
            this.xAxisPosition = 50;
            this.yAxisPosition = 50;
            this.bundleProductElements.productAnnotationBannerId = this.$route.params.id;
            this.openModelForAddProduct = true;
        },

        onCloseButtonModal() {
            this.openModelForAddProduct = false;
        },

        onUpdateItem(item) {
            const updateCriteria = new Criteria(this.page, this.limit);
            updateCriteria.addAssociation('product');
            updateCriteria.addFilter(Criteria.equals('id', item.id))
            this.productAnnotationBannerAxisRepository.search(updateCriteria, Shopware.Context.api)
                .then((result) => {
                    this.bundleProductElements = result[0];
                    this.xAxisPosition = item.xAxis;
                    this.yAxisPosition = item.yAxis;
                    this.openModelForAddProduct = true;
                });
        },

        onDeleteItem(id) {
            return this.productAnnotationBannerAxisRepository.delete(id).then(() => {
                this.createNotificationSuccess({
                    title: this.$t('annotation_banner.detail.addProductComponent.success.title'),
                    message: this.$t('annotation_banner.detail.addProductComponent.success.deleteMessage')
                });
                this.getAnnotationBannerAxisData();
            });
        },

        xAxisPositionInput(xAxisValue) {
            if (xAxisValue === '') {
                this.createNotificationError({
                    title: this.$tc('annotation_banner.error.xAxisPosition.title'),
                    message: this.$tc('annotation_banner.error.xAxisPosition.message')
                });
            }
        },

        yAxisPositionInput(yAxisValue) {
            if (yAxisValue === '') {
                this.createNotificationError({
                    title: this.$tc('annotation_banner.error.yAxisPosition.title'),
                    message: this.$tc('annotation_banner.error.yAxisPosition.message')
                });
            }
        },

        onConfirmBannerProduct() {
            this.bundleProductElements.xAxis = parseFloat(this.xAxisPosition).toFixed(2);
            this.bundleProductElements.yAxis = parseFloat(this.yAxisPosition).toFixed(2);
            if(this.checkTypeRequirement() === false){
                return;
            }
            this.isLoading = true;

            if(this.bundleProductElements.selectType === "product" || this.bundleProductElements.selectType === "category"){
                this.bundleProductElements.productManufacturerId = null;
            }
            if (this.bundleProductElements.selectType === "product" || this.bundleProductElements.selectType === "manufacturer") {
                this.bundleProductElements.categoryId = null;
            }

            if (this.bundleProductElements.selectType === "category" || this.bundleProductElements.selectType === "manufacturer") {
                this.bundleProductElements.productId = null;
            }
            this.productAnnotationBannerAxisRepository
                .save(this.bundleProductElements, Shopware.Context.api)
                .then((res) => {
                    this.openModelForAddProduct = false;
                    this.isLoading = false;
                    this.createNotificationSuccess({
                        title: this.$t('annotation_banner.detail.addProductComponent.success.title'),
                        message: this.$t('annotation_banner.detail.addProductComponent.success.message')
                    });
                    this.getAnnotationBannerAxisData();
                }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('annotation_banner.detail.addProductComponent.error.title'),
                    message: this.$tc('annotation_banner.detail.addProductComponent.error.message')
                });
            });
        },

        checkTypeRequirement(){
            if(this.bundleProductElements.selectType === 'product'){
                if(this.bundleProductElements.productId == null) {
                    this.createNotificationError({
                        title: this.$tc('annotation_banner.detail.addProductComponent.error.title'),
                        message: this.$tc('annotation_banner.error.productRequired')
                    });
                    return false;
                }
            }
            if(this.bundleProductElements.selectType === 'category'){
                if(this.bundleProductElements.categoryId == null) {
                    this.createNotificationError({
                        title: this.$tc('annotation_banner.detail.addProductComponent.error.title'),
                        message: this.$tc('annotation_banner.error.categoryRequired')
                    });
                    return false;
                }
            }
            if(this.bundleProductElements.selectType === 'manufacturer'){
                if(this.bundleProductElements.productManufacturerId == null) {
                    this.createNotificationError({
                        title: this.$tc('annotation_banner.detail.addProductComponent.error.title'),
                        message: this.$tc('annotation_banner.error.manufactureRequired')
                    });
                    return false;
                }
            }
            return true;
        },
    }
});
