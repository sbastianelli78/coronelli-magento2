<?php
namespace Hoop\Contacts\Model\ResourceModel\Contact;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Hoop\Contacts\Model\Contact','Hoop\Contacts\Model\ResourceModel\Contact');
    }
}
