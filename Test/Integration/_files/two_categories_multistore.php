<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Store/_files/second_store.php');
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Model\Category $category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    444
)->setCreatedAt(
    '2017-05-5 09:50:07'
)->setName(
    'Category'
)->setParentId(
    2
)->setPath(
    '1/2/555'
)->setLevel(
    2
)->setAvailableSortBy(
    ['position', 'name']
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->setUrlKey(
    'category'
)->save();

$subcategory = $objectManager->create(\Magento\Catalog\Model\Category::class);
$subcategory->isObjectNew(true);
$subcategory->setId(
    555
)->setCreatedAt(
    '2017-05-5 09:50:07'
)->setName(
    'Subcategory'
)->setParentId(
    $category->getId()
)->setPath(
    "1/2/{$category->getId()}/555"
)->setLevel(
    3
)->setAvailableSortBy(
    ['position', 'name']
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->setUrlKey(
    'subcategory'
)->save();

/** @var \Magento\Store\Model\Store $store */
$store = $objectManager->create(\Magento\Store\Model\Store::class);
$secondStore = $store->load('fixture_second_store');
$category->setStoreId($secondStore->getId())
    ->save();
$subcategory->setStoreId($secondStore->getId())
    ->setName('subcategory-fixturestore')
    ->setUrlKey('subcategory-fixturestore')
    ->save();
