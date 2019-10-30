<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class UpdateMetaRobotsTagTest extends \Magento\TestFramework\TestCase\AbstractController
{
    const ROBOTS_TAG_INDEX_FOLLOW = 'INDEX,FOLLOW';

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/only_one_filter_demasked 1
     */
    public function testItDoesntUpdateSeoMetaRobots()
    {
        $this->dispatch('/test-category/option+1');

        $this->assertContains(self::ROBOTS_TAG_INDEX_FOLLOW, $this->getResponse()->getBody());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFilterableProducts
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/only_one_filter_demasked 1
     */
    public function testItUpdatesSeoMetaRobots()
    {
        $this->dispatch('/test-category/option+1--option+2');

        $this->assertContains('Multiselect Attribute', $this->getResponse()->getBody());

        $this->assertContains(\MageSuite\SeoLinkMasking\Observer\UpdateMetaRobotsTag::ROBOTS_TAG_NOINDEX_FOLLOW, $this->getResponse()->getBody());
    }

    public static function loadFilterableProducts()
    {
        require __DIR__.'/../_files/filterable_products.php';
    }

    public static function loadFilterableProductsRollback()
    {
        require __DIR__.'/../_files/filterable_products_rollback.php';
    }
}
