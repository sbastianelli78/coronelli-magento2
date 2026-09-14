<?php

    namespace Hoop\Contacts\Helper;

    use \Magento\Framework\App\Helper\AbstractHelper;

    class Data extends AbstractHelper
    {
        public function isProduction() {
            $ip = $_SERVER['REMOTE_ADDR'];

            if (substr($ip, 0, 9) == '192.168.0') {
                return false;
            }
            return true;
        }
    }