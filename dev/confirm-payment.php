<?php
    require_once '../app/Mage.php';
    require_once 'functions.php';
    checkIsLoggedIn();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    if ($data['order_no']) {
        $orderNo = $data['order_no'];
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderNo);
        if (!$order->getId()) {
            dataResponse(400, 'Order No is not exist');
            die();
        }

        $name = $data['name'] ? $data['name'] : null;
        $email = $data['email'] ? $data['email'] : null;
        $telephone = $data['telephone'] ? $data['telephone'] : null;
        $bathAmount = $data['bath_amount'] ? $data['bath_amount'] : null;
        $dateTime = $data['date_time'] ? $data['date_time'] : null;
        $bank = $data['bank'] ? $data['bank'] : null;
        $message = $data['message'] ? $data['message'] : null;
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        $date = date('Y-m-d-His', $currentTimestamp);
        $attachSlipPhoto = isset($data['attach_slip']) ? substr($data['attach_slip'], 0, -1) : null;
        $attachSlipPhotoName = $date . time();
        $attachSlipPhotoPath = Mage::getBaseDir('media') . DS . 'confirmpayment/';
        if (!is_dir($attachSlipPhotoPath)) {
            mkdir($attachSlipPhotoPath, 0777, true);
        }
        if ($attachSlipPhoto != null) {
            file_put_contents($attachSlipPhotoPath . $attachSlipPhotoName, base64_decode($attachSlipPhoto));
        }
        $dataUpdate = array(
            'order_no' => $orderNo,
            'name' => $name,
            'email' => $email,
            'tel' => $telephone,
            'amount' => $bathAmount,
            'bank' => $bank,
            'message' => $message,
            'date' => $dateTime
        );
        $confirmForm = Mage::getModel('confirmpayment/cpform');
        try {
            $confirmForm->setData($dataUpdate)->setAttachment($attachSlipPhotoName)->setStatus(1)->setId(NULL)->save();
            $confirm = Mage::getModel('confirmorder/confirm');
            $confirm->setOrderIncrementId($orderNo)
                ->setIsConfirmed(true)
                ->save();
            http_response_code(200);
            echo json_encode(array('status_code' => 200, 'message' => 'successful'));
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));

            // Mage::log($e, null, 'app-checkout-error.log');
        }
    } else {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => 'Invalid Post'));
    }
