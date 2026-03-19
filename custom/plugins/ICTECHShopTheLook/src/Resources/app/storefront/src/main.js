import ShopLookSlider from './js/shop-look-slider';
import './js/shop-the-look';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ict-shop-look-slider').forEach(el => new ShopLookSlider(el));
});
