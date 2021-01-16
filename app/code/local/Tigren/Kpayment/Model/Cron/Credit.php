<?php
class Tigren_Kpayment_Model_Cron_Credit
{
    public function updateOrderStatusCredit()
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');

        $periodTime = intval($kHelper->getPeriodTimeCredit());
        $periodTime = $periodTime ?: 60;

        $fromDate = date('Y-m-d H:i:s', strtotime('-10 day'));
        $toDate = date('Y-m-d H:i:s', strtotime('-10 minutes'));

        $paymentMethod = 'kpayment_credit';
        $read = $kHelper->readAdapter();
        $query = "SELECT main_table.*, sop.method, kpr.charge_id
            FROM sales_flat_order AS main_table
            INNER JOIN sales_flat_order_payment AS sop ON main_table.entity_id = sop.parent_id
            INNER JOIN kpayment_credit_reference AS kpr ON main_table.increment_id = kpr.order_increment
            WHERE main_table.status = 'pending'
                AND (main_table.created_at >= '".$fromDate."' AND main_table.created_at <= '".$toDate."')
                AND sop.method = '".$paymentMethod."'
        ;";
        $paymentTrans = $read->fetchAll($query);

        $kHelper->logAPI('[QUERY]', 'credit_cron');
        $kHelper->logAPI(array($query), 'credit_cron');
        $kHelper->logAPI('========== END ==========', 'credit_cron');

        foreach ($paymentTrans as $trans) {
            $this->_inquiryOder($trans, $periodTime);
        }

        return $this;
    }

    protected function _inquiryOder($trans, $periodTime)
    {
        $order = Mage::getModel('sales/order')->load($trans['entity_id']);

        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');

        $headerPrivate = array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
            'x-api-key: ' . $kHelper->getSecretKey()
        );

        $url = $this->getUrl('kpayment_credit') . '/charge/' . $trans['charge_id'];

        $kHelper->logAPI('[INQUIRY REQUEST Kpayment Credit Card]', 'credit_cron');
        $kHelper->logAPI($url, 'credit_cron');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerPrivate);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if(curl_errno($ch)){
            $kHelper->logAPI('[CURL ERROR]', 'credit_cron');
            $kHelper->logAPI(array(curl_error($ch)), 'credit_cron');
        }

        $responseMessage = "- **** Inquiry response from Cron Kpayment<br>";

        if ($response && is_array($response)) {
            foreach ($response as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $childKey => $childValue) {
                        $responseMessage .= $key . '[' . $childKey . ']' . ' = ' . $childValue . '<br>';
                    }
                } else {
                    $responseMessage .= $key . ' = ' . $value . '<br>';
                }
            }
        }

        $kHelper->logAPI('[RESPONSE]', 'credit_cron');
        $kHelper->logAPI( array($response), 'credit_cron');
        $kHelper->logAPI('========== END ==========', 'credit_cron');

        if (!empty($response['status']) && $response['status'] === 'success') {
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
            $order->addStatusToHistory($kHelper->getOrderStatusPaidCredit(), $responseMessage);
            $order->save();
            if ($kHelper->getCreateAutoInvoiceCredit()) {
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register();
                    $payment = $order->getPayment();
                    $payment->setCreatedInvoice($invoice)
                        ->setIsTransactionClosed(false)
                        ->setIsTransactionPending(true)
                        ->addTransaction(
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                            $invoice,
                            false,
                            $kHelper->__('Authorizing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                        );
                    $invoice->setEmailSent(true);
                    $order->addRelatedObject($invoice);
                    $order->addStatusHistoryComment($kHelper->__('Notified customer about invoice #%1.', $invoice->getId()))
                        ->setIsCustomerNotified(true)
                        ->save();
                }
            }

            $this->_updateInquiryStatus($trans['charge_id'], $order->getIncrementId(), $response['status']);

            return true;
        }
        elseif (!empty($response['status']) && $response['status'] == 'pending') {
            $order->addStatusHistoryComment($kHelper->__('Kbank transaction status : pending', $order->getStatus()))
                ->setIsCustomerNotified(false)
                ->save();
        }
        elseif (!empty($response['status']) && $response['status'] == 'fail') {
            $order->addStatusHistoryComment($kHelper->__('Kbank transaction status : fail', $order->getStatus()))
                ->setIsCustomerNotified(true)
                ->save();

            $cancelStatus = $kHelper->getOrderStatusCancelCredit();

            if ($order->canCancel()) {
                $order->cancel()
                    ->addStatusToHistory($cancelStatus, $responseMessage)
                    ->save();
            }
            else {
                $state = Mage_Sales_Model_Order::STATE_CANCELED;
                $order->setState($state)
                    ->addStatusToHistory($cancelStatus, $responseMessage)
                    ->save();
            }
        }

        $orderCreatedTime = strtotime('now') - strtotime($order->getCreatedAt());
        if($orderCreatedTime > $periodTime*60){
            $cancelStatus = $kHelper->getOrderStatusCancelCredit();
            if ($order->canCancel()) {
                $order->cancel()
                    ->addStatusToHistory($cancelStatus, $responseMessage)
                    ->save();
            }
            else {
                $state = Mage_Sales_Model_Order::STATE_CANCELED;
                $order->setState($state)
                    ->addStatusToHistory($cancelStatus, $responseMessage)
                    ->save();
            }
        }

        $this->_updateInquiryStatus($trans['charge_id'], $order->getIncrementId(), $response['status']);

        return false;
    }

    protected function _updateInquiryStatus($chargeId, $orderIncrement, $inquiryStatus = 'default')
    {
        try {
            /** @var Tigren_Kpayment_Helper_Data $kHelper **/
            $kHelper = Mage::helper('kpayment');

            $write = $kHelper->writeAdapter();
            $query = "UPDATE kpayment_credit_reference SET inquiry_status = :inquiryStatus, inquiry_date = NOW() WHERE charge_id = :chargeId AND order_increment = :orderIncrement;";
            $write->query($query, array(
                'chargeId' => $chargeId,
                'orderIncrement' => $orderIncrement,
                'inquiryStatus' => $inquiryStatus
                ));
        }
        catch(Exception $e){
            return array(
                'code' => '500',
                'message' => $e->getMessage()
            );
        }
        return array(
            'code' => '200',
            'message' => 'success'
        );
    }

    private function getUrl($id = '')
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        return $kHelper->getConfigData($id,'api_base_url');
    }
}