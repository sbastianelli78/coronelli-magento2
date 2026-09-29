<?php

namespace Hoop\Contacts\Observer;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class ValidateContactCaptcha implements ObserverInterface
{
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
        $objectManager = ObjectManager::getInstance();
        /** @var CaptchaHelper $captchaHelper */
        $captchaHelper = $objectManager->get(CaptchaHelper::class);
        $captcha = $captchaHelper->getCaptcha($formId);

        if (!$captcha->isRequired()) {
            return;
        }

        /** @var CaptchaStringResolver $captchaStringResolver */
        $captchaStringResolver = $objectManager->get(CaptchaStringResolver::class);
        if ($captcha->isCorrect($captchaStringResolver->resolve($request, $formId))) {
            return;
        }

        /** @var ManagerInterface $messageManager */
        $messageManager = $objectManager->get(ManagerInterface::class);
        $messageManager->addError(__('Incorrect CAPTCHA.'));

        /** @var ActionFlag $actionFlag */
        $actionFlag = $objectManager->get(ActionFlag::class);
        $actionFlag->set('', Action::FLAG_NO_DISPATCH, true);

        /** @var RedirectInterface $redirect */
        $redirect = $objectManager->get(RedirectInterface::class);
        $redirect->redirect($controller->getResponse(), 'hoop_contacts/index/index');
    }
}
