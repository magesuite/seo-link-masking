<?php

namespace MageSuite\SeoLinkMasking\Test\Integration\Controller\Filter;

class RedirectTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->formKey = $this->objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
    }

    public function testItReturns404ForGetRequest()
    {
        $url = 'linkmasking/filter/redirect?url=contact&form_key=' . $this->formKey->getFormKey();

        $this->getRequest()->setMethod(\Magento\Framework\App\Request\Http::METHOD_GET);

        $this->dispatch($url);
        $this->assertEquals(\Laminas\Http\Response::STATUS_CODE_404, $this->getResponse()->getHttpResponseCode());
    }

    public function testItReturnsErrorPageForMissingParameter()
    {
        $this->getRequest()->setMethod(\Magento\Framework\App\Request\Http::METHOD_POST);
        $this->getRequest()->setParams(
            [\MageSuite\SeoLinkMasking\Controller\Filter\Redirect::REDIRECT_URL_PARAMETER => null]
        );

        $this->dispatch('linkmasking/filter/redirect');
        $this->assertEquals(\Laminas\Http\Response::STATUS_CODE_404, $this->getResponse()->getHttpResponseCode());
    }

    public function testItReturnsRedirect()
    {
        $this->getRequest()->setMethod(\Magento\Framework\App\Request\Http::METHOD_POST);
        $this->getRequest()->setParams(
            [\MageSuite\SeoLinkMasking\Controller\Filter\Redirect::REDIRECT_URL_PARAMETER => 'contact']
        );

        $this->dispatch('linkmasking/filter/redirect');
        $this->assertEquals(\Laminas\Http\Response::STATUS_CODE_302, $this->getResponse()->getHttpResponseCode());
        $this->assertEquals('Location: http://localhost/index.php/contact/', (string)$this->getResponse()->getHeader('location'));
    }
}
