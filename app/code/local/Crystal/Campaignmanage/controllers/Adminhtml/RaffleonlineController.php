<?php

class Crystal_Campaignmanage_Adminhtml_RaffleonlineController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('campaignmanage/raffle');
    }
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('campaignmanage/items')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Raffle Manager'),
                Mage::helper('adminhtml')->__('Raffle Manager')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function exportCsvAction() {
        $fileName = 'raffleonline.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_grid')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'raffleonline.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_grid')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportWinnerCsvAction() {
        $fileName = 'raffleonlinewinner.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_winner')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportWinnerXmlAction() {
        $fileName = 'raffleonlinewinner.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_winner')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportMemberCsvAction() {
        $fileName = 'raffleonlinesubcriber.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_allmember')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportMemberXmlAction() {
        $fileName = 'raffleonlinesubcriber.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_allmember')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', TRUE);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', TRUE);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('campaignmanage/campaignonline')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('raffleonline_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('campaignmanage/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Raffle Online Manager'), Mage::helper('adminhtml')->__('Raffle Online Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Raffle Online News'), Mage::helper('adminhtml')->__('Raffle Online News'));
            $this->_addContent($this->getLayout()->createBlock(' campaignmanage/adminhtml_raffleonline_edit'))
                ->_addLeft($this->getLayout()
                    ->createBlock('campaignmanage/adminhtml_raffleonline_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('campaignmanage')->__('Item does not exist'));
            $this->_redirectReferer();
        }
    }

    public function saveAction()
    {
        $model = Mage::getModel('campaignmanage/campaignonline');

        if ($data = $this->getRequest()->getPost()) {
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    //rename image in case image name has space
                    $image_name = $_FILES['image']['name'];
                    $new_image_name = Mage::helper('campaignmanage')->renameImage($image_name);

                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(TRUE);
                    $uploader->setFilesDispersion(FALSE);

                    $path = Mage::getBaseDir('media') . DS . 'campaignmanage' . DS . 'images';
                    if (!is_dir($path)) {
                        mkdir($path, 0777, TRUE);
                    }

                    if (!file_exists($path . DS . $new_image_name)) {
                        $uploader->save($path, $new_image_name);
                    }
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
                $data['image'] = $new_image_name;
            } else {
                if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
                    $data['image'] = '';
                } else {
                    unset($data['image']);
                }
            }

            if (isset($data['stores_active'])) {
                $data['stores_active'] = implode(',', $data['stores_active']);
            }

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            # Process Timezone
            $datetimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

            //set start register time
            $startRegisterTime = new Zend_Date($model->getStartRegisterTime(), $datetimeFormat);
            $startRegisterTime->addSecond(Mage::helper('mpblog')->getTimezoneOffset());
            $model->setStartRegisterTime($startRegisterTime->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));

            //set start register time
            $endRegisterTime = new Zend_Date($model->getEndRegisterTime(), $datetimeFormat);
            $endRegisterTime->addSecond(Mage::helper('mpblog')->getTimezoneOffset());
            $model->setEndRegisterTime($endRegisterTime->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));

            //start store image
            try {
                if ($model->getStatus() == NULL)
                    $model->setStatus(1);
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('campaignmanage')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/index');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('campaignmanage')->__('Unable to find item to save'));
        $this->_redirect('*/*/index');
    }

    public function deleteAction()
    {
        $model = Mage::getModel('campaignmanage/campaignonline');
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $subcribleCustomers = Mage::getModel('campaignmanage/raffleonline')->getCollection()
                    ->addFieldToFilter('raffle_id', $this->getRequest()->getParam('id'));
                if (count($subcribleCustomers)) {
                    foreach ($subcribleCustomers as $customer) {
                        $customer->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/index');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $campaignIds = $this->getRequest()->getParam('campaignmanage');
        if (!is_array($campaignIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($campaignIds as $campaignId) {
                    $campaign = Mage::getModel('campaignmanage/campaignonline')
                        ->load($campaignId);
                    $campaign->delete();
                    $subcribleCustomers = Mage::getModel('campaignmanage/raffleonline')->getCollection()
                        ->addFieldToFilter('raffle_id', $campaignId);
                    if (count($subcribleCustomers)) {
                        foreach ($subcribleCustomers as $customer) {
                            $customer->delete();
                        }
                    }
                }
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($campaignIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
//		$this->_redirect('*/*/index');
        $this->_redirectReferer();

    }

    public function productAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('campaignonline.edit.tab.item');
        $this->renderLayout();
    }

    public function winnerAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('campaignonline.edit.tab.winner');
        $this->renderLayout();
    }

    public function unassignAction()
    {
        $joinerID = $this->getRequest()->getParam('joiner_id');
        $joiner = Mage::getModel('campaignmanage/raffleonline')->load($joinerID);
        if ($joiner->getId() || $joinerID == 0) {
            try {
                $joiner->setIsWinner(false)->setAssignedLoser(true);
                $joiner->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('campaignmanage')->__('Unassign Customer Success'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/edit', array('id' => $joiner->getRaffleId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam($joiner->getRaffleId())));
                return;
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('campaignmanage')->__('Item does not exist'));
            $this->_redirectReferer();
        }
    }


    public function randomWinnerQuotaAction()
    {
        $campaignId = $this->getRequest()->getParam('id');


        $assignedLoserCollection = Mage::getModel('campaignmanage/raffleonline')->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId)
            ->addFieldToFilter('assigned_loser', true)
            ->addFieldToFilter('assigned_winner', array(
                array('null' => 1),
                array('eq' => false)
            ));

        if (count($assignedLoserCollection)) {
            foreach ($assignedLoserCollection as $joiner) :

                $product = Mage::getModel('catalog/product')->load($joiner->getProductId());
                $productId = $product->getId();
                if ($product->isConfigurable()) {
                    $optionList = $this->getOptions($productId, $campaignId);
                    foreach ($optionList as $option) :
                        $optionValue = $option[0];
                        $qty = $option[1];
                        $this->setListWinnerByOption($optionValue, 1, $productId, $campaignId, true);
                    endforeach;
                } else {
                    $qty = (int)$product->getStockItem()->getQty();
                    $this->setListWinnerByProduct($productId, 1, $campaignId, true);
                }
            endforeach;
        }

//        $this->sendNotificationToWinner($campaignId);
        $this->_getSession()->addSuccess(
            $this->__('Random Quota successfully!')
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('campaignmanage/adminhtml_raffleonline_edit_tab_item')->toHtml()
        );
//		$this->getLayout()->getBlock('campaign.grid');
//		$this->renderLayout();
    }

    public function addSelectedItemsAction()
    {
        $data = $this->getRequest()->getPost();
        $campaignId = $this->getRequest()->getParam('id');
        $productIds = explode('&', $data['product_ids']);
        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $size = Mage::helper('campaignmanage')->convertOptionsToString($productId);
                $model = Mage::getModel('campaignmanage/onlineproduct');
                try {
                    $model->setRaffleId($campaignId)
                        ->setProductId($productId)
                        ->setOptions($size)
                        ->save();
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }
            $this->_getSession()->addSuccess(
                $this->__('Add product(s) successfully .')
            );
        }
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    public function randomAllWinnerAction()
    {
        $campaignId = $this->getRequest()->getParam('id');
        $campaign = Mage::getModel('campaignmanage/campaignonline')->load($campaignId);
        $productsRaffle = Mage::getModel('campaignmanage/onlineproduct')->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId);
        foreach ($productsRaffle as $product) :
            $product = Mage::getModel('catalog/product')->load($product->getProductId());
            $productId = $product->getId();
            if ($product->isConfigurable()) {
                $optionList = $this->getOptions($productId, $campaignId);
                foreach ($optionList as $option) :
                    $optionValue = $option[0];
                    $qty = $option[1];
                    $this->setListWinnerByOption($optionValue, $qty, $productId, $campaignId);
                endforeach;
            } else {
                $qty = (int)$product->getStockItem()->getQty();
                $this->setListWinnerByProduct($productId, $qty, $campaignId);
            }
        endforeach;
        $campaign->setEndRegisterTime(now());
        $campaign->setStatus(2);
        $campaign->save();

//        if (!$campaign->getIsCardPayment()) {
//            $this->sendNotificationToAllWinner($campaignId);
//        }

        $this->_getSession()->addSuccess(
            $this->__('Random successfully!')
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    // Email Actions
    public function emailToSelectedWinnerAction()
    {
        $data = $this->getRequest()->getPost();

        $winnerCollection = Mage::getModel('campaignmanage/raffleonline')->getCollection();
        $winnerIds = explode('&', $data['winner_ids']);
        if (!empty($winnerIds)) {
            $winnerCollection->addFieldToFilter('id', $winnerIds);

            $sendEmail = Mage::getModel('campaignmanage/email')->sendEmailToWinners($winnerCollection);
            $this->_getSession()->addSuccess(
                $this->__('Sent email successfully to %d winner(s).', $sendEmail)
            );
        }
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getPost('raffle_id')));
    }

    public function emailToAllWinnerAction()
    {
        $raffleId = $this->getRequest()->getParam('id');
        $winnerCollection = Mage::getModel('campaignmanage/raffleonline')->getCollection();
        $winnerCollection->addFieldToFilter('raffle_id', $raffleId)
            ->addFieldToFilter('is_winner', 1)
            ->addFieldToFilter('send_email', [ 'null' => true ]);

        $sendEmail = Mage::getModel('campaignmanage/email')->sendEmailToWinners($winnerCollection);
        $this->_getSession()->addSuccess(
            $this->__('Sent email successfully to %d winner(s).', $sendEmail)
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $raffleId));
    }

    public function emailToAllLooserAction()
    {
        $raffleId = $this->getRequest()->getParam('id');
        $winnerCollection = Mage::getModel('campaignmanage/raffleonline')->getCollection();
        $winnerCollection->addFieldToFilter('raffle_id', $raffleId)
            ->addFieldToFilter('is_winner', [0, [ 'null' => true ]])
            ->addFieldToFilter('send_email', [ 'null' => true ]);

        $sendEmail = Mage::getModel('campaignmanage/email')->sendEmailToLoosers($winnerCollection);
        $this->_getSession()->addSuccess(
            $this->__('Sent email successfully to %d loser(s).', $sendEmail)
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $raffleId));
    }


    public function sendNotificationToAllWinner($campaignId)
    {
        $campaign = Mage::getModel('campaignmanage/campaignonline')->load($campaignId);

        $raffle = Mage::getModel('campaignmanage/raffleonline')
            ->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId);

        if (count($raffle)) {
            foreach ($raffle as $item) {
                if ($item->getData('is_winner') == true) {
                    $data = array(
                        "type" => '6',
                        'activity_id' => $campaignId,
                        'product_id' => $item->getProductId(),
                        'selected_size' => $item->getOption(),
                        'is_card_payment' => $campaign->getIsCardPayment() ? true : false,
                        'size_name' => Mage::helper('campaignmanage')->getOptionLabel($item->getOption()),
                    );
                    $titleWinner = 'STORE_RAFFLE_TITLE_WINNER';
                    $msg = 'STORE_RAFFLE_CONTENT_WINNER';
                    $msg_args = array();
                    Mage::helper('pushnotification')
                        ->sendAction(array($item->getCustomerId()), $titleWinner, $msg, $msg_args, $data);
                }
            }
        }


    }

    public function sendNotificationToWinner($joiner)
    {
        $campaign = Mage::getModel('campaignmanage/campaignonline')->load($joiner->getRaffleId());
        $data = array(
            "type" => '6',
            'activity_id' => $joiner->getRaffleId(),
            'product_id' => $joiner->getProductId(),
            'selected_size' => $joiner->getOption(),
            'is_card_payment' => $campaign->getIsCardPayment() ? true : false,
            'size_name' => Mage::helper('campaignmanage')->getOptionLabel($joiner->getOption()),
        );
        $titleWinner = 'STORE_RAFFLE_TITLE_WINNER';
        $msg = 'STORE_RAFFLE_CONTENT_WINNER';
        $msg_args = array();
        Mage::helper('pushnotification')
            ->sendAction(array($joiner->getCustomerId()), $titleWinner, $msg, $msg_args, $data);
    }

    public function getOptions($productId, $campaignId)
    {
        $options = array();
        $optionArr = array();
        $product = Mage::getModel('campaignmanage/onlineproduct')->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId)
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem();
        $optionArr = explode(";", $product->getOptions());
        foreach ($optionArr as $option) {
            $option = explode(",", $option);
            $options[] = $option;
        }
        return $options;
    }

    public function setListWinnerByOption($optionValue, $qty, $productId, $campaignId, $reRandom = false)
    {
        $collection = Mage::getModel('campaignmanage/raffleonline')->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('option', $optionValue);
        if ($reRandom == true) {
            $collection->addFieldToFilter('is_winner', array(
                array('null' => 1),
                array('eq' => false)
            ));
            $collection->addFieldToFilter('assigned_loser', array(
                array('null' => 1),
                array('eq' => false)
            ));
        }
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'))
            ->limit($qty);
        foreach ($collection as $item) {
            if ($reRandom == true) {
                $item->setAssignedWinner(true);
            }
            $item->setIsWinner(true)->save();
        }
        return true;
    }

    public function setListWinnerByProduct($productId, $qty, $campaignId, $reRandom = false)
    {
        $collection = Mage::getModel('campaignmanage/raffleonline')->getCollection()
            ->addFieldToFilter('raffle_id', $campaignId)
            ->addFieldToFilter('product_id', $productId);
        if ($reRandom == true) {
            $collection->addFieldToFilter('is_winner', array(
                array('null' => 1),
                array('eq' => false)
            ));
            $collection->addFieldToFilter('assigned_loser', array(
                array('null' => 1),
                array('eq' => false)
            ));
        }
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'))
            ->limit($qty);
        foreach ($collection as $item) {
            if ($reRandom == true) {
                $item->setAssignedWinner(true);
            }
            $item->setIsWinner(true)->save();
        }
        return true;
    }

}
