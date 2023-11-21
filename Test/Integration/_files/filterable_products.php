<?php

require __DIR__ . '/multiselect_attribute.php';

$defaultStoreId = 1;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$options = $objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class);
$cacheManager = $objectManager->get(\Magento\Framework\App\Cache\Manager::class);

$options->setAttributeFilter($attribute->getId());
$optionIds = $options->getAllIds();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(444)
    ->setAttributeSetId(4)
    ->setName('Product 1')
    ->setSku('product_1')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setDescription('Description')
    ->setMultiselectAttribute([$optionIds[0]])
    ->save();

$product->reindex();
$product->priceReindexCallback();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(445)
    ->setAttributeSetId(4)
    ->setName('Product 2')
    ->setSku('product_2')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setCanSaveCustomOptions(true)
    ->setDescription('Description')
    ->setMultiselectAttribute([$optionIds[1], $optionIds[2]])
    ->save();

$product->reindex();
$product->priceReindexCallback();

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category
    ->setId(333)
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Main category')
    ->setParentId(2)
    ->setPath('1/2/333')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy("price")
    ->setPostedProducts([
        444 => 10,
        445 => 11
    ])
    ->save()
    ->reindex();

$rewriteResource = $objectManager->create(\Magento\UrlRewrite\Model\ResourceModel\UrlRewrite::class);

$rewrite = $objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class);
$rewrite
    ->setEntityType('category')
    ->setEntityId(333)
    ->setRequestPath('test-category')
    ->setTargetPath('catalog/category/view/id/333')
    ->setRedirectType(0)
    ->setStoreId($defaultStoreId);

$rewriteResource->save($rewrite);

$cacheManager->clean($cacheManager->getAvailableTypes());
