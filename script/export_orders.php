<?php

die('alora');

    use Magento\Framework\App\Bootstrap;

    require '../app/bootstrap.php';

    $params = $_SERVER;

    $bootstrap = Bootstrap::create(BP, $params);

    $obj = $bootstrap->getObjectManager();

    $state = $obj->get('Magento\Framework\App\State');
    $state->setAreaCode('frontend');


    $orderDatamodel = $obj->get('Magento\Sales\Model\Order')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('exported', array('null' => true));


    var_dump($orderDatamodel->getSize());die;
    foreach ($orderDatamodel as $order) {
        var_dump($order->setExported(NULL)->save());die;

        $orderData = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($getid);
        //echo "<pre>";
        $getorderdata = $orderData->getData();
        $orderItems = $orderData->getAllVisibleItems();
        foreach($orderItems as $orderItems){
            print_r($orderItems->getData());
        }
    }
    var_dump($orderDatamodel->getSize());die;