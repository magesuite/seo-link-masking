<?php

namespace MageSuite\SeoLinkMasking\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    const SEO_LINK_MASKING_ATTRIBUTE_CODE = 'seo_link_masking';
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetupInterface;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetupInterface
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetupInterface = $moduleDataSetupInterface;
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetupInterface]);
    }

    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addFilterLinkMaskingAttributeToCategory();
        }

        $setup->endSetup();
    }

    protected function addFilterLinkMaskingAttributeToCategory()
    {
        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, self::SEO_LINK_MASKING_ATTRIBUTE_CODE)) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                self::SEO_LINK_MASKING_ATTRIBUTE_CODE,
                [
                    'type' => 'text',
                    'label' => 'Filter Link Masking',
                    'input' => 'textarea',
                    'visible' => true,
                    'required' => false,
                    'sort_order' => 10,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'Search Engine Optimization'
                ]
            );
        }
    }
}
