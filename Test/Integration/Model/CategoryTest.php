<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Test\Integration\Model;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    public const CATEGORY_WITHOUT_LINK_MASKING = 777;
    public const CATEGORY_WITH_LINK_MASKING = 778;
    public const ROOT_CATEGORY_WITH_LINK_MASKING = 2;

    protected ?\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;

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
     */
    public function testItReturnsCorrectAttributeValue(int $categoryId, ?array $expectedFilterState): void
    {
        $category = $this->categoryRepository->get($categoryId);

        $this->assertEquals($category->getSeoLinkMasking(), $expectedFilterState);
    }

    public static function dataProvider(): array
    {
        return [
            [self::CATEGORY_WITHOUT_LINK_MASKING, null],
            [self::CATEGORY_WITH_LINK_MASKING, [1 => false, 2 => true, 3 => false]],
            [self::ROOT_CATEGORY_WITH_LINK_MASKING, [1 => false, 2 => true, 3 => false]]
        ];
    }

    public static function loadCategories(): void
    {
        require __DIR__ . '/../_files/categories.php';
    }

    public static function loadCategoriesRollback(): void
    {
        require __DIR__ . '/../_files/categories_rollback.php';
    }
}
