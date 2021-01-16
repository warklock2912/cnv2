<?php

// ini_set('memory_limit', '8M');
// ini_set('upload_max_filesize', '8M');
// ini_set('post_max_size', '32M');
// set_time_limit(7200); // กำหนดเวลาการประมวลผล script (0 หมายถึงไม่กำหนดเวลาการทำงาน)
// ini_set('max_input_time', 7200); // กำหนดเวลาการทำงานสูงสุดกับการส่งค่าด้วย $_GET $_POST และ $_FILES (วินาที)
// ini_set('max_execution_time', 7200); // กำหนดเวลาการประมวลผล script (วินาที)

class Tigren_Ruffle_Model_Email extends Mage_Core_Model_Abstract
{
    const XML_PATH_NOTIFICATION_AFTER_JOINING_EMAIL_TEMPLATE = 'ruffle/email_settings/notification_after_joining_email_template';
    const XML_PATH_EMAIL_SENDER = 'ruffle/email_settings/sender_email';
    const XML_PATH_NOTIFICATION_WINNER_EMAIL_TEMPLATE = 'ruffle/email_settings/notification_winner_email_template';
    const XML_PATH_NOTIFICATION_LOOSER_EMAIL_TEMPLATE = 'ruffle/email_settings/notification_looser_email_template';

    private $_connection;
    public function _construct() {
  		parent::_construct();
      $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function sendEmailAfterJoining($data, $fileName)
    {
        $storeId = Mage::app()->getStore()->getId();
        $mailTemplate = Mage::getModel('core/email_template');
        $sendToEmail = $data['email_address'];
        $dataObj = new Varien_Object();
        $dataObj->setData($data);
        $options = '';
        $_product = Mage::getModel('catalog/product')->load($data['product_id']);
        $productType = $_product->getTypeId();
        if($productType == 'configurable' && isset($data['product_options'])){
            $superAttribute = unserialize($data['product_options']);
            $options = $this->getProductOptionsHtml($_product, $superAttribute);
        }

        try
            {
                // $fileContents = file_get_contents(Mage::getBaseDir('media').DS.'pdf_ruffle'.DS.$fileName);
                // $mailTemplate->getMail()->createAttachment(
                //                             $fileContents,
                //                             'application/pdf',
                //                             Zend_Mime::DISPOSITION_ATTACHMENT,
                //                             Zend_Mime::ENCODING_BASE64,
                //                             $fileName
                //                         );
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_AFTER_JOINING_EMAIL_TEMPLATE, $storeId),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId),
                        $sendToEmail,
                        null,
                        array(
                            'data' => $dataObj,
                            'options' => $options
                        )
                    );

