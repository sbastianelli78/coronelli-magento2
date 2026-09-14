<?php
namespace Hoop\Contacts\Api;

use Hoop\Contacts\Api\Data\ContactInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ContactRepositoryInterface 
{
    public function save(ContactInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(ContactInterface $page);

    public function deleteById($id);
}
