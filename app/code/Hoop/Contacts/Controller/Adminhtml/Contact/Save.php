<?php
namespace Hoop\Contacts\Controller\Adminhtml\Contact;

use Magento\Backend\App\Action;
use Hoop\Contacts\Model\Page;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
            
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Hoop_Contacts::contacts';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Hoop\Contacts\Model\Contact::STATUS_ENABLED;
            }
            if (empty($data['hoop_contacts_contact_id'])) {
                $data['hoop_contacts_contact_id'] = null;
            }

            /** @var Hoop\Contacts\Model\Contact $model */
            $model = $this->_objectManager->create('Hoop\Contacts\Model\Contact');

            $id = $this->getRequest()->getParam('hoop_contacts_contact_id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the thing.'));
                $this->dataPersistor->clear('hoop_contacts_contact');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['hoop_contacts_contact_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->dataPersistor->set('hoop_contacts_contact', $data);
            return $resultRedirect->setPath('*/*/edit', ['hoop_contacts_contact_id' => $this->getRequest()->getParam('hoop_contacts_contact_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }    
}
