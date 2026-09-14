<?php
class ExportOrders
    extends \Magento\Framework\App\Http
    implements \Magento\Framework\AppInterface {
    public function launch() {
        $fromEmail = 'www-data@studiopleiadi.it';
        $fromName = 'Script eportazione ordini Coronelli';
        $toEmail = 'a.giorgini@hoopcommunication.it';
        $toName = 'giorg';
        $subject = 'Eportazione ordiniCoronelli';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . $fromEmail . "\r\n";

        $path_prefix = 'magento';

        $mail = new Zend_Mail();
        $mail->setFrom($fromEmail, $fromName);
        $mail->addTo($toEmail, $toName);
        // $mailbefore->addCc('marketi
        //ng@magazzinidrudi.it', 'Marketing');
        $mail->setSubject($subject);

        $mail->setBodyHtml('Script lanciato');
        $mail->send();
    }

    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception) {
        return false;
    }
}