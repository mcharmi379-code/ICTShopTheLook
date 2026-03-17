import ShopLookSlider from './js/shop-look-slider';
import './js/shop-the-look';

console.log('[ICTECHShopTheLook] main.js loaded');

document.addEventListener('DOMContentLoaded', () => {
    console.log('[ICTECHShopTheLook] DOMContentLoaded fired');
    const sliders = document.querySelectorAll('.ict-shop-look-slider');
    console.log('[ICTECHShopTheLook] Found sliders:', sliders.length);
    sliders.forEach(el => new ShopLookSlider(el));
});
