<?php
namespace Hoop\Contacts\Block;
class Main extends \Magento\Framework\View\Element\Template
{
    function _prepareLayout(){

    }

    public function getFormAction()
    {
        return '/hoop_contacts/index/index';
    }
}
