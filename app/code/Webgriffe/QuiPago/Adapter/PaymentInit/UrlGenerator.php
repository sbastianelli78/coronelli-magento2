<?php

namespace Webgriffe\QuiPago\Adapter\PaymentInit;

if (!class_exists('Webgriffe\LibQuiPago\PaymentInit\UrlGenerator')) {
    throw new \RuntimeException('Some dependencies are missing: please read the module documentation.');
}

class UrlGenerator extends \Webgriffe\LibQuiPago\PaymentInit\UrlGenerator
{
}
