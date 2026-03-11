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
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return `cms-element-media-config-${this.element.id}`;
        },

        defaultFolderName() {
            return this.cmsPageState._entityName;
        },

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
        console.log('Shop The Look - Element Created:', this.element);
        console.log('Shop The Look - Config:', this.element.config);

        this.products = new EntityCollection(
            this.productRepository.route,
            this.productRepository.entityName,
            this.productContext
        );

        if (this.element.config.products.value?.length) {
            console.log('Shop The Look - Loading Products:', this.element.config.products.value);
            const criteria = new Criteria();
            criteria.setIds(this.element.config.products.value);

            this.productRepository.search(criteria, this.productContext).then((result) => {
                this.products = result;
                console.log('Shop The Look - Products Loaded:', result);
            });
        }
    },

    methods: {

        setProductCollection(productCollection) {
            console.log('Shop The Look - Product Collection Changed:', productCollection);
            this.products = productCollection;

            this.element.config.products.value = productCollection.getIds();
            console.log('Shop The Look - Product IDs Saved:', this.element.config.products.value);

            this.$emit('element-update', this.element);
        },

        onMediaUploadOpen() {
            this.mediaModalOpen = true;
        },

        onMediaModalClose() {
            this.mediaModalOpen = false;
        },

        onMediaSelect(selection) {
            console.log('Shop The Look - Media Selected:', selection);
            this.element.config.lookImage.value = selection[0];
            this.mediaModalOpen = false;
            console.log('Shop The Look - Look Image Saved:', this.element.config.lookImage.value);

            this.$emit('element-update', this.element);
        },

        onMediaUpload(mediaItem) {
            console.log('Shop The Look - Media Uploaded:', mediaItem);
            this.setMediaItem({ targetId: mediaItem.targetId });
        },

        async setMediaItem({ targetId }) {
            console.log('Shop The Look - Setting Media Item:', targetId);
            const media = await this.mediaRepository.get(targetId);
            this.element.config.lookImage.value = media;
            console.log('Shop The Look - Media Item Set:', media);
            this.$emit('element-update', this.element);
        },

        onRemoveImage() {
            console.log('Shop The Look - Image Removed');
            this.element.config.lookImage.value = null;
            console.log('Shop The Look - Look Image After Remove:', this.element.config.lookImage.value);

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
            console.log('Shop The Look - Hotspot Added:', { x, y });
            console.log('Shop The Look - All Hotspots:', this.element.config.hotspots.value);
            this.$emit('element-update', this.element);
        },
        removeHotspot(index) {
            console.log('Shop The Look - Removing Hotspot at Index:', index);
            this.element.config.hotspots.value.splice(index, 1);
            console.log('Shop The Look - Hotspots After Remove:', this.element.config.hotspots.value);
            this.$emit('element-update', this.element);
        }
    }
});