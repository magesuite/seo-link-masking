<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Plugin\Magento\Framework\Session\SessionStartChecker;

class DisableSessionUrls
{
    public function __construct(
        protected \Magento\Framework\App\Request\Http $request,
        protected array $actionNameList = []
    ) {}

    public function afterCheck(\Magento\Framework\Session\SessionStartChecker $subject, bool $result): bool
    {
        $pathInfo = (string)$this->request->getPathInfo();

        foreach ($this->actionNameList as $actionName) {
            if (!str_contains($pathInfo, $actionName)) {
                continue;
            }

            return false;
        }

        return $result;
    }
}
