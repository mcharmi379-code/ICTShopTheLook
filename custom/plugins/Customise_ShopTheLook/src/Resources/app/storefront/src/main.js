import CustomProductBuyListPlugin from './product-buy-list/product-buy-list.plugin';

const PluginManager = window.PluginManager;

PluginManager.register('CustomProductBuyList', CustomProductBuyListPlugin, '[data-custom-product-buy-list]');