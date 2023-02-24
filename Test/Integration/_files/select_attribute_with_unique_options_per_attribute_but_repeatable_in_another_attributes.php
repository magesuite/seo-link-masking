<?php

/** @var \Magento\TestFramework\Helper\Bootstrap */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Eav\Model\Config */
$eavConfig = $objectManager->create(\Magento\Eav\Model\Config::class);

/** @var \Magento\Eav\Setup\EavSetupFactory */
$eavSetupFactory = $objectManager->create(\Magento\Eav\Setup\EavSetupFactory::class);

/** @var  \Magento\Framework\Setup\ModuleDataSetupInterface*/
$moduleDataSetup = $objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);

/** @var \Magento\Eav\Setup\EavSetup */
$eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetup]);

$defaultAttributeSetId = $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);

$eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'first_attribute_with_unique_options_per_attribute',
    [
        'group' => 'General',
        'type' => 'int',
        'label' => 'first_attribute_with_unique_options_per_attribute',
        'input' => 'select',
        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'comparable' => false,
        'used_for_promo_rules' => true,
        'is_html_allowed_on_front' => true,
        'visible_on_front' => true,
        'used_in_product_listing' => true,
        'pdp_group' => null,
        'searchable' => true,
        'visible_in_advanced_search' => true,
        'filterable' => 1,
        'filterable_in_search' => true,
        'attribute_set_id' => $defaultAttributeSetId,
        'option' => [
            'values' => [
                'unique_option_1',
                'unique_option_2',
                'unique_option_3'
            ]
        ]
    ]
);

$eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'second_attribute_with_unique_options_per_attribute',
    [
        'group' => 'General',
        'type' => 'int',
        'label' => 'second_attribute_with_unique_options_per_attribute',
        'input' => 'select',
        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => null,
        'comparable' => false,
        'used_for_promo_rules' => true,
        'is_html_allowed_on_front' => true,
        'visible_on_front' => true,
        'used_in_product_listing' => true,
        'pdp_group' => null,
        'searchable' => true,
        'visible_in_advanced_search' => true,
        'filterable' => 1,
        'filterable_in_search' => true,
        'attribute_set_id' => $defaultAttributeSetId,
        'option' => [
            'values' => [
                'unique_option_1',
                'unique_option_2',
                'unique_option_3'
            ]
        ]
    ]
);
