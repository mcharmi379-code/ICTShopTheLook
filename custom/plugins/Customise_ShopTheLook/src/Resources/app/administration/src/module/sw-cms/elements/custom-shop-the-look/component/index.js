const {Component, Application, Mixin} = Shopware;
import template from './sw-cms-el-custom-shop-the-look.html.twig';
import './sw-cms-el-custom-shop-the-look.scss';

Component.register('sw-cms-el-custom-shop-the-look', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
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
        },

        priceTotal() {
            const elemData = this.element.data.products;
            let price = 0;
            if (elemData) {
                if (elemData) {
                    elemData.forEach(function (product) {
                        if (product.price) {
                            price = price + product.price[0].gross;
                        }
                    });
                }
            }
            return price;
        },

        itemCss() {
            return {
                '--products-width': this.element.config.productsWidth.value
            }
        },
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('custom-shop-the-look');
            this.initElementData('custom-shop-the-look');
        },

        pointerPositionCss(id) {
            try {
                this.element.config.productMediaHotspots.value[id].top = parseInt(this.element.config.productMediaHotspots.value[id].top);
                this.element.config.productMediaHotspots.value[id].left = parseInt(this.element.config.productMediaHotspots.value[id].left);

                return {
                    top: this.element.config.productMediaHotspots.value[id].top + '%',
                    left: this.element.config.productMediaHotspots.value[id].left + '%'
                }
            } catch (exception) {
                return {};
            }
        },
    },

    filters: {
        numberFormat: function (value) {
            return new Intl.NumberFormat('de-DE', {style: 'currency', currency: 'EUR'}).format(value)
        }
    }
});
