<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);

if ($moduleManager->isEnabled('MageSuite_BrandManagement')) {
    $registry = $objectManager->get(\Magento\Framework\Registry::class);
    $brandRepository = $objectManager->create(\MageSuite\BrandManagement\Api\BrandsRepositoryInterface::class);

    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', true);

    $productIds = [121, 122, 123];

    foreach ($productIds as $productId) {
        $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($productId);
        if ($product->getId()) {
            $product->delete();
        }
    }

    $brandId = 40;

    try {
        $brand = $brandRepository->getById($brandId);

        if ($brand) {
            $brandRepository->delete($brand);
        }
    } catch (\Exception $e) {
        //already removed
    }
}
