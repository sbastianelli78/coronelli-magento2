<?php
namespace Hoop\Contacts\Model\ResourceModel;
class Contact extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('hoop_contacts_contact','hoop_contacts_contact_id');
    }
}
