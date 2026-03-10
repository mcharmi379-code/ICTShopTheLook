import template from './sw-cms-el-config-ict-shop-the-look.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Component.register('sw-cms-el-config-ict-shop-the-look', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element')
    ],

    emits: ['element-update'], 
    

    data() {
        return {
            mediaModalOpen: false,
            products: null
        };
    },

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        },

        productCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('cover');
            return criteria;
        },

        productContext() {
            return { ...Shopware.Context.api, inheritance: true };
        },

        layoutOptions() {
            return [
                { value: 'image-products', label: 'Image → Products' },
                { value: 'products-image', label: 'Products → Image' },
                { value: 'only-image', label: 'Only Image' },
                { value: 'only-products', label: 'Only Products' }
            ];
        }
    },

    created() {
        this.initElementConfig('ict-shop-the-look');

        this.products = new EntityCollection(
            this.productRepository.route,
            this.productRepository.entityName,
            this.productContext
        );

        if (this.element.config.products.value?.length) {
            const criteria = new Criteria();
            criteria.setIds(this.element.config.products.value);

            this.productRepository.search(criteria, this.productContext).then((result) => {
                this.products = result;
            });
        }
    },

    methods: {

        setProductCollection(productCollection) {
            this.products = productCollection;

            this.element.config.products.value = productCollection.getIds();

            this.$emit('element-update', this.element);
        },

        onMediaUploadOpen() {
            this.mediaModalOpen = true;
        },

        onMediaModalClose() {
            this.mediaModalOpen = false;
        },

        onMediaSelect(selection) {
            this.element.config.lookImage.value = selection[0];
            this.mediaModalOpen = false;

            this.$emit('element-update', this.element);
        },

        onRemoveImage() {
            this.element.config.lookImage.value = null;

            this.$emit('element-update', this.element);
        },
        addHotspot(event) {
            const rect = event.target.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            
            if (!this.element.config.hotspots.value) {
                this.element.config.hotspots.value = [];
            }
            
            this.element.config.hotspots.value.push({ x, y, productIndex: this.element.config.hotspots.value.length });
            this.$emit('element-update', this.element);
        },
        removeHotspot(index) {
            this.element.config.hotspots.value.splice(index, 1);
            this.$emit('element-update', this.element);
        }
    }
});