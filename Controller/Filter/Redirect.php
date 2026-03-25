<?php

declare(strict_types=1);

namespace MageSuite\SeoLinkMasking\Controller\Filter;

class Redirect implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const REDIRECT_URL_PARAMETER = 'url';

    public function __construct(
        protected \Magento\Framework\App\RequestInterface $request,
        protected \Magento\Framework\Controller\ResultFactory $resultFactory,
        protected \Magento\Framework\UrlInterface $urlInterface,
        protected \Magento\Framework\Url\HostChecker $hostChecker
    ) {}

    public function execute(): mixed
    {
        $redirectUrl = $this->request->getParam(self::REDIRECT_URL_PARAMETER, null);
        $url = $this->urlInterface->getUrl($redirectUrl);

        if (empty($redirectUrl) || !$this->hostChecker->isOwnOrigin($url)) {
            $resultJson = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setStatusHeader(
                    \Laminas\Http\Response::STATUS_CODE_404,
                    \Laminas\Http\AbstractMessage::VERSION_11,
                    'Bad Request'
                );

            $result = [
                'error' => __('Bad Request'),
                'errorcode' => \Laminas\Http\Response::STATUS_CODE_404
            ];

            return $resultJson->setData($result);
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
}
