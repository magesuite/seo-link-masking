<?php

namespace MageSuite\SeoLinkMasking\Helper;

class Filter extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider
     */
    protected $filterableAttributesProvider;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \MageSuite\SeoLinkMasking\Service\FilterableAttributesProvider $filterableAttributesProvider
    ) {
        parent::__construct($context);

        $this->request = $request;
        $this->filterableAttributesProvider = $filterableAttributesProvider;
    }

    public function isFilterSelected($category)
    {
        $filters = $this->request->getQueryValue();

        if (empty($filters)) {
            return false;
        }

        $filterableAttributes = $this->filterableAttributesProvider->getList($category);

        if (empty($filterableAttributes)) {
            return false;
        }

        $difference = array_diff_key($filters, $filterableAttributes);

        return count($difference) < count($filters) ? true : false;
    }
}
