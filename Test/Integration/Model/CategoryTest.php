<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Model;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    const CATEGORY_WITHOUT_LINK_MASKING = 777;
    const CATEGORY_WITH_LINK_MASKING = 778;
    const ROOT_CATEGORY_WITH_LINK_MASKING = 2;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function setUp(): void
    {
        $this->categoryRepository = \Magento\TestFramework\ObjectManager::getInstance()
            ->create(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadCategories
     * @dataProvider dataProvider
     * @param integer $categoryId
     * @param array|null $expectedFilterState
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testItReturnsCorrectAttributeValue($categoryId, $expectedFilterState)
    {
        $category = $this->categoryRepository->get($categoryId);

        $this->assertEquals($category->getSeoLinkMasking(), $expectedFilterState);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [self::CATEGORY_WITHOUT_LINK_MASKING, null],
            [self::CATEGORY_WITH_LINK_MASKING, [1 => false, 2 => true, 3 => false]],
            [self::ROOT_CATEGORY_WITH_LINK_MASKING, [1 => false, 2 => true, 3 => false]]
        ];
    }

    public static function loadCategories()
    {
        require __DIR__ . '/../_files/categories.php';
    }

    public static function loadCategoriesRollback()
    {
        require __DIR__ . '/../_files/categories_rollback.php';
    }
}
