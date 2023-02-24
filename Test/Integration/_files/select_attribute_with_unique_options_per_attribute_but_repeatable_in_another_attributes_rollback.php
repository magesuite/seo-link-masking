<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$installer = $objectManager->create(\Magento\Catalog\Setup\CategorySetup::class);

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$entityType = $installer->getEntityTypeId('catalog_product');

if ($attribute->loadByCode($entityType, 'first_attribute_with_unique_options_per_attribute')->getAttributeId()) {
    $attribute->delete();
}

if ($attribute->loadByCode($entityType, 'second_attribute_with_unique_options_per_attribute')->getAttributeId()) {
    $attribute->delete();
}
