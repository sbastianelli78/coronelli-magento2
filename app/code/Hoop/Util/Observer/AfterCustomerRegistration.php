<?php

namespace Hoop\Util\Observer;

use Magento\Framework\Event\ObserverInterface;


class AfterCustomerRegistration implements ObserverInterface
{
    protected $request;
    protected $_customerRepositoryInterface;

    public function __construct(\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
                                \Magento\Framework\App\Request\Http $request
    )
    {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // dovrebbe essere sempre vero
        if ($this->request->getPost('privacy') == 'on') {
            $customer = $this->_customerRepositoryInterface->getById($observer->getEvent()->getCustomer()->getId())->setCustomAttribute('privacy',1);
            $this->_customerRepositoryInterface->save($customer);
        }
    }
}