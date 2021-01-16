<?php

class Crystal_Pushnotification_Adminhtml_PushnotificationController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('pushnotification/items')
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
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('pushnotification/notification')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('notification_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('pushnotification/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
            $this->_addContent($this->getLayout()->createBlock('pushnotification/adminhtml_pushnotification_edit'))
                ->_addLeft($this->getLayout()->createBlock('pushnotification/adminhtml_pushnotification_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pushnotification')->__('Item does not exist'));
            $this->_redirect('*/*/index');
        }
    }

    public function saveAction()
    {
        $model = Mage::getModel('pushnotification/notification');
        if ($data = $this->getRequest()->getPost()) {

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            try {
                $currentTime = Varien_Date::now();
                if ($model->getCreatedAt == NULL) {
                    $model->setCreatedAt($currentTime);
                } else {
                    $model->setUpdatedAt($currentTime);
                }

                $model->save();
                //send message to all devices
                $title = $model->getTitle();
                $message = $model->getMessage();
                $url = $model->getUrl();
                $messageData = array(
                    'type' => '2',
                    'notification_id' => $model->getNotificationId().'',
                    'url' => $url
                );
                Mage::helper('pushnotification')->sendMessageToAllDevices($title, $message, null, $messageData);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pushnotification')->__('Message was successfully sent'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pushnotification')->__('Unable to find item to save'));
        $this->_redirect('*/*/index/');
    }

    public function deleteAction()
    {
        $model = Mage::getModel('pushnotification/notification');
        if ($this->getRequest()->getParam('id') > 0) {
            try {

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/index/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/index');
    }


    public function massDeleteAction()
    {
        $notificationIds = $this->getRequest()->getParam('pushnotification');
        if (!is_array($notificationIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($notificationIds as $notificationId) {
                    $notification = Mage::getModel('pushnotification/notification')
                        ->load($notificationId);
                    $notification->delete();
                }
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($notificationIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function sendMessageToAllDevices($title, $message, $data)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $topic = '/topics/messageBroadcast';
        $fields = array(
            'to' => $topic,
            'priority' => 'high',
            'notification' => array(
                'title' => $title,
                'body' => $message
            ),
            'content_available' => true,
            'data' => $data
        );
        $fields = json_encode($fields);

        Mage::log($fields, null, 'firebase_sendMessageToAllDevices2.log');

        $key = Crystal_Pushnotification_Helper_Data::SERVER_KEY_FCM;
        $headers = array(
            'Authorization: key=' . $key,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);
    }

}
