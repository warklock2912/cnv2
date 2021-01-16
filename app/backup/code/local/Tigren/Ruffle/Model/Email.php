<?php

class Tigren_Ruffle_Model_Email extends Mage_Core_Model_Abstract
{
    const XML_PATH_NOTIFICATION_AFTER_JOINING_EMAIL_TEMPLATE = 'ruffle/email_settings/notification_after_joining_email_template';
    const XML_PATH_EMAIL_SENDER = 'ruffle/email_settings/sender_email';
    const XML_PATH_NOTIFICATION_WINNER_EMAIL_TEMPLATE = 'ruffle/email_settings/notification_winner_email_template';
    const XML_PATH_NOTIFICATION_LOOSER_EMAIL_TEMPLATE = 'ruffle/email_settings/notification_looser_email_template';

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
        $mailTemplate = Mage::getModel('core/email_template');
        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollection->addFieldToFilter('joiner_id', array('in' => $winnerIds))
                        ->addFieldToFilter('is_winner', 1);
        $count = 0;
        foreach ($winnerCollection as $winner) {
            $dataObj = new Varien_Object();
            $dataObj->setData($winner->getData());
            $options = '';
            $_product = Mage::getModel('catalog/product')->load($winner->getProductId());
            $productUrl = $_product->getProductUrl();
            $productType = $_product->getTypeId();

            $product_img = Mage::helper('catalog/image')->init($_product, 'image')->resize(265);
            if ($product_img != '') {
                $dataObj->setData('product_img',$product_img);
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
                        ->addFieldToFilter('is_winner', 0);
        $count = 0;
        foreach ($winnerCollection as $winner) {
            $dataObj = new Varien_Object();
            $dataObj->setData($winner->getData());
            $options = '';
            $_product = Mage::getModel('catalog/product')->load($winner->getProductId());
            $productUrl = $_product->getProductUrl();
            $productType = $_product->getTypeId();

            $product_img = Mage::helper('catalog/image')->init($_product, 'image')->resize(265);
            if ($product_img != '') {
                $dataObj->setData('product_img',$product_img);
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
                $count ++;
            }
        }
        return $count;

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