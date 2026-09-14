<?php

namespace Hoop\Contacts\Controller\Index;

use Braintree\Exception;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use \Psr\Log\LoggerInterface;
use Hoop\Contacts\Helper\Data;
use Hoop\Contacts\Helper\MailChimp;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $storeManager;
    protected $logger;
    protected $subscriberFactory;
    protected $helper;
    protected $mailchimp;
    protected $_response;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        Data $helper,
        MailChimp $mailchimp,
        ResponseInterface $response
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->subscriberFactory = $subscriberFactory;
        $this->helper = $helper;
        $this->mailchimp = $mailchimp;
        $this->_response = $response;

        parent::__construct($context);
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!empty($post)) {

            if ($post['from'] == 'mailchimpform') {

                if (!filter_var($this->getRequest()->getPost('email'), FILTER_VALIDATE_EMAIL)) {
                    $this->messageManager->addError('E-mail non valida.');
                    $this->_response->setRedirect('/emailnotvalid')->sendResponse();
                    return;
                }

                $MailChimp = $this->mailchimp;

                $list_id = '91837cc46e';
                if (!$this->helper->isProduction()) {
                    $list_id = '91837cc46e';
                }

                $MailChimp->post("lists/$list_id/members", [
                    'email_address' => $this->getRequest()->getPost('email'),
                    'status' => 'subscribed',
                ]);
                if (!$MailChimp->success()) {
                    $response = $MailChimp->getLastResponse();
                    if (strpos($response['body'], 'Member Exists') !== false) {
                        // membro già esistente, invece di una insert faccio un update
                        $subscriber_hash = $MailChimp->subscriberHash($this->getRequest()->getPost('email'));
                        $result = $MailChimp->patch("lists/$list_id/members/$subscriber_hash", [
                        ]);

                        if (!$MailChimp->success()) {
                        } else {
                            $this->messageManager->addSuccess(__("Grazie per esserti iscritto alla nostra newsletter."));
                            $this->_response->setRedirect('/newsletter')->sendResponse();

                        }
                    } else {
                    }
                } else {
                    $this->messageManager->addSuccess(__("Grazie per esserti iscritto alla nostra newsletter."));
                    $this->_response->setRedirect('/newsletter')->sendResponse();
                }
                $this->subscriberFactory->create()->subscribe($post['email']);
            } else {

                $contact = $this->_objectManager->create('Hoop\Contacts\Model\Contact');

                $newsletter = $privacy = 0;
                if (isset($post['newsletter']) && $post['newsletter'] == 'on') {
                    $newsletter = 1;
                }
                if ($post['privacy'] == 'on') {
                    $privacy = 1;
                }
                try {
                    $contact
                        ->setFirstname($post['firstname'])
                        ->setLastname($post['lastname'])
                        ->setEmail($post['email'])
                        ->setCompany($post['company'])
                        ->setPhone($post['phone'])
                        ->setMessage($post['message'])
                        ->setPrivacy($privacy)
                        ->setNewsletter($newsletter)
                        ->save();
                    $this->messageManager->addSuccess(__("Thanks for contacting us. We'll answer as soon as possible."));

                    // ho salvato, inserisco su mailchimp
                    $MailChimp = $this->mailchimp;

                    $list_id = '91837cc46e';
                    if (!$this->helper->isProduction()) {
                        $list_id = '91837cc46e';
                    }

                    $MailChimp->post("lists/$list_id/members", [
                        'email_address' => $this->getRequest()->getPost('email'),
                        'status' => 'subscribed',
                        'merge_fields' => ['FNAME' => $this->getRequest()->getPost('firstname'),
                            'LNAME' => $this->getRequest()->getPost('lastname'),
                        ],
                    ]);
                    if (!$MailChimp->success()) {
                        $response = $MailChimp->getLastResponse();
                        if (strpos($response['body'], 'Member Exists') !== false) {
                            // membro già esistente, invece di una insert faccio un update
                            $subscriber_hash = $MailChimp->subscriberHash($this->getRequest()->getPost('email'));
                            $result = $MailChimp->patch("lists/$list_id/members/$subscriber_hash", [
                                'merge_fields' => ['FNAME' => $this->getRequest()->getPost('firstname'),
                                    'LNAME' => $this->getRequest()->getPost('lastname'),
                                ],
                            ]);

                            if (!$MailChimp->success()) {
                            } else {
                            }
                        } else {
                        }
                    }
                } catch (Exception $e) {
                    $this->logger->info('Hoop - Non sono riuscito a salvare il contatto: ' . $e->getMessage());
                }

                // iscrizione alla newsletter
                if ($newsletter) {
                    $this->subscriberFactory->create()->subscribe($post['email']);
                }

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

                $sender_fullname = $post['firstname'] . ' ' . $post['lastname'];

                $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());
                $templateVars = array(
                    'store' => $storeManager->getStore(),
                    'customer_name' => $sender_fullname,
                    'message' => $post['message'],
                    'company' => $post['company'],
                    'phone' => $post['phone']
                );
                $from = array('email' => $post['email'], 'name' => $sender_fullname);
                $this->inlineTranslation->suspend();

                $to = 'a.giorgini@hoopcommunication.it';
                if ($this->helper->isProduction()) {
                    $to = 'info@coronelli.it';
                }
                $transport = $this->transportBuilder->setTemplateIdentifier('contact_form')
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($to);
                if ($this->helper->isProduction()) {
                    $transport->addTo('vendite@fugarcommerciale.it')
                        ->addTo('bastianelli@fugar.it')
                        ->addBcc('e.magnani@hoopcommunication.it');
                }

                $transport = $transport->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
        }

        return $this->resultPageFactory->create();
    }
}
