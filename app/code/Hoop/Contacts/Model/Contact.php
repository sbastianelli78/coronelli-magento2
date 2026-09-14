<?php
namespace Hoop\Contacts\Model;
class Contact extends \Magento\Framework\Model\AbstractModel implements \Hoop\Contacts\Api\Data\ContactInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'hoop_contacts_contact';

    protected function _construct()
    {
        $this->_init('Hoop\Contacts\Model\ResourceModel\Contact');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
