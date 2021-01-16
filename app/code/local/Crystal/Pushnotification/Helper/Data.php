<?php

    class Crystal_Pushnotification_Helper_Data extends Mage_Core_Helper_Abstract
    {
        public function getStatus()
        {
            return Mage::getStoreConfig('fcm_options/key_config/status');
        }

        public function getFcmKey()
        {
            $environment = Mage::getStoreConfig('fcm_options/key_config/env');
            if ($environment == 'dev') {
                return Mage::getStoreConfig('fcm_options/key_config/dev_key');
            }
            return Mage::getStoreConfig('fcm_options/key_config/live_key');
        }

        public function pushNotification($title, $message, $bodyArgs, $tokenArr, $data)
        {

            if (!$this->getStatus()){
                return;
            }

            $url = 'https://fcm.googleapis.com/fcm/send';

            $fields = array(
                'registration_ids' => $tokenArr,
                'priority' => "high",
                'content_available' => true,
                'data' => $data
            );

            if (isset($data['type']) && $data['type'] == 1) {
                $fields['notification'] = array(
                    'title' => $title,
                    'body' => $message
                );
            } else {
                $fields['notification'] = array(
                    'title_loc_key' => $title,
                    'body_loc_key' => $message,
                    "body_loc_args" => $bodyArgs
                );
            }

            $fields = json_encode($fields);

            Mage::log($fields, null, 'firebase_pushNotification.log');

            $key = '';
            $headers = array(
                'Authorization: key=' . $this->getFcmKey(),
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

        public function renameImage($image_name)
        {
            $string = str_replace("  ", " ", $image_name);
            $new_image_name = str_replace(" ", "-", $string);
            $new_image_name = strtolower($new_image_name);
            return $new_image_name;
        }

        public function sendAction($customerIdsArr, $title, $msg, $bodyArgs, $data)
        {
            if (!$this->getStatus()){
                return;
            }

            $product_id = null;
            $selected_size = null;
            $is_card_payment = null;
            if (!empty($data['product_id'])) {
                $product_id = $data['product_id'];
            }
            if (!empty($data['is_card_payment'])) {
                $is_card_payment = $data['is_card_payment'];
            }
            if (!empty($data['selected_size'])) {
                $selected_size = $data['selected_size'];
            }
//            $deviceCollection = Mage::getModel('pushnotification/device')->getCollection()->addFieldToFilter('user_id', array('in' => $customerIdsArr));
            foreach ($customerIdsArr as $userId) {
                $device = Mage::getModel('pushnotification/device')->getCollection()->addFieldToFilter('user_id', $userId)->getFirstItem();
                $notification_id = $this->saveNotificationList($device->getUserId(), $data, 0, $title, $msg, $bodyArgs, $product_id , $selected_size, $is_card_payment);
                $data['notification_id'] = $notification_id;
                $this->pushNotification($title, $msg, $bodyArgs, array($device->getDeviceToken()), $data);
            }
        }

        public function saveNotificationList($customerId, $data, $notificationStatus, $title, $msg, $bodyArgs, $productId = null, $selected_size = null,$is_card_payment = null)
        {
            $contentId = null;
            $notificationList = Mage::getModel('pushnotification/notificationlist');
            $currentTime = new Zend_Date();
            if (isset($data['type'])) {
                $type = $data['type'];
                if (isset($data['id'])) {
                    $contentId = $data['id'];
                }
                if (isset($data['activity_id'])) {
                    $contentId = $data['activity_id'];
                }

                if (!empty($contentId)) {
                    $notificationList->setCustomerId($customerId)
                        ->setCreatedAt($currentTime)
                        ->setType($type)
                        ->setContentId($contentId)
                        ->setTitle($title)
                        ->setShortContent($msg)
                        ->setContentArgs(implode(',', $bodyArgs))
                        ->setProductId($productId)
                        ->setSelectedSize($selected_size)
                        ->setIsCardPayment($is_card_payment)
                        ->setNotificationStatus($notificationStatus);

                    try {
                        $notification_id = $notificationList->save()->getId();
                        return $notification_id;
                    } catch (Exception $e) {

                    }
                }
            }
        }

        public function getBroadcastUrl(){
            $broadcastEnv =  Mage::getStoreConfig('fcm_options/key_config/broadcast_env');
            return '/topics/'.$broadcastEnv;
        }

        public function sendMessageToAllDevices($title, $message, $messageAgrs = null, $data)
        {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $topic = $this->getBroadcastUrl();
            $fields = array(
                'to' => $topic,
                'priority' => 'high',
                'content_available' => true,
                'data' => $data
            );
            $notification = array();
            if ($messageAgrs != null) {
                $notification = array(
                    'title_loc_key' => $title,
                    'body_loc_key' => $message,
                    "body_loc_args" => $messageAgrs
                );
            } else {
                $notification = array(
                    'title' => $title,
                    'body' => $message
                );
            }
            $fields['notification'] = $notification;
            $fields = json_encode($fields);
            //Mage::log('My log entry');
            Mage::log($fields, null, 'firebase_sendMessageToAllDevices.log');

            $headers = array(
                'Authorization: key=' . $this->getFcmKey(),
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