                    $result = true;
            } catch(Exception $exception) {
                $result = false;
            }

        return $result;
    }

    public function sendEmailToWinners($winnerIds){

        $storeId = Mage::app()->getStore()->getId();
        //$mailTemplate = Mage::getModel('core/email_template');
        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollection->addFieldToFilter('joiner_id', array('in' => $winnerIds))
                        ->addFieldToFilter('is_winner', 1)
                        ->addFieldToFilter('send_email', [ 'null' => true ])
                        ;
        $count = 0;
        $products = [];
        foreach ($winnerCollection as $winner) {
            $mailTemplate = Mage::getModel('core/email_template');
            $dataObj = new Varien_Object();
            $dataObj->setData($winner->getData());
            $options = '';
            if(!isset($products[$winner->getProductId()])){
              $_product = Mage::getModel('catalog/product')->load($winner->getProductId());
              $products[$winner->getProductId()]['product'] = $_product;
              $products[$winner->getProductId()]['productUrl'] = $_product->getProductUrl();
              $products[$winner->getProductId()]['productType'] = $_product->getTypeId();
              $products[$winner->getProductId()]['productImg'] = Mage::helper('catalog/image')->init($_product, 'image')->resize(265);
            }
            $_product = $products[$winner->getProductId()]['product'];
            $productUrl = $products[$winner->getProductId()]['productUrl'];
            $productType = $products[$winner->getProductId()]['productType'];
            $productImg = $products[$winner->getProductId()]['productImg'];
            if ($productImg != '') {
                $dataObj->setData('product_img', $productImg);
            }
            if($productType == 'configurable'){
                $superAttribute = unserialize($winner->getProductOptions());
                $options = $this->getProductOptionsHtml($_product, $superAttribute);
            }
            $fileName = 'Information_'.$winner->getJoinerId().'_'.$winner->getRuffleNumber().'.pdf';
            $filePath = Mage::getBaseDir('media').DS.'pdf_ruffle'.DS.$fileName;
            if (file_exists($filePath)){
                $fileContents = file_get_contents($filePath);
                $mailTemplate->getMail()->createAttachment(
                                            $fileContents,
                                            'application/pdf',
                                            Zend_Mime::DISPOSITION_ATTACHMENT,
                                            Zend_Mime::ENCODING_BASE64,
                                            $fileName
                                        );
            }
            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_WINNER_EMAIL_TEMPLATE, $storeId),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId),
                    $winner->getEmailAddress(),
                    null,
                    array(
                        'data' => $dataObj,
                        'options' => $options,
                        'link' => $productUrl
                        )
                );

            if ($mailTemplate->getSentSuccess()) {
                $this->updateSendEmail($winner->getJoinerId());
                $count ++;
            }
        }
        return $count;

    }
    public function sendEmailToLoosers($winnerIds){

        $storeId = Mage::app()->getStore()->getId();
        $mailTemplate = Mage::getModel('core/email_template');
        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollection->addFieldToFilter('joiner_id', array('in' => $winnerIds))
                        ->addFieldToFilter('is_winner', 0)
                        ->addFieldToFilter('send_email', [ 'null' => true ])
                        ;
        $count = 0;
        $products = [];
        foreach ($winnerCollection as $winner) {
            $mailTemplate = Mage::getModel('core/email_template');
            $dataObj = new Varien_Object();
            $dataObj->setData($winner->getData());
            $options = '';
            if(!isset($products[$winner->getProductId()])){
              $_product = Mage::getModel('catalog/product')->load($winner->getProductId());
              $products[$winner->getProductId()]['product'] = $_product;
              $products[$winner->getProductId()]['productUrl'] = $_product->getProductUrl();
              $products[$winner->getProductId()]['productType'] = $_product->getTypeId();
              $products[$winner->getProductId()]['productImg'] = Mage::helper('catalog/image')->init($_product, 'image')->resize(265);
            }
            $_product = $products[$winner->getProductId()]['product'];
            $productUrl = $products[$winner->getProductId()]['productUrl'];
            $productType = $products[$winner->getProductId()]['productType'];
            $productImg = $products[$winner->getProductId()]['productImg'];
            if ($productImg != '') {
                $dataObj->setData('product_img', $productImg);
            }
            if($productType == 'configurable'){
                $superAttribute = unserialize($winner->getProductOptions());
                $options = $this->getProductOptionsHtml($_product, $superAttribute);
            }

            // $fileName = 'Information_'.$winner->getJoinerId().'_'.$winner->getRuffleNumber().'.pdf';
            // $filePath = Mage::getBaseDir('media').DS.'pdf_ruffle'.DS.$fileName;
            // if (file_exists($filePath)){
            //     $fileContents = file_get_contents($filePath);
            //     $mailTemplate->getMail()->createAttachment(
            //                                 $fileContents,
            //                                 'application/pdf',
            //                                 Zend_Mime::DISPOSITION_ATTACHMENT,
            //                                 Zend_Mime::ENCODING_BASE64,
            //                                 $fileName
            //                             );
            // }
            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_LOOSER_EMAIL_TEMPLATE, $storeId),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId),
                    $winner->getEmailAddress(),
                    null,
                    array(
                        'data' => $dataObj,
                        'options' => $options,
                        'link' => $productUrl
                        )
                );

            if ($mailTemplate->getSentSuccess()) {
                $this->updateSendEmail($winner->getJoinerId());
                $count ++;
            }
        }
        return $count;

    }

    protected function updateSendEmail($joiner_id, $send_email = 1){
      if($send_email){
        $send_email = gmdate('Y-m-d H:i:s');
      }
      $update = 'UPDATE ruffle_joiner SET send_email = :send_email WHERE joiner_id = :joiner_id;';
      $this->_connection->query($update, ['joiner_id' => $joiner_id, 'send_email' => $send_email]);
    }

    public function getProductOptionsHtml($product, $superAttribute)
    {
        $options = '';
        $attributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        if($attributes){
            foreach ($attributes as $attribute){
                $options .= '<strong>'.$attribute['frontend_label'].': </strong>';
                foreach ($attribute['values'] as $value){
                    if($value['value_index'] == $superAttribute[$attribute['attribute_id']]){
                        $options .= $value['label'];
                        }
                }
            }
        }
        return $options;

    }

}
