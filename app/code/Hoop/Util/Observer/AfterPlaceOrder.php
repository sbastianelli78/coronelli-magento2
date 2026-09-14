<?php
namespace Hoop\Util\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Zend_Mail;
use Zend_Mime;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $_order;

    protected $to, $to2, $to3;
    protected $bcc;
    protected $transportBuilder;
    protected $inlineTranslation;

    public function __construct(
        TransportBuilder $transportBuilder,
        \Magento\Sales\Api\Data\OrderInterface $order,
        StateInterface $inlineTranslation
    )
    {
        $this->_order = $order;
        $this->to = 'info@coronelli.it';
        $this->to2 = 'vendite@fugarcommerciale.it';
        $this->to3 = 'bastianelli@fugar.it';
        $this->bcc = 'a.giorgini@hoopcommunication.it';
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrderIds();

        $attach = $this->generateCsv($orderId);

        $this->sendOrderByEmail($attach, $this->to, $this->bcc, $this->to2, $this->to3);
    }

    protected function sendOrderByEmail($attach, $to, $bcc, $to2 = '', $to3 = '') {
        $fromEmail = 'info@coronelli.it';
        $fromName = 'E-Commerce Coronelli';

        $subject = 'Nuovo ordine';

        $mail = new Zend_Mail();
        $mail->setFrom($fromEmail, $fromName);
        $mail->addTo($to);
        if ($to2) {
            $mail->addTo($to2);
        }
        if ($to3) {
            $mail->addTo($to3);
        }
        $mail->addBcc($bcc);

        $mail->setSubject($subject);
        $mail->setBodyHtml('Invio ordine fatto su ecommerce.');

        $file = $mail->createAttachment(file_get_contents($attach));
        $file ->type        = 'text/csv';
        $file ->disposition = Zend_Mime::DISPOSITION_INLINE;
        $file ->encoding    = Zend_Mime::ENCODING_BASE64;
        $file ->filename    = 'order.xml';

        $mail->send();
        /*$this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier('order_notify')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            );
        $senderInfo = [
            'name' => 'enri',
            'email' => 'e.onofri@hoopcommunication.it'
        ];
        $receiverInfo = [
            'name' => 'giorg',
            'email' => 'a.giorgini@hoopcommunication.it',
        ];

        $transport->setTemplateVars(['data' => 'nonprende'])
            ->setFrom($senderInfo)
            ->addTo($receiverInfo);
        $transport = $transport->getTransport();
        $transport->addAttachment(file_get_contents($attach));
        $transport->sendMessage();
        $this->inlineTranslation->resume();*/
    }

    protected function generateCsv($orderId) {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $order = $this->_order->load($orderId);
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());

        $method = $order->getPayment()->getMethodInstance()->getCode();

        switch ($method) {
            // pagamenti: contrassegno (msp_cashondelivery), bonifico (banktransfer), paypal (paypal_express), carta credito (quipago)
            case 'quipago':
                $payment = 'CC';
                break;
            case 'banktransfer':
                $payment = 'BB';
                break;
            case 'paypal_express':
                $payment = 'PP';
                break;
            case 'msp_cashondelivery':
                $payment = 'CO';
                break;
            default:
                $payment = 'ND';
                break;
        }

        list($order_date, $order_time) = explode(' ', $order->getCreatedAt());

        $baddress = $order->getBillingAddress()->getStreet();
        $saddress = $order->getShippingAddress()->getStreet();

        $shipping_cost = $order->getShippingAmount();

        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\r\n";
        $xml .= '<Order>' . "\r\n";
        $xml .= '<IDOrdine>' . $order->getId() . '</IDOrdine>' . "\r\n";
        $xml .= '<OrderDate>' . $order_date . '</OrderDate>' . "\r\n";
        $xml .= '<OrderTime>' . $order_time . '</OrderTime>' . "\r\n";

        $xml .= '<CustomerName>' . $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname() . '</CustomerName>' . "\r\n";

        $xml .= '<BillingAddress>' . $baddress[0] . '</BillingAddress>' . "\r\n";
        $xml .= '<BillingCity>' . $order->getBillingAddress()->getCity() . '</BillingCity>' . "\r\n";
        $xml .= '<BillingPostCode>' . $order->getBillingAddress()->getPostcode() . '</BillingPostCode>' . "\r\n";
        $xml .= '<BillingState>' . $order->getBillingAddress()->getCountryId() . '</BillingState>' . "\r\n";
        $xml .= '<BillingCounty>' . strtoupper($order->getBillingAddress()->getRegion()) . '</BillingCounty>' . "\r\n";

        $xml .= '<ShippingName>' . $order->getShippingAddress()->getName() . '</ShippingName>' . "\r\n";
        $xml .= '<ShippingAddress>' . $saddress[0] . '</ShippingAddress>' . "\r\n";
        $xml .= '<ShippingCity>' . $order->getShippingAddress()->getCity() . '</ShippingCity>' . "\r\n";
        $xml .= '<ShippingPostCode>' . $order->getShippingAddress()->getPostcode() . '</ShippingPostCode>' . "\r\n";
        $xml .= '<ShippingState>' . $order->getShippingAddress()->getCountryId() . '</ShippingState>' . "\r\n";
        $xml .= '<ShippingCounty>' . strtoupper($order->getShippingAddress()->getRegion()) . '</ShippingCounty>' . "\r\n";

        $xml .= '<PaymentType>' . $payment . '</PaymentType>' . "\r\n";
        $xml .= '<Email>' . $order->getCustomerEmail() . '</Email>' . "\r\n";
        $xml .= '<Currency>' . $order->getOrderCurrencyCode() . '</Currency>' . "\r\n";
        $xml .= '<FiscalCode>' . $customer->getTaxvat() . '</FiscalCode>' . "\r\n";

        $xml .= '<VatRegistration>' . $order->getBillingAddress()->getVatId() . '</VatRegistration>' . "\r\n";
        $xml .= '<Phone>' . $order->getBillingAddress()->getTelephone() . '</Phone>' . "\r\n";
        $xml .= '<OrderAmount>' . number_format($order->getGrandTotal(), 2, ',', '.') . '</OrderAmount>' . "\r\n";
        $xml .= '<TransportCharge>' . number_format($shipping_cost, 2, ',', '.') . '</TransportCharge>' . "\r\n";
        $xml .= '<OrderLines>' . "\r\n";

        $lineno = 0;

        foreach ($order->getAllVisibleItems() as $item) {

            $lineno = $lineno + 1;
            $real_prod = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());

            $vat = $real_prod->getTaxClassId();
            // 4: iva 22% - 5: iva 10%
            $iva = 22;
            if ($vat == 5) {
                $iva = 10;
            }

            $prezzo_netto = ((int)$item->getQtyOrdered() * $item->getPrice() - $item->getDiscountAmount()) / (int)$item->getQtyOrdered();
            $price = $item->getPrice();

            $prezzo_netto = $this->calcolaIva($prezzo_netto, $iva);
            $price = $this->calcolaIva($price, $iva);

            //var_dump($price);die;

            $xml .= '<OrderLine>' . "\r\n";
            $xml .= '<LineNo>' . $lineno . '</LineNo>' . "\r\n";
            $xml .= '<ItemNo>' . $item->getSku() . '</ItemNo>' . "\r\n";
            $xml .= '<Description>' . str_replace('&', ' ', $item->getName()) . '</Description>' . "\r\n";
            $xml .= '<Quantity>' . (int)$item->getQtyOrdered() . '</Quantity>' . "\r\n";
            $xml .= '<UnitPrice>' . number_format($price, 2, ',', '.') . '</UnitPrice>' . "\r\n";
            $xml .= '<NetUnitPrice>' . number_format($prezzo_netto, 2, ',', '.') . '</NetUnitPrice>' . "\r\n";
            $xml .= '<LineAmount>' . number_format((int)$item->getQtyOrdered() * $prezzo_netto, 2, ',', '.') . '</LineAmount>' . "\r\n";
            $xml .= '</OrderLine>' . "\r\n";
        }
        $xml .= '</OrderLines>' . "\r\n";
        $xml .= '</Order>';

        $f = fopen('/var/www/coronelli/script/order_' . $order->getId() . '.xml', 'w+');
        fwrite($f, $xml);
        //rename('/var/www/coronelli/script/order_' . $order->getId() . '.xml', '/var/www/coronelli/script/exported/order_' . $order->getId() . '.xml');

        return '/var/www/coronelli/script/order_' . $order->getId() . '.xml';
    }

    protected function calcolaIva($prezzo, $iva){
        return $prezzo - (($prezzo * $iva) / 100);
    }
}