<?php

namespace Hoop\Contacts\Observer;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CheckContactCaptcha implements ObserverInterface
{
    protected $captchaHelper;
    protected $actionFlag;
    protected $messageManager;
    protected $redirect;
    protected $captchaStringResolver;

    public function __construct(
        CaptchaHelper $captchaHelper,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        CaptchaStringResolver $captchaStringResolver
    ) {
        $this->captchaHelper = $captchaHelper;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->captchaStringResolver = $captchaStringResolver;
    }

    public function execute(Observer $observer)
    {
        /** @var Action $controller */
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();

        if (!$request->isPost()) {
            return;
        }

        $post = $request->getPostValue();
        if (isset($post['from']) && $post['from'] === 'mailchimpform') {
            return;
        }

        $formId = 'contact_us';
        $captcha = $this->captchaHelper->getCaptcha($formId);
        if ($captcha->isRequired()
            && !$captcha->isCorrect($this->captchaStringResolver->resolve($request, $formId))
        ) {
            $this->messageManager->addError(__('Incorrect CAPTCHA.'));
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($controller->getResponse(), 'hoop_contacts/index/index');
        }
    }
}
