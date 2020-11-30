<?php

require __DIR__ . '/filterable_products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\Store $secondStore */
$secondStore = $objectManager->create(\Magento\Store\Model\Store::class);
$secondStore->load('fixture_second_store');
$secondStoreId = $secondStore->getId();
$storeID = 1;

/** @var \Magento\UrlRewrite\Model\UrlRewrite Translated */
$rewriteTranslated = $objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class);
$rewriteTranslated
    ->setEntityType('category')
    ->setEntityId(333)
    ->setRequestPath('test-category-translated')
    ->setTargetPath('catalog/category/view/id/333')
    ->setRedirectType(0)
    ->setStoreId($secondStoreId);

$rewriteResource->save($rewriteTranslated);

/** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->create(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);

/** @var $attribute \Magento\Catalog\Api\Data\ProductAttributeInterface */
$attribute = $productAttributeRepository->get('multiselect_attribute');

$options = $attribute->getOptions();

foreach ($options as $option){
    $optionLabel = new \Magento\Framework\DataObject(
        ['store_id' => $storeID, 'label' => $option->getLabel()]
    );
    $optionLabelTranslated = new \Magento\Framework\DataObject(
        ['store_id' => $secondStoreId, 'label' => $option->getLabel()." translated"]
    );

    $option->setStoreLabels([$optionLabel, $optionLabelTranslated]);
}
$attribute->setOptions($options);
$productAttributeRepository->save($attribute);



