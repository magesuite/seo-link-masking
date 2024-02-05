<?php

namespace MageSuite\SeoLinkMasking\Controller\Filter;

class Redirect extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    const REDIRECT_URL_PARAMETER = 'url';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        parent::__construct($context);

        $this->resultFactory = $resultFactory;
        $this->urlInterface = $urlInterface;
    }

    public function execute()
    {
        $redirectUrl = $this->getRequest()->getParam(self::REDIRECT_URL_PARAMETER, null);

        if (empty($redirectUrl)) {

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
        $resultRedirect->setUrl($this->urlInterface->getUrl($redirectUrl));

        return $resultRedirect;
    }
}
