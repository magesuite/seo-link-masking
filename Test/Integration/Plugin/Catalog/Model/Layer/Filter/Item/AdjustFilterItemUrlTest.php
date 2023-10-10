<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Plugin\Catalog\Model\Layer\Filter\Item;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AdjustFilterItemUrlTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\SeoLinkMasking\Helper\Page $pageHelperMock;
    protected ?\Magento\Catalog\Model\Layer\Filter\Category $categoryFilter;
    protected ?\Magento\Catalog\Model\Layer\Filter\Item $filterItem;
    protected ?\MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\Item\AdjustFilterItemUrl $plugin;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->pageHelperMock = $this->getMockBuilder(\MageSuite\SeoLinkMasking\Helper\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSearchResultPage', 'isBrandsIndexPage', 'isSearchResultPageAjaxFilterCall'])
            ->getMock();

        $objectManager->addSharedInstance(
            $this->pageHelperMock,
            \MageSuite\SeoLinkMasking\Helper\Page::class,
            true
        );

        $layer = $objectManager->create(\Magento\Catalog\Model\Layer\Category::class);
        $this->categoryFilter = $objectManager->create(\Magento\Catalog\Model\Layer\Filter\Category::class, ['layer' => $layer]);

        $this->filterItem = $objectManager->create(\Magento\Catalog\Model\Layer\Filter\Item::class);
        $this->plugin = $objectManager->get(\MageSuite\SeoLinkMasking\Plugin\Catalog\Model\Layer\Filter\Item\AdjustFilterItemUrl::class);
    }

    /**
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/default_masking_state 0
     */
    public function testItDoesNotReturnMaskedUrl()
    {
        $testUrl = 'http://localhost/index.php/catalogsearch/result/?cat=5&q=senior';
        $filterItem = $this->filterItem->setData('filter', $this->categoryFilter);

        // phpcs:ignore Standard.Classes.RequireFullPath
        $proceed = function () use ($testUrl) {
            return $testUrl;
        };

        $this->assertEquals($testUrl, $this->plugin->aroundGetUrl($filterItem, $proceed));
    }

    /**
     * @magentoConfigFixture current_store seo/link_masking/is_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/is_short_filter_url_enabled 1
     * @magentoConfigFixture current_store seo/link_masking/mask_category_url_on_search_page 1
     */
    public function testItReturnsMaskedUrl()
    {
        $testUrl = 'http://localhost/index.php/catalogsearch/result/?cat=5&q=senior';
        $filterItem = $this->filterItem->setData('filter', $this->categoryFilter);

        // phpcs:ignore Standard.Classes.RequireFullPath
        $proceed = function () use ($testUrl) {
            return $testUrl;
        };

        $this->pageHelperMock->method('isSearchResultPage')->willReturn(true);
        $result = $this->plugin->aroundGetUrl($filterItem, $proceed);

        $this->assertStringStartsWith('{"action":"http:\/\/localhost\/index.php\/linkmasking\/filter\/redirect\/","data":{"url"', $result);
        $this->assertStringContainsString(json_encode($testUrl), $result);
    }
}
