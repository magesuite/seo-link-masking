<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Plugin\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
 */
class IsLinkMaskingEnabledTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    protected $attributeFilter;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setId(1);

        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->registry->register('current_category', $category);

        $this->request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);

        $attribute = $this->objectManager->create(\Magento\Catalog\Model\Entity\Attribute::class);
        $attribute->loadByCode('catalog_product', 'attribute_with_option');

        $layer = $this->objectManager->create(\Magento\Catalog\Model\Layer\Category::class);

        $this->attributeFilter = $this->objectManager->create(\Magento\Catalog\Model\Layer\Filter\Attribute::class, ['layer' => $layer]);
        $this->attributeFilter->setData(['attribute_model' => $attribute]);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 0
     */
    public function testLinkMaskingIsDisabled()
    {
        $this->assertFalse($this->attributeFilter->getIsLinkMaskingEnabled());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/default_masking_state 0
     */
    public function testLinkMaskingIsEnabledAndDefaultIsDemasked()
    {
        $this->assertFalse($this->attributeFilter->getIsLinkMaskingEnabled());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/default_masking_state 1
     */
    public function testLinkMaskingIsEnabledAndDefaultIsMasked()
    {
        $this->assertTrue($this->attributeFilter->getIsLinkMaskingEnabled());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/default_masking_state 1
     */
    public function testLinkMaskingStateIsTakenFromCategoryConfig()
    {
        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setId(1);

        $attributeId = $this->attributeFilter->getAttributeModel()->getId();
        $category->setSeoLinkMasking([$attributeId => false]);

        $this->registry->unregister('current_category');
        $this->registry->register('current_category', $category);

        $this->assertFalse($this->attributeFilter->getIsLinkMaskingEnabled());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/default_masking_state 1
     */
    public function testLinkMaskingStateIsTakenFromRootCategoryOnSearchResultPage()
    {
        $this->assertTrue($this->attributeFilter->getIsLinkMaskingEnabled());

        $this->registry->unregister('current_category');
        $this->assertNull($this->attributeFilter->getIsLinkMaskingEnabled());

        $this->request
            ->setRouteName('catalogsearch')
            ->setControllerName('result')
            ->setActionName('index');

        $this->assertTrue($this->attributeFilter->getIsLinkMaskingEnabled());
    }
}
