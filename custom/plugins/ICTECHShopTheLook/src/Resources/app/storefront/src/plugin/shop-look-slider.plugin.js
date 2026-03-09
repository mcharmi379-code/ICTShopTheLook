import Plugin from 'src/plugin-system/plugin.class';
import { tns } from 'tiny-slider/src/tiny-slider';

export default class ShopLookSlider extends Plugin {

    init() {

        const container = this.el.querySelector('.ict-slider-container');

        if (!container) {
            return;
        }

        const options = this.options;

        const config = {
            container: container,
            items: 6,
            slideBy: 1,
            gutter: 20,
            controls: options.navigationArrows !== 'none',
            controlsPosition: options.navigationArrows === 'outside' ? 'bottom' : 'top',
            nav: options.navigationDots !== 'none',
            navPosition: options.navigationDots === 'outside' ? 'bottom' : 'top',
            mouseDrag: true,
            speed: options.speed || 300,
            autoplay: options.autoSlide || false,
            autoplayTimeout: options.autoplayTimeout || 5000,
            autoplayButtonOutput: false,

            responsive: {
                1200: { items: 6 },
                992: { items: 4 },
                768: { items: 3 },
                480: { items: 2 }
            }
        };

        tns(config);
    }
}