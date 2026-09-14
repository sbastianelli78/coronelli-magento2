<?php
namespace Hoop\Contacts\Ui\Component\Listing\DataProviders\Hoop\Contacts;

class Contacts extends \Magento\Ui\DataProvider\AbstractDataProvider
{    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Hoop\Contacts\Model\ResourceModel\Contact\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
