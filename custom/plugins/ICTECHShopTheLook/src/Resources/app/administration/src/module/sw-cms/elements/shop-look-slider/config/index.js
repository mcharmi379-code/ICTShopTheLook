import template from './sw-cms-el-config-ict-shop-look-slider.html.twig';
import './sw-cms-el-config-ict-shop-look-slider.scss';

const { Component, Mixin } = Shopware;
const { moveItem, object: { cloneDeep } } = Shopware.Utils;
const Criteria = Shopware.Data.Criteria;

Component.register('sw-cms-el-config-ict-shop-look-slider', {
    template,

    inject: ['repositoryFactory'],

    emits: ['element-update'],

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            mediaModalIsOpen: false,
            entity: this.element,
            mediaItems: []
        };
    },

    computed: {
        uploadTag() {
            return `cms-element-media-config-${this.element.id}`;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        defaultFolderName() {
            return this.cmsPageState.pageEntityName;
        },

        items() {
            return this.element.config?.sliderItems?.value || [];
        },

        speedDefault() {
            return 300;
        },

        autoplayTimeoutDefault() {
            return 5000;
        },

        navigationArrowsValueOptions() {
            return [
                { value: 'none', label: this.$t('sw-cms.elements.imageSlider.config.label.navigationPositionNone') },
                { value: 'inside', label: this.$t('sw-cms.elements.imageSlider.config.label.navigationPositionInside') },
                { value: 'outside', label: this.$t('sw-cms.elements.imageSlider.config.label.navigationPositionOutside') }
            ];
        },

        navigationDotsValueOptions() {
            return [
                { value: 'none', label: this.$t('sw-cms.elements.imageSlider.config.label.navigationPositionNone') },
                { value: 'inside', label: this.$t('sw-cms.elements.imageSlider.config.label.navigationPositionInside') },
                { value: 'outside', label: this.$t('sw-cms.elements.imageSlider.config.label.navigationPositionOutside') }
            ];
        }
    },

    created() {
        this.initElementConfig('ict-shop-look-slider');
        this.initSliderItems();
    },

    methods: {
        async initSliderItems() {
            if (this.element.config.sliderItems.value.length > 0) {
                const mediaIds = this.element.config.sliderItems.value.map(item => item.mediaId);
                const criteria = new Criteria(1, 25);
                criteria.setIds(mediaIds);
                const searchResult = await this.mediaRepository.search(criteria);
                this.mediaItems = mediaIds.map(id => searchResult.get(id)).filter(item => item !== null);
            }
        },

        async onImageUpload(mediaItem) {
            const resolvedMediaItem = await this.getMediaItem(mediaItem);
            if (!resolvedMediaItem) return;

            const sliderItems = this.element.config.sliderItems;
            if (sliderItems.source === 'default') {
                sliderItems.value = [];
                sliderItems.source = 'static';
            }

            sliderItems.value.push({
                mediaUrl: resolvedMediaItem.url,
                mediaId: resolvedMediaItem.id,
                url: null,
                newTab: false
            });

            this.mediaItems.push(resolvedMediaItem);
            this.updateMediaDataValue();
            this.emitUpdateEl();
        },

        async getMediaItem(mediaItem) {
            return mediaItem?.targetId ? this.mediaRepository.get(mediaItem.targetId) : mediaItem;
        },

        onItemRemove(mediaItem, index) {
            this.element.config.sliderItems.value = this.element.config.sliderItems.value.filter((item, i) => 
                item.mediaId !== mediaItem.id || i !== index
            );
            this.mediaItems = this.mediaItems.filter((item, i) => item.id !== mediaItem.id || i !== index);
            this.updateMediaDataValue();
            this.emitUpdateEl();
        },

        onCloseMediaModal() {
            this.mediaModalIsOpen = false;
        },

        onMediaSelectionChange(mediaItems) {
            const sliderItems = this.element.config.sliderItems;
            if (sliderItems.source === 'default') {
                sliderItems.value = [];
                sliderItems.source = 'static';
            }

            mediaItems.forEach(item => {
                sliderItems.value.push({
                    mediaUrl: item.url,
                    mediaId: item.id,
                    url: null,
                    newTab: false
                });
            });

            this.mediaItems.push(...mediaItems);
            this.updateMediaDataValue();
            this.emitUpdateEl();
        },

        onItemSort(dragData, dropData) {
            moveItem(this.mediaItems, dragData.position, dropData.position);
            moveItem(this.element.config.sliderItems.value, dragData.position, dropData.position);
            this.updateMediaDataValue();
            this.emitUpdateEl();
        },

        updateMediaDataValue() {
            if (this.element.config.sliderItems.value) {
                const sliderItems = cloneDeep(this.element.config.sliderItems.value);
                sliderItems.forEach(sliderItem => {
                    this.mediaItems.forEach(mediaItem => {
                        if (sliderItem.mediaId === mediaItem.id) {
                            sliderItem.media = mediaItem;
                        }
                    });
                });
                this.element.data = { sliderItems };
            }
        },

        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        },

        emitUpdateEl() {
            this.$emit('element-update', this.element);
        },

        onChangeAutoSlide(value) {
            if (!value) {
                this.element.config.autoplayTimeout.value = this.autoplayTimeoutDefault;
                this.element.config.speed.value = this.speedDefault;
            }
            this.emitUpdateEl();
        }
    }
});