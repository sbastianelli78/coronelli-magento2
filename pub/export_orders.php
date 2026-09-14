<?php

    use Magento\Framework\App\Bootstrap;

    require '../app/bootstrap.php';

    $params = $_SERVER;

    $bootstrap = Bootstrap::create(BP, $params);

    $obj = $bootstrap->getObjectManager();

    $state = $obj->get('Magento\Framework\App\State');
    $state->setAreaCode('frontend');


    $orderDatamodel = $obj->get('Magento\Sales\Model\Order')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('exported', array('null' => true));

    foreach ($orderDatamodel as $order) {

        try {
            $order->setExported('2')->save();
            die('qui4');
        }
        catch (Exception $e) {
            die($e->getMessage());
        }

    }