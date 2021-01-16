<?php

class Tigren_Kpayment_Model_Charge extends Tigren_Kpayment_Model_Kpayment
{

    const ENDPOINT = 'charge';

    public function createKpaymentCreditCharge($data)
    {
        $this->_paymentMethod = 'kpayment_credit';
        $this->_quote->getShippingAddress()->setPaymentMethod($this->_paymentMethod);
        $this->_quote->getPayment()->importData(array('method' => $this->_paymentMethod));
        try {
            // Collect totals of the quote
            $this->_quote->collectTotals();

            // Save quote
            $this->_quote->save();

            // Create Order From Quote
            $service = Mage::getModel('sales/service_quote', $this->_quote);
            $service->submitAll();
            $incrementId = $service->getOrder()->getRealOrderId();
            /** @var Mage_Checkout_Model_Session $checkoutSession **/
            $checkoutSession = Mage::getSingleton('checkout/session');
            $checkoutSession->setLastQuoteId($this->_quote->getId())
                ->setLastSuccessQuoteId($this->_quote->getId())
                ->setLastOrderId($service->getOrder()->getId())
                ->setLastRealOrderId($incrementId);

            $checkoutSession->getQuote()->setIsActive(false)->save();
            // Log order created message
            Mage::log('Order created with increment id: '.$incrementId, null, 'kpayment_credit.log');

            $currency = Mage::app()->getStore()->getBaseCurrencyCode();
            $amount = $this->_quote->getData('grand_total');
            $additionalData = array(
                'mid' => $this->_helper->getMerchantIdCredit(),
                'tid' => $this->_helper->getTerminalIdCredit()
            );
            $params = array(
                'token' => $data['token'],
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Charge order: ' . $incrementId,
                'source_type' => 'card',
                'mode' => 'token',
                'reference_order' => $incrementId,
                'additional_data' => $additionalData,
                'savecard' => $data['saveCard']
            );

            $isPrivate = $this->_helper->getIsPrivateCredit();
            $this->_helper->logAPI('[CHARGE REQUEST Kpayment KpaymentCode]', 'credit');
            $this->_helper->logAPI($this->getUrl('kpayment_credit'), 'credit');
            $this->_helper->logAPI(array($params), 'credit');
            $response = $this->curl($params, $isPrivate);

            $this->_helper->logAPI('[CHARGE $response Kpayment KpaymentCode]', 'credit');
            $this->_helper->logAPI($response, 'credit');
            $createCharge = json_decode($response, true);
            $this->_helper->logAPI($createCharge, 'credit');
            return $createCharge;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    private function getUrl($id = '')
    {
        $this->_apiBaseUrl = $this->_helper->getConfigData($id,'api_base_url');
        return $this->_apiBaseUrl . DS . self::ENDPOINT;
    }

    public function curl($params, $private = null)
    {
        $this->_helper->logAPI('[Test request Kpayment KpaymentCode]', 'credit');
        $this->_helper->logAPI('chayvaodayko', 'credit');
        $options['headers'] = array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
        );
        $url = 'https://service.carnivalbkk.com/kpayment/charge/result';

        $post_data = http_build_query($params);
        $url = $url . '?' . $post_data;
        $this->_helper->logAPI('[Test request Kpayment Log url]', 'credit');
        $this->_helper->logAPI($url, 'credit');

        $ch = curl_init($url);
// var_dump($post_data);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
//        curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_VERBOSE, true);
//        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);
        $this->_helper->logAPI($response, 'credit');
        // $httpBody = substr($response, $contentSize);
        $this->_helper->logAPI('[Test $response Kpayment KpaymentCode]', 'credit');
        $this->_helper->logAPI($response, 'credit');

        return $response;
    }

    public function curl2($params, $private = null)
    {
        $this->_helper->logAPI('[Test request Kpayment KpaymentCode]', 'credit');
        $this->_helper->logAPI('chayvaodayko', 'credit');
        $options['headers'] = array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
        );
        // $url = 'https://service.carnivalbkk.com/kpayment/charge/result';
        $url = 'http://127.0.0.1/marginframe_carnivalbkk/kpayment/charge/result';
        // $params = array(
        //     '123',
        //     '2132'
        // );
        $post_data = http_build_query($params);
        $url = $url . '?' . $post_data;
        $this->_helper->logAPI('[Test request Kpayment Log url]', 'credit');
        $this->_helper->logAPI($url, 'credit');

        $ch = curl_init($url);
// var_dump($post_data);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
//        curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_VERBOSE, true);
//        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);
        $this->_helper->logAPI($response, 'credit');
        $httpBody = substr($response, $contentSize);
        $this->_helper->logAPI('[Test $response Kpayment KpaymentCode]', 'credit');
        $this->_helper->logAPI($httpBody, 'credit');
        //
        $response = array(
            'token' => 'tokn_prod_5513d0f75eb8712de7a125baaa985e220eb',
            'amount' => 1001,
            'currency' => 'THB',
            'description' => 'Charge order: 2009132862',
            'source_type' => 'card',
            'mode' => 'token',
        );
        $response = json_encode($response);
        return $response;
    }

}
