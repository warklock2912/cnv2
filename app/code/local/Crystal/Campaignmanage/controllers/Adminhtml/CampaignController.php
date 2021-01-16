<?php

class Crystal_Campaignmanage_Adminhtml_CampaignController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('campaignmanage/items')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Item Manager'),
                Mage::helper('adminhtml')->__('Item Manager')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function newAction()
    {
        if ($this->getRequest()->getParam('locator_id')) {
            $this->_forward('edit');
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('campaignmanage/campaign')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('campaign_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('campaignmanage/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
            $this->_addContent($this->getLayout()->createBlock(' campaignmanage/adminhtml_campaign_edit'))
                ->_addLeft($this->getLayout()
                    ->createBlock('campaignmanage/adminhtml_campaign_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('campaignmanage')->__('Item does not exist'));
            $this->_redirectReferer();
        }
    }

    public function saveAction()
    {
        $model = Mage::getModel('campaignmanage/campaign');
        if ($this->getRequest()->getParam('dealerlocator_id')) {
            $locator_id = $this->getRequest()->getParam('dealerlocator_id');
        } else
            $locator_id = $model->load($this->getRequest()->getParam('id'))['dealerlocator_id'];
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

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            # Process Timezone
            $datetimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

            //set start register time
            $startRegisterTime = new Zend_Date($model->getStartRegisterTime(), $datetimeFormat,'en_US');
            $startRegisterTime->addSecond(Mage::helper('mpblog')->getTimezoneOffset());
            $model->setStartRegisterTime($startRegisterTime->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));

            //set start register time
            $endRegisterTime = new Zend_Date($model->getEndRegisterTime(), $datetimeFormat,'en_US');
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
                if ($model->getCampaignType() == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE && $model->getIsWaiting() == NULL) {
                    $model->setIsWaiting(1);
                }
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('campaignmanage')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/index/id/' . $locator_id);
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('campaignmanage')->__('Unable to find item to save'));
        $this->_redirect('*/*/index/id/' . $locator_id);
    }

    public function deleteAction()
    {
        $model = Mage::getModel('campaignmanage/campaign');
        $locator_id = $model->load($this->getRequest()->getParam('id'))['dealerlocator_id'];
        if ($this->getRequest()->getParam('id') > 0) {
            try {

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $subcribleCustomers = Mage::getModel('campaignmanage/queue')->getCollection()
                    ->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'));
                if (count($subcribleCustomers)){
                    foreach ($subcribleCustomers as $customer){
                        $customer->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/index/id/' . $locator_id);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/index/id/' . $locator_id);
    }

    public function massDeleteAction()
    {
        $campaignIds = $this->getRequest()->getParam('campaignmanage');
        if (!is_array($campaignIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($campaignIds as $campaignId) {
                    $campaign = Mage::getModel('campaignmanage/campaign')
                        ->load($campaignId);
                    $campaign->delete();
                    $subcribleCustomers = Mage::getModel('campaignmanage/queue')->getCollection()
                        ->addFieldToFilter('campaign_id', $campaignId);
                    if (count($subcribleCustomers)){
                        foreach ($subcribleCustomers as $customer){
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

    public function startQueueAction()
    {
        $postData = $this->getRequest()->getPost();
        $campaignId = $postData['campaign_id'];
        $queue = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->setOrder('no_of_queue', 'ASC')
            ->getFirstItem();
        if ($queue->getId()) {
            $queue->setQueueStatus(2);
            try {
                $queue->save();
                $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
                if ($campaign->getCampaignType() == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_QUEUE) {
                    $data = array(
                        "type" => '3',
                        'activity_id' => $campaignId
                    );
                } else {
                    $data = array(
                        "type" => '5',
                        'activity_id' => $campaignId
                    );
                }

                $this->sendNotificationForQueue($campaignId, $data);

                $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
                $this->getResponse()->setBody(json_encode(array(
                    'success' => true,
                    'outputHtml' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_queue_content')->toHtml()
                )));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }


    public function nextQueueAction()
    {

        $postData = $this->getRequest()->getPost();
        $campaignId = $postData['campaign_id'];
        $statusInQueue = 1;
        $statusCurrent = 2;
        $queueCurrent = Mage::getModel('campaignmanage/queue')->getCollection()->addFieldToFilter('campaign_id', $campaignId)
            ->addFieldToFilter('queue_status', $statusCurrent)
            ->getFirstItem();

        $queueNext = Mage::getModel('campaignmanage/queue')->getCollection()->addFieldToFilter('campaign_id', $campaignId)
            ->addFieldToFilter('queue_status', $statusInQueue)
            ->setOrder('no_of_queue', 'ASC');
        if ($queueNext->getFirstItem()->getId()) {
            $queueNext = $queueNext->getFirstItem()->setQueueStatus(2);
        }
        try {
            $queueCurrent->setQueueStatus(3);
            $queueCurrent->setIsShowing(false);
            $queueCurrent->save();
            $queueNext->save();

            $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
            if ($campaign->getCampaignType() == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_QUEUE) {
                $data = array(
                    "type" => '3',
                    'activity_id' => $campaignId
                );
            } else {
                $data = array(
                    "type" => '5',
                    'activity_id' => $campaignId
                );
            }
            $this->sendNotificationForQueue($campaignId, $data);

            $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(json_encode(array(
                'success' => true,
                'outputHtml' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_queue_content')->toHtml()
            )));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function sendNotificationForQueue($campaignId, $data)
    {
        $queue_current = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->addFieldToFilter('queue_status', 2)
            ->getFirstItem();


        if ($queue_current->getId()) {
            $msg_for_current_content = 'STORE_QUEUE_YOUR_TURN_CONTENT';
            $msg_for_current_args =  array($queue_current->getNoOfId());
            $yourTurnTitle = 'STORE_QUEUE_YOUR_TURN_TITLE';
            Mage::helper('pushnotification')
                ->sendAction(array($queue_current->getCustomerId()), $yourTurnTitle, $msg_for_current_content, $msg_for_current_args, $data);
            $no_of_five = $queue_current->getNoOfQueue() + 5;

            $queue_five = Mage::getModel('campaignmanage/queue')->getCollection()
                ->addFieldToFilter('campaign_id', $campaignId)
                ->addFieldToFilter('no_of_queue', $no_of_five)
                ->getFirstItem();
            if ($queue_five->getId()) {
                $msgTitle = "STORE_QUEUE_CALL_SOON_TITLE";
                $no_of_ten = $queue_current->getNoOfQueue() + 10;
                $queue_ten = Mage::getModel('campaignmanage/queue')->getCollection()
                    ->addFieldToFilter('campaign_id', $campaignId)
                    ->addFieldToFilter('no_of_queue', $no_of_ten)
                    ->getFirstItem();
                $msg_for_five_content = 'STORE_QUEUE_CALL_SOON_CONTENT';
                $msg_for_five_agrs = array(
                    $queue_five->getNoOfId(), 5
                );
                Mage::helper('pushnotification')
                    ->sendAction(array($queue_five->getCustomerId()), $msgTitle, $msg_for_five_content, $msg_for_five_agrs, $data);

                if ($queue_ten->getId()) {
                    $msg_for_ten_content = $queue_ten->getNoOfId() . ': Another 10 queues will reach your queue';
                    $msg_for_ten_agrs = array(
                        $queue_ten->getNoOfId(), 10
                    );
                    Mage::helper('pushnotification')
                        ->sendAction(array($queue_ten->getCustomerId()), $msgTitle, $msg_for_ten_content, $msg_for_ten_agrs, $data);
                }
            }
        }
    }

    public function shuffleAction()
    {
        $collection = Mage::getModel('campaignmanage/queue')->getCollection();
        $campaignId = $this->getRequest()->getParam('id');
        $collection->addFieldToFilter('campaign_id', $campaignId);
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));

        $customerIDs = array();
        $msgTitle = "STORE_SH UFFLE_RANDOM_TITLE";
        $msgContent = 'STORE_SHUFFLE_RANDOM_CONTENT';
        $bodyAgrs = array();
        $data = array(
            "type" => '9',
            'activity_id' => $campaignId
        );

        foreach ($collection as $customer) {
            $customerIDs[] = $customer->getCustomerId();
        }

        if (!empty($customerIDs)) {
            Mage::helper('pushnotification')
                ->sendAction($customerIDs, $msgTitle, $msgContent, $bodyAgrs, $data);
        }

         $this->assignNoOfQueue($customerIDs, $campaignId);


        $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
        $campaign->setIsWaiting(false)->save();

        Mage::getSingleton('adminhtml/session')
            ->addSuccess(Mage::helper('adminhtml')->__('Shuffle  successfully'));
        $this->_redirectReferer();
    }



    private function assignNoOfQueue($customerIDs, $campaignID) {
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');

        /**
         * Retrieve the write connection
         */
        $writeConnection = $resource->getConnection('core_write');

        $query = $this->buildNoOfQueueQuery($customerIDs, $campaignID);
        /**
         * Execute the query
         */
        try {
            $writeConnection->query($query);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    private function buildNoOfQueueQuery($customerIDs, $campaignID) {
        $sqlQuery = "";
        if (!empty($customerIDs)) {
            $i = 0;
            $sqlQuery = "update campaign_subcribe_customer" .
                " set no_of_queue = (" .
                " CASE";
            foreach ($customerIDs as $customerID) {
                $i ++;
                $sqlQuery .= " WHEN customer_id= {$customerID} THEN " . $i;
            }
            $sqlQuery .= " END )";
            $sqlQuery .= " WHERE customer_id IN (" . implode(',', $customerIDs) . ")";
            $sqlQuery .= " AND campaign_id =" . $campaignID;
        }
        return $sqlQuery;
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_tab_item')->toHtml()
        );
//		$this->getLayout()->getBlock('campaign.grid');
//		$this->renderLayout();
    }

    public function productAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('campaign.edit.tab.item');
        $this->renderLayout();
    }

    public function addSelectedItemsAction()
    {
        $data = $this->getRequest()->getPost();
        $campaignId = $this->getRequest()->getParam('id');
        $productIds = explode('&', $data['product_ids']);
        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $size = Mage::helper('campaignmanage')->convertOptionsToString($productId);
                $model = Mage::getModel('campaignmanage/products');
                try {
                    $model->setCampaignId($campaignId)
                        ->setProductId($productId)
                        ->setOption($size)
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
		$campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);
        $productsRaffle = Mage::getModel('campaignmanage/products')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId);
        foreach ($productsRaffle as $product) :
            $product = Mage::getModel('catalog/product')->load($product->getProductId());
            $productId = $product->getId();
            if ($product->isConfigurable()) {
                $optionList = Mage::helper('campaignmanage')->getOptions($productId, $campaignId);
                foreach ($optionList as $option) :
                    $optionValue = $option[0];
                    $qty = $option[1];
                    Mage::helper('campaignmanage')->setListWinnerByOption($optionValue, $qty, $productId, $campaignId);
                endforeach;
            } else {
                $qty = (int)$product->getStockItem()->getQty();
                Mage::helper('campaignmanage')->setListWinnerByProduct($productId, $qty, $campaignId);
            }
        endforeach;
        $this->sendNotificationToAllMember($campaignId);
        $campaign->setEndRegisterTime(now());
        $campaign->setStatus(2);
        $campaign->save();
        $this->_getSession()->addSuccess(
            $this->__('Random successfully!')
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    public function sendNotificationToAllMember($campaignId)
    {
        $raffle = Mage::getModel('campaignmanage/raffle')->getCollection()->addFieldToFilter('campaign_id',$campaignId);

        $data = array(
            "type" => '4',
            'activity_id' => $campaignId
        );
        if(count($raffle)){
            $title = 'STORE_RAFFLE_TITLE_WINNER';
            $msg_content = 'STORE_RAFFLE_CONTENT_WINNER';
            $msg_args = array();

            foreach ($raffle as $item){
                Mage::helper('pushnotification')
                    ->sendAction(array($item->getCustomerId()), $title, $msg_content, $msg_args, $data);
            }
        }
    }

    public function endQueueAction(){
        $id = $this->getRequest()->getParam('id');
        $queue = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id',$id)
            ->addFieldToFilter('queue_status',array(1,2))
        ;
        $model = Mage::getModel('campaignmanage/campaign')->load($id);
        $model->setIsEnd(1);
        $model->save();
        if (count($queue)){
            foreach ($queue as $cusomter){
                $cusomter->setQueueStatus(3);
                $cusomter->save();
            }
        }
        $this->_getSession()->addSuccess(
            $this->__('successfully!')
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    public function exportCsvAction() {
        $fileName = 'campaign.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_grid')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'campaign.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_grid')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }


    public function exportQueueMemberCsvAction() {
        $fileName = 'member.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_tab_allmember')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportQueueMemberXmlAction() {
        $fileName = 'member.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_tab_allmember')->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportRaffleMemberCsvAction() {
        $fileName = 'rafflemember.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_raffle_member')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportRaffleMemberXmlAction() {
        $fileName = 'rafflemember.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_raffle_member')->getXml();

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
}
