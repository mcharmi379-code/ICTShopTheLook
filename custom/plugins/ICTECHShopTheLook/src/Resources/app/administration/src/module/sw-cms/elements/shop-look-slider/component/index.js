import template from './sw-cms-el-ict-shop-look-slider.html.twig';

const { Component, Mixin, Filter } = Shopware;

Component.register('sw-cms-el-ict-shop-look-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            sliderPos: 0
        };
    },

    computed: {

        sliderItems() {
            const configItems = this.element?.config?.sliderItems?.value;
            if (configItems && configItems.length) {
                return configItems;
            }
            return [];
        },

        visibleItems() {

            if (!this.sliderItems.length) {
                return new Array(6).fill(null); // show default preview images
            }

            const start = this.sliderPos;
            const end = start + 6;

            return this.sliderItems.slice(start, end);

        },

        assetFilter() {
            return Filter.getByName('asset');
        }

    },

    created() {

        this.initElementConfig('ict-shop-look-slider');
        this.initElementData('ict-shop-look-slider');

    },

    methods: {

        nextSlide() {

            if (this.sliderPos + 6 < this.sliderItems.length) {
                this.sliderPos++;
            }

        },

        prevSlide() {

            if (this.sliderPos > 0) {
                this.sliderPos--;
            }

        }

    }
});