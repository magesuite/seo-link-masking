<?php

require __DIR__ . '/multiselect_attribute_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$categoryIds = [333];

foreach ($categoryIds as $categoryId) {
    $category = $objectManager->create(\Magento\Catalog\Model\Category::class);
    $category->load($categoryId);
    if ($category->getId()) {
        $category->delete();
    }
}

$urlRewriteCollection = $objectManager->create(\Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection::class);
$urlRewriteTargetPaths = ['catalog/category/view/id/333'];

$collection = $urlRewriteCollection
    ->addFieldToFilter('target_path', $urlRewriteTargetPaths)
    ->load()
    ->walk('delete');

$productIds = [444,445];

foreach ($productIds as $productId) {
    $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
    $product->load($categoryId);
    if ($product->getId()) {
        $product->delete();
    }
}
