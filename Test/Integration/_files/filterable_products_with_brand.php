<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);

if ($moduleManager->isEnabled('MageSuite_BrandManagement')) {
    $brandRepository = $objectManager->create(\MageSuite\BrandManagement\Api\BrandsRepositoryInterface::class);
    $options = $objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class);

    require __DIR__ . '/multiselect_attribute.php';

    $options->setAttributeFilter($attribute->getId());
    $optionIds = $options->getAllIds();

    $brand = $objectManager->create(\MageSuite\BrandManagement\Model\Brands::class);
    $brand
        ->setEntityId(40)
        ->setStoreId(0)
        ->setUrlKey('test_brand')
        ->setBrandName('test_brand')
        ->setEnabled(1)
        ->setIsFeatured(1);

    $brandRepository->save($brand);

    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
    $product->isObjectNew(true);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setId(120)
        ->setAttributeSetId(4)
        ->setName('Product without multi option')
        ->setSku('simple0')
        ->setTaxClassId('none')
        ->setOptionsContainer('container1')
        ->setPrice(10)
        ->setWeight(1)
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setWebsiteIds([1])
        ->setCategoryIds([])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1])
        ->setBrand(40)
        ->save();

    $product->reindex();
    $product->priceReindexCallback();

    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
    $product->isObjectNew(true);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setId(121)
        ->setAttributeSetId(4)
        ->setName('Product with option 1 and 2')
        ->setSku('simple1')
        ->setTaxClassId('none')
        ->setOptionsContainer('container1')
        ->setPrice(10)
        ->setWeight(1)
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setWebsiteIds([1])
        ->setCategoryIds([])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1])
        ->setBrand(40)
        ->setMultiselectAttribute([$optionIds[1], $optionIds[2]])
        ->save();

    $product->reindex();
    $product->priceReindexCallback();

    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
    $product->isObjectNew(true);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setId(122)
        ->setAttributeSetId(4)
        ->setName('Product with option 2 only')
        ->setSku('simple2')
        ->setTaxClassId('none')
        ->setOptionsContainer('container1')
        ->setPrice(20)
        ->setWeight(1)
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setWebsiteIds([1])
        ->setCategoryIds([])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1])
        ->setBrand(40)
        ->setMultiselectAttribute([$optionIds[1]])
        ->save();

    $product->reindex();
    $product->priceReindexCallback();

    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
    $product->isObjectNew(true);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setId(123)
        ->setAttributeSetId(4)
        ->setName('Product with option 1 only')
        ->setSku('simple3')
        ->setTaxClassId('none')
        ->setPrice(30)
        ->setWeight(1)
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setWebsiteIds([1])
        ->setCategoryIds([])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1])
        ->setBrand(40)
        ->setMultiselectAttribute([$optionIds[2]])
        ->save();

    $product->reindex();
    $product->priceReindexCallback();
}
