<?php
class ImportProducts
    extends \Magento\Framework\App\Http
    implements \Magento\Framework\AppInterface {
    public function launch() {
        $fromEmail = 'www-data@studiopleiadi.it';
        $fromName = 'Script importazione prodotti Coronelli';
        $toEmail = 'a.giorgini@hoopcommunication.it';
        $toName = 'giorg';
        $subject = 'Importazione prodotti Coronelli';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: ' . $fromEmail . "\r\n";

        $path_prefix = 'magento';

        $mail = new Zend_Mail();
        $mail->setFrom($fromEmail, $fromName);
        $mail->addTo($toEmail, $toName);
        // $mailbefore->addCc('marketing@magazzinidrudi.it', 'Marketing');
        $mail->setSubject($subject);

        ini_set('max_execution_time', '3400');

        $inserted = $failed = array();
        $src = 'p.csv';
        $i = $j = $h = 0;
        // Categoria,Codice,Descrizione,Confezione,Prezzo iva esclusa,IVA
        if (($handle = fopen($src, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($data[0] == 'Categoria')
                    continue;

                $art_categ = $data[0];
                $sku = $data[1];
                $art_name = ucwords(strtolower($data[2]));
                $art_conf = $data[3];
                $art_price_it = $data[4];
                $art_iva = $data[5];

                if ($sku != '') {
                    $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

                    $appState = $objectManager->get('\Magento\Framework\App\State');
                    $appState->setAreaCode('frontend');
                    $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                    $product = $objectManager->create('Magento\Catalog\Model\Product');

                    $is_instock = 1;

                    // se non metto questo salva nello store ita invece che nel default
                    $product->setStoreId(0);

                    // prodotto nuovo
                    $product->setDescription('&nbsp;')
                        ->setShortDescription('&nbsp')
                        ->setSku($sku)->setName($art_name)
                        ->setWebsiteIds(array(1))
                        ->setConfezione($art_conf)
                        ->setStockData(array(
                                'use_config_manage_stock' => 1,
                                'manage_stock'=>1,
                                'is_in_stock' => $is_instock,
                                'qty' => (int)trim(9999999)
                            )
                        );
                    try {
                        // (4 - iva 22%, 5 - iva 10%)
                        $product->setTaxClassId($art_iva);
                    }
                    catch (Exception $e) {
                        var_dump($e->getMessage());die;
                    }
                    // categoria
                    $product->setCategoryIds($art_categ);

                    // prezzo
                    $product->setPrice(trim($art_price_it));

                    try {
                        $product->save();
                        $inserted[] = $sku . "\n";
                        $i++;

                        continue;
                    }
                    catch (Exception $e) {
                        echo $e->getMessage();
                        die;
                    }

                }
            }
        }
    }

    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception) {
        return false;
    }
}