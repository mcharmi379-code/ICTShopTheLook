const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

import template from './sw-cms-el-config-custom-shop-the-look.html.twig';
import './sw-cms-el-config-custom-shop-the-look.scss';

Component.register('sw-cms-el-config-custom-shop-the-look', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    data() {
        return {
            mediaModalIsOpen: false,
            initialFolderId: null,
            snippetPrefix: 'sw-cms.elements.custom-shop-the-look.',
        };
    },

    computed: {
        products() {
            if (this.element.data && this.element.data.products && this.element.data.products.length > 0) {
                return this.element.data.products;
            }

            return null;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return `cms-element-media-config-${this.element.id}`;
        },

        previewSource() {
            if (this.element.data && this.element.data.media && this.element.data.media.id) {
                return this.element.data.media;
            }

            return this.element.config.media.value;
        },

        mediaUrl() {
            const context = Shopware.Context.api;
            const elemData = this.element.data.media;
            if (elemData && elemData.id) {
                return elemData.url;
            }
            if (elemData && elemData.url) {
                return `${context.assetsPath}${elemData.url}`;
            }
            return `${context.assetsPath}/administration/static/img/cms/preview_mountain_large.jpg`;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('custom-shop-the-look');
            this.initElementData('custom-shop-the-look');

            if (typeof this.previewSource === 'string') {
                this.mediaRepository.get(this.previewSource, Shopware.Context.api).then(result => {
                    this.element.data.media = result;
                });
            }
        },

        onProductsChange() {
            const _that = this;

            this.element.config.products.value.forEach(function (id) {
                if (!_that.element.config.productMediaHotspots.value[id]) {
                    _that.element.config.productMediaHotspots.value[id] = {
                        top: 50,
                        left: 50
                    };
                }

                _that.element.config.productMediaHotspots.value[id].top = parseInt(_that.element.config.productMediaHotspots.value[id].top);
                _that.element.config.productMediaHotspots.value[id].left = parseInt(_that.element.config.productMediaHotspots.value[id].left);
            });
        },

        pointerPositionCss(id) {
            try {
                return {
                    top: this.element.config.productMediaHotspots.value[id].top + '%',
                    left: this.element.config.productMediaHotspots.value[id].left + '%'
                }
            } catch (exception) {
                return {};
            }
        },

    }
});
