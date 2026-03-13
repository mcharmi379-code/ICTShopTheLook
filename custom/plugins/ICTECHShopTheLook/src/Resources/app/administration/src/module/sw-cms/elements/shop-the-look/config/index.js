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
            console.log('Image dimension changed to:', newValue);
            console.log('Current config before change:', JSON.stringify({
                imageDimension: this.element.config.imageDimension?.value,
                customWidth: this.element.config.customWidth?.value,
                customHeight: this.element.config.customHeight?.value
            }));
            
            this.cleanupDimensionConfig();
            
            console.log('Config after change:', JSON.stringify({
                imageDimension: this.element.config.imageDimension?.value,
                customWidth: this.element.config.customWidth?.value,
                customHeight: this.element.config.customHeight?.value
            }));
            
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
        console.log('Shop The Look - Element Created:', this.element);
        console.log('Shop The Look - Config:', this.element.config);
        this.loadHotspots();
    },

    methods: {
        onElementUpdate() {
            console.log('=== ELEMENT UPDATE TRIGGERED ===');
            console.log('Current hotspots:', JSON.stringify(this.hotspots));
            console.log('Element config:', JSON.stringify(this.element.config));
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
                console.log('Cleared custom dimensions for predefined size:', imageDimension);
            }
        },

        onElementUpdate() {
            // Clean up dimension config before saving
            this.cleanupDimensionConfig();
            
            console.log('Final config before save:', JSON.stringify({
                imageDimension: this.element.config.imageDimension?.value,
                customWidth: this.element.config.customWidth?.value,
                customHeight: this.element.config.customHeight?.value
            }));
            
            this.$emit('element-update', this.element);
        },

        loadHotspots() {
            if (this.element.config.hotspots?.value) {
                this.hotspots = this.element.config.hotspots.value;
                console.log('Shop The Look - Hotspots Loaded:', this.hotspots);
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
            console.log('Shop The Look - Hotspot Added:', newHotspot);
        },

        removeHotspot(index) {
            console.log('Shop The Look - Removing Hotspot at Index:', index);
            this.hotspots.splice(index, 1);
            this.saveHotspots();
        },

        onHotspotChange(index) {
            console.log('Shop The Look - Hotspot Changed:', this.hotspots[index]);
            this.saveHotspots();
        },

        saveHotspots() {
            this.element.config.hotspots.value = this.hotspots;
            console.log('Shop The Look - All Hotspots Saved:', this.hotspots);
            console.log('Shop The Look - Full Config Being Saved:', JSON.stringify({
                imageDimension: this.element.config.imageDimension?.value,
                customWidth: this.element.config.customWidth?.value,
                customHeight: this.element.config.customHeight?.value,
                layout: this.element.config.layout?.value,
                hotspots: this.element.config.hotspots?.value
            }));
            this.onElementUpdate();
        },

        generateId() {
            return 'hotspot_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        onMediaSelect(selection) {
            console.log('Shop The Look - Media Selected:', selection);
            this.element.config.lookImage.value = selection[0];
            this.mediaModalOpen = false;
            console.log('Shop The Look - Look Image Saved:', this.element.config.lookImage.value);
            this.onElementUpdate();
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
            this.onElementUpdate();
        },

        onMediaUploadOpen() {
            this.mediaModalOpen = true;
        },

        onMediaModalClose() {
            this.mediaModalOpen = false;
        },

        onRemoveImage() {
            console.log('Shop The Look - Image Removed');
            this.element.config.lookImage.value = null;
            console.log('Shop The Look - Look Image After Remove:', this.element.config.lookImage.value);
            this.onElementUpdate();
        }
    }
});