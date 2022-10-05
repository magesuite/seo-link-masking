<?php
namespace MageSuite\SeoLinkMasking\Plugin\Theme\Block\Html;

class AddLinkMaskingToPager
{
    const FULL_CATEGORY_ACTION_NAME = 'catalog_category_view';

    /**
     * @var \MageSuite\SeoLinkMasking\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\SeoLinkMasking\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function aroundGetPagerUrl(\Magento\Theme\Block\Html\Pager $subject, \Closure $proceed, $params = [])
    {
        if (!$this->configuration->isLinkMaskingEnabled() || $subject->getRequest()->getFullActionName() !== self::FULL_CATEGORY_ACTION_NAME) {
            return $proceed($params);
        }
        return $this->getLinkMaskedPagerUrl($subject, $params);
    }

    protected function getLinkMaskedPagerUrl(\Magento\Theme\Block\Html\Pager $pager, $params = [])
    {
        $url = $pager->getRequest()->getUriString() ?? '';

        $fragment = $pager->getFragment();
        $paginationParam = $pager->getPageVarName();
        $page = isset($params[$paginationParam]) ? $params[$paginationParam] : null;

        $query = [];
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        if ($page === null || (int)$page === 1) {
            unset($query[$paginationParam]);
        } else {
            $query[$paginationParam] = $page;
        }

        $query = !empty($query) ? '?' . http_build_query($query) : '';

        return strtok($url, '?') . $query . $fragment;
    }
}
