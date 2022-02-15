<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$installer = $objectManager->create(\Magento\Catalog\Setup\CategorySetup::class);

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$entityType = $installer->getEntityTypeId('catalog_product');

if (!$attribute->loadByCode($entityType, 'multiselect_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'attribute_code' => 'multiselect_attribute',
            'entity_type_id' => $entityType,
            'is_global' => 1,
            'is_visible' => 1,
            'is_visible_on_front' => 1,
            'is_user_defined' => 1,
            'search_weight' => 1,
            'frontend_input' => 'multiselect',
            'is_unique' => 0,
            'is_required' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_filterable' => 1,
            'is_html_allowed_on_front' => 1,
            'used_for_sort_by' => 0,
            'facet_min_coverage_rate' => 0,
            'facet_max_size' => 10,
            'frontend_label' => ['Multiselect Attribute'],
            'backend_type' => 'varchar',
            'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
            'option' => [
                'value' => [
                    'option_1' => ['Option 1'],
                    'option_2' => ['Option 2'],
                    'option_3' => ['Option with & special char']
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                ],
            ],
        ]
    );

    $attribute->save();
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}
