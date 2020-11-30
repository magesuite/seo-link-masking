<?php

require __DIR__ . '/filterable_products_rollback.php';

/** @var \Magento\Store\Model\Store $secondStore */
$secondStore = $objectManager->create(\Magento\Store\Model\Store::class);
$secondStore->load('fixture_second_store');

/** @var \Magento\Store\Model\StoreCookieManager $storeCookieManager */
$storeCookieManager = $objectManager->get(\Magento\Store\Model\StoreCookieManager::class);
$storeCookieManager->deleteStoreCookie($secondStore);
