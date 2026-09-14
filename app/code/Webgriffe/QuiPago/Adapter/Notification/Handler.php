<?php

namespace Webgriffe\QuiPago\Adapter\Notification;

if (!class_exists('Webgriffe\LibQuiPago\Notification\Handler')) {
    throw new \RuntimeException('Some dependencies are missing: please read the module documentation.');
}

class Handler extends \Webgriffe\LibQuiPago\Notification\Handler
{
}
