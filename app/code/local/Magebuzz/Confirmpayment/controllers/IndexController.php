<?php

class Magebuzz_Confirmpayment_IndexController extends Mage_Core_Controller_Front_Action {

  public function indexAction() {
    $this->loadLayout();
    $this->renderLayout();
  }

  protected function _getSession() {
    return Mage::getSingleton('core/session');
  }

  public function sendMailAction() {

    $post = $this->getRequest()->getPost();
    if ($post) {
      try {
        $postObject = new Varien_Object();
        $postObject->setData($post);

        $fileName = '';
        if (isset($_FILES['cp-attachment']['name']) && $_FILES['cp-attachment']['name'] != '') {
          try {
            $order_id = $post['order_no'];
            $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
            $date = date('Y-m-d-His', $currentTimestamp);

//            $fileName = $_FILES['cp-attachment']['name'];
            $fileName = $date . time();
            $uploader = new Varien_File_Uploader('cp-attachment');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); //add more file types you want to allow
            $uploader->setAllowRenameFiles(TRUE);
            $uploader->setFilesDispersion(false);
            $path = Mage::getBaseDir('media') . DS . 'confirmpayment';
            if (!is_dir($path)) {
              mkdir($path, 0777, true);
            }
            $ref = $uploader->save($path . DS, $fileName);

            /*
             * save data submited     */

            $model = Mage::getModel('confirmpayment/cpform');
            $model->setData($post)->setAttachment($fileName)->setStatus(1)->setId(NULL)->save();

            /*
             * send email
             *  * */

            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            $mailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();

            /*         * *********************************************************** */
            //sending file as attachment
            $attachmentFilePath = Mage::getBaseDir('media') . DS . 'confirmpayment' . DS . $fileName;
            if (file_exists($attachmentFilePath)) {
              $fileContents = file_get_contents($attachmentFilePath);
              $mailTemplate->getMail()->createAttachment($fileContents, Zend_Mime::TYPE_OCTETSTREAM, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $fileName);
            }
            /* SET PARAM SENDMAIL */

            //template
            $template = Mage::getStoreConfig('confirmpayment/info/email_template', $storeId);
            //sender
            $sender = Mage::getStoreConfig('confirmpayment/info/sender_id', $storeId);
            //recipient
            $recepientEmail = Mage::getStoreConfig('confirmpayment/recipient/email', $storeId);
            $recepientName = Mage::getStoreConfig('confirmpayment/recipient/name', $storeId);

            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                    ->sendTransactional(
                            $template, $sender, $recepientEmail, $recepientName, array('data' => $postObject)
            );

            // if (!$mailTemplate->getSentSuccess()) {
            //   throw new Exception();
            // }
            $translate->setTranslateInline(true);
            $this->_getSession()->addSuccess('Data submited successfully');
            $this->_redirect('*/*/');
            return;

          } catch (Exception $e) {
              $this->_getSession()->addError($e->getMessage());
              $this->_redirect('*/*/');
          }
        }

      } catch (Exception $e) {
        $translate->setTranslateInline(true);
        $this->_redirect('*/*/');
        return;
      }
    } else {
      $this->_redirect('*/*/');
    }
  }

}
