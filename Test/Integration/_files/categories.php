<?php
$rootCategoryId = 2;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category
    ->setId(777)
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category without link masking attribute')
    ->setParentId(2)
    ->setPath('1/2/777')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position'])
    ->save()
    ->reindex();

$seoLinkMasking = [
    ['attribute_id' => 1, 'is_masked' => "false"],
    ['attribute_id' => 2, 'is_masked' => "true"],
    ['attribute_id' => 3, 'is_masked' => "false"]
];

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category
    ->setId(778)
    ->setName('Category with link masking attribute')
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setParentId(2)
    ->setPath('1/2/778')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position'])
    ->setSeoLinkMasking($seoLinkMasking)
    ->save()
    ->reindex();

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->load($rootCategoryId);

$category
    ->setSeoLinkMasking($seoLinkMasking)
    ->save()
    ->reindex();