<?php
namespace Hoop\Contacts\Controller\Adminhtml\Contact;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Hoop_Contacts::contacts';
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/index/index');
        return $resultRedirect;
    }
}
