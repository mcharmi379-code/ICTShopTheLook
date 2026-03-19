import template from './sw-cms-el-ict-shop-look-slider.html.twig';
import './sw-cms-el-ict-shop-look-slider.scss';

const { Component, Mixin, Filter } = Shopware;

/**
 * Administration preview component for the 'ict-shop-look-slider' CMS element.
 * Renders a paginated preview of the configured slider images inside the
 * CMS page builder canvas (shows 6 items at a time).
 */
Component.register('sw-cms-el-ict-shop-look-slider', {
    template,

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    data() {
        return {
            sliderPos: 0,
        };
    },

    computed: {
        sliderItems() {
            const configItems = this.element?.config?.sliderItems?.value;
            return (configItems && configItems.length) ? configItems : [];
        },

        visibleItems() {
            if (!this.sliderItems.length) {
                return new Array(6).fill(null);
            }
            return this.sliderItems.slice(this.sliderPos, this.sliderPos + 6);
        },

        assetFilter() {
            return Filter.getByName('asset');
        },
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
        },
    },
});
