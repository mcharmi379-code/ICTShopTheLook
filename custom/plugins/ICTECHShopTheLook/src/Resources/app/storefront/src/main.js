// Imports
import PluginManager from 'src/plugin-system/plugin.manager';
import ShopLookSlider from './plugin/shop-look-slider.plugin';

// Register plugin
PluginManager.register(
    'ShopLookSlider',
    ShopLookSlider,
    '[data-shop-look-slider]'
);