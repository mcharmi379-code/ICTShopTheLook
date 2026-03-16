import template from './sw-cms-el-config-ict-shop-the-look.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

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
            hotspots: []
        };
    },

    watch: {
        'element.config.imageDimension.value'(newValue) {
       
           
            
            this.cleanupDimensionConfig();

            
            this.onElementUpdate();
        }
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        uploadTag() {
            return `cms-element-media-config-${this.element.id}`;
        },

        defaultFolderName() {
            return this.cmsPageState._entityName;
        },

        productCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('cover');
            // Only show parent products (not variants)
            criteria.addFilter(Criteria.equals('parentId', null));
            return criteria;
        },

        productContext() {
            return { ...Shopware.Context.api, inheritance: true };
        },

        imageDimensionOptions() {
            return [
                { value: '90x90', label: '90 x 90' },
                { value: '120x120', label: '120 x 120' },
                { value: '150x150', label: '150 x 150' },
                { value: '200x200', label: '200 x 200' },
                { value: '300x300', label: '300 x 300' },
                { value: '400x400', label: '400 x 400' },
                { value: '500x500', label: '500 x 500' },
                { value: 'custom', label: 'Custom' }
            ];
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
      
        this.loadHotspots();
    },

    methods: {
        onElementUpdate() {
          
            this.$emit('element-update', this.element);
        },

        cleanupDimensionConfig() {
            const imageDimension = this.element.config.imageDimension?.value;
            
            if (imageDimension !== 'custom') {
                // Clear custom dimensions when using predefined size
                if (this.element.config.customWidth) {
                    this.element.config.customWidth.value = null;
                }
                if (this.element.config.customHeight) {
                    this.element.config.customHeight.value = null;
                }
               
            }
        },

        onElementUpdate() {
            // Clean up dimension config before saving
            this.cleanupDimensionConfig();
            
            
            
            this.$emit('element-update', this.element);
        },

        loadHotspots() {
            if (this.element.config.hotspots?.value) {
                this.hotspots = this.element.config.hotspots.value;
               
            } else {
                this.hotspots = [];
            }
        },

        addHotspot() {
            const newHotspot = {
                id: this.generateId(),
                xPosition: 50,
                yPosition: 50,
                productId: null
            };
            this.hotspots.push(newHotspot);
            this.saveHotspots();
        },

        removeHotspot(index) {
            this.hotspots.splice(index, 1);
            this.saveHotspots();
        },

        async onHotspotProductChange(index) {
            const hotspot = this.hotspots[index];
            if (hotspot.productId) {
                const criteria = new Criteria(1, 1);
                criteria.addAssociation('cover.media');
                const product = await this.productRepository.get(hotspot.productId, Shopware.Context.api, criteria);
                if (product) {
                    hotspot.productName = product.translated?.name || product.name || '';
                    hotspot.productCoverUrl = product.cover?.media?.url || null;
                }
            } else {
                hotspot.productName = null;
                hotspot.productCoverUrl = null;
            }
            this.saveHotspots();
        },

        onHotspotChange(index) {
            this.saveHotspots();
        },

        saveHotspots() {
            this.element.config.hotspots.value = this.hotspots;
           
            this.onElementUpdate();
        },

        generateId() {
            return 'hotspot_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        onMediaSelect(selection) {
            this.element.config.lookImage.value = selection[0];
            this.mediaModalOpen = false;
            this.onElementUpdate();
        },

        onMediaUpload(mediaItem) {
            this.setMediaItem({ targetId: mediaItem.targetId });
        },

        async setMediaItem({ targetId }) {
            const media = await this.mediaRepository.get(targetId);
            this.element.config.lookImage.value = media;
            this.onElementUpdate();
        },

        onMediaUploadOpen() {
            this.mediaModalOpen = true;
        },

        onMediaModalClose() {
            this.mediaModalOpen = false;
        },

        onRemoveImage() {
            this.element.config.lookImage.value = null;
            this.onElementUpdate();
        }
    }
});