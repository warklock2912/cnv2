<?php

class Crystal_Campaignmanage_Adminhtml_CropanddropController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('campaignmanage/cropanddrop');
    }
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('campaignmanage/cropanddrop')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Crop and Drop Manager'),
                Mage::helper('adminhtml')->__('Crop and Drop Manager')
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

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('campaignmanage/cropanddrop')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('cropanddrop_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('campaignmanage/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Crop and Drop Manager'), Mage::helper('adminhtml')->__('Crop and Drop Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Crop and Drop New'), Mage::helper('adminhtml')->__('Crop and Drop New'));
            $this->_addContent($this->getLayout()->createBlock(' campaignmanage/adminhtml_cropanddrop_edit'))
                ->_addLeft($this->getLayout()
                    ->createBlock('campaignmanage/adminhtml_cropanddrop_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('campaignmanage')->__('Item does not exist'));
            $this->_redirectReferer();
        }
    }

    public function saveAction()
    {
        $model = Mage::getModel('campaignmanage/cropanddrop');
        if ($data = $this->getRequest()->getPost()) {
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            try {
                if(isset($data['product_id'])){
                    $_product =Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($data['product_id']);
                    if(!$_product->getProductId() ){
                        Mage::getSingleton('adminhtml/session')->addError('Product does not exist or not in stock');
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
                if ($model->getCreatedAt() == NULL) {
                    $model->setCreatedAt(now())
                        ;
                }
                if($model->getTitle() == NULL){
                    $model->setTitle('CROP_AND_DROP_TITLE');
                    $model->setContent('CROP_AND_DROP_CONTENT');
                }
                $model->save();
                if($model->getSize() != null){
                    $model = Mage::getModel('campaignmanage/cropanddrop')->load($model->getId());
                    //send message to all devices
                    $title = $model->getTitle();
                    $message = $model->getContent();
                    $productId = $model->getProductId();
                    $size = $model->getSize();
                    $_product = Mage::getSingleton('catalog/product')->load($productId);
                    $messageAgrs = array($_product->getName());

                    $currentTime = new Zend_Date();
                    $model_notification = Mage::getModel('pushnotification/notification');
                    $model_notification->setData('notification_date',$currentTime);
                    $model_notification->setData('created_at',$currentTime);
                    $model_notification->setData('message',$message);
                    $model_notification->setData('message_args',$messageAgrs);
                    $model_notification->setData('title',$title);
                    $model_notification->setData('url','');
                    $model_notification->setData('type','crop');
                    $model_notification->setData('is_sent',1);
                    $model_notification->save();

                    $messageData = array(
                        'type' => '7',
                        'id' => $model->getId(),
                        'notification_id' => $model_notification->getId(),
                        'product_id' => $productId,
                        'selected_size' => $size,
                        'size_name' => Mage::helper('campaignmanage')->getOptionLabel($size),
                    );
                    Mage::helper('pushnotification')->sendMessageToAllDevices($title, $message, $messageAgrs, $messageData);
                    $model->setData('notification_id',$model_notification->getId());
                    $model->save();
                }
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
        $model = Mage::getModel('campaignmanage/cropanddrop');
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
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
                    $campaign = Mage::getModel('campaignmanage/cropanddrop')
                        ->load($campaignId);
                    $campaign->delete();
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

    public function exportCsvAction() {
        $fileName = 'cropanddrop.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_cropanddrop_grid')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'cropanddrop.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_cropanddrop_grid')->getXml();

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
