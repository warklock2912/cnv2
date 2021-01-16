<?php

class Crystal_Campaignmanage_Model_Email extends Mage_Core_Model_Abstract
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

    public function sendEmailToWinners($winnerCollection){
        $storeId = Mage::app()->getStore()->getId();
        $winnerCollectionData = $winnerCollection;
        $count = 0;
        $products = [];
        foreach ($winnerCollectionData as $winner) {
            $mailTemplate = Mage::getModel('core/email_template');
            $dataObj = new Varien_Object();
            $dataObj->setData($winner->getData());
            $options = '';
            if(!isset($products[$winner->getProductId()])){
              $_product = Mage::getModel('catalog/product')->load($winner->getProductId());
              $products[$winner->getProductId()]['product'] = $_product;
              $products[$winner->getProductId()]['productName'] = $_product->getName();
              $products[$winner->getProductId()]['productUrl'] = $_product->getProductUrl();
              $products[$winner->getProductId()]['productType'] = $_product->getTypeId();
              $product_img = '';
              try{
                  $product_img = Mage::helper('catalog/image')->init($_product, 'image')->resize(265);
              }catch(Exception $e) {
                  $product_img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/carnival/default/images/no-image.jpg';
              }
              $products[$winner->getProductId()]['productImg'] = $product_img;
            }
            $_product = $products[$winner->getProductId()]['product'];
            $productUrl = $products[$winner->getProductId()]['productUrl'];
            $productType = $products[$winner->getProductId()]['productType'];
            $productImg = $products[$winner->getProductId()]['productImg'];
            $productName = $products[$winner->getProductId()]['productName'];
            $dataObj->setData('product_name', $productName);
            $dataObj->setData('product_img', $productImg);
            $dataObj->setData('ruffle_number', $winner->getRaffleId() . '-' . $winner->getId());

            if($productType == 'configurable'){
                $superAttribute = $winner->getOption();
                $options = $this->getProductOptionsHtml($_product, $superAttribute);
            }

            // var_dump($dataObj);
            // die;

            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_WINNER_EMAIL_TEMPLATE, $storeId),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId),
                    $winner->getEmail(),
                    null,
                    array(
                        'data' => $dataObj,
                        'options' => $options,
                        'link' => $productUrl
                        )
                );

            if ($mailTemplate->getSentSuccess()) {
                $this->updateSendEmail($winner->getId());
                $count ++;
            }
        }
        return $count;
    }

    public function sendEmailToLoosers($winnerCollection){
        $storeId = Mage::app()->getStore()->getId();
        $winnerCollectionData = $winnerCollection;
        $count = 0;
        $products = [];
        foreach ($winnerCollectionData as $winner) {
            $mailTemplate = Mage::getModel('core/email_template');
            $dataObj = new Varien_Object();
            $dataObj->setData($winner->getData());
            $options = '';
            if(!isset($products[$winner->getProductId()])){
              $_product = Mage::getModel('catalog/product')->load($winner->getProductId());
              $products[$winner->getProductId()]['product'] = $_product;
              $products[$winner->getProductId()]['productName'] = $_product->getName();
              $products[$winner->getProductId()]['productUrl'] = $_product->getProductUrl();
              $products[$winner->getProductId()]['productType'] = $_product->getTypeId();
              $product_img = '';
              try{
                  $product_img = Mage::helper('catalog/image')->init($_product, 'image')->resize(265);
              }catch(Exception $e) {
                  $product_img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/carnival/default/images/no-image.jpg';
              }
              $products[$winner->getProductId()]['productImg'] = $product_img;
            }
            $_product = $products[$winner->getProductId()]['product'];
            $productUrl = $products[$winner->getProductId()]['productUrl'];
            $productType = $products[$winner->getProductId()]['productType'];
            $productImg = $products[$winner->getProductId()]['productImg'];
            $productName = $products[$winner->getProductId()]['productName'];
            $dataObj->setData('product_name', $productName);
            $dataObj->setData('product_img', $productImg);

            if($productType == 'configurable'){
                $superAttribute = $winner->getOption();
                $options = $this->getProductOptionsHtml($_product, $superAttribute);
            }

            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_LOOSER_EMAIL_TEMPLATE, $storeId),
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId),
                    $winner->getEmail(),
                    null,
                    array(
                        'data' => $dataObj,
                        'options' => $options,
                        'link' => $productUrl
                        )
                );

            if ($mailTemplate->getSentSuccess()) {
                $this->updateSendEmail($winner->getId());
                $count ++;
            }
        }
        return $count;
    }

    protected function updateSendEmail($id, $send_email = 1){
      if($send_email){
        $send_email = gmdate('Y-m-d H:i:s');
      }
      $update = 'UPDATE campaign_raffle_online_subcrible SET send_email = :send_email WHERE id = :id;';
      $this->_connection->query($update, ['id' => $id, 'send_email' => $send_email]);
    }

    public function getProductOptionsHtml($product, $superAttribute)
    {
        $options = '';
        $attributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

        if($attributes){
            foreach ($attributes as $attribute){
                if($attribute['attribute_id'] != 255){
                    continue;
                }
                $options .= '<strong>'.$attribute['frontend_label'].': </strong>';
                foreach ($attribute['values'] as $value){
                    if($value['value_index'] == $superAttribute){
                        $options .= $value['label'];
                        }
                }
            }
        }
        return $options;
    }
}
