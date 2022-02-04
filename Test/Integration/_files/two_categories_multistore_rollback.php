<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);

foreach ($collection->addAttributeToFilter('entity_id', ['in' => [444, 555]]) as $category) {
    /** @var \Magento\Catalog\Model\Category $category */
    $category->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
