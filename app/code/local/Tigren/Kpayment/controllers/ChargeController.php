<?php

class Tigren_Kpayment_ChargeController extends Mage_Core_Controller_Front_Action
{

    public function resultAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');

        $url = $this->getUrl('kpayment_credit');
        $params = $this->getRequest()->getParams();
        $private = true;
        $result =  $this->create($params, $url, $private);
        return  $this->getResponse()->setBody(($result));
        // return  $this->getResponse()->setBody(json_encode($result));
    }

    public function create($params, $url, $isPrivate = false)
    {
        $response = $this->requestHTTP($params, $url, $isPrivate);
        return $response;
    }

    public function getHeaderPrivate()
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        return array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
            'x-api-key: ' . $kHelper->getSecretKey(),
        );
    }

    public function getHeaderPublic()
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        return array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
            'x-api-key: ' . $kHelper->getPublicKey(),
        );
    }

    public function requestHTTP($params, $url, $private = false)
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        $kHelper->logAPI('[Params Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI($params, 'credit');
        // $params = json_decode($params);
        $kHelper->logAPI('[Params Kpayment KpaymentCode]', 'credit');
        $kHelper->logAPI($params, 'credit');
        $kHelper->logAPI('tét', 'credit');
        $options['headers'] = $this->getHeaderPublic();

        if ($private) {
            $options['headers'] = $this->getHeaderPrivate();
        } else {
            $options['headers'] = $this->getHeaderPublic();
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

//        echo'<pre>';var_dump($response);die;
//        echo'<pre>';var_dump($options);die;
//        Mage::log($response, null, 'muaxuancoemnhuchuabatdau.log', true);
//        var_dump($statusCode);
//
//        var_dump($this->_publicKey);
//        var_dump($this->_secretKey);
//        var_dump($url);
//        var_dump($options['headers']);
//        var_dump(json_encode($params));
//        var_dump(curl_error($ch));die;
        curl_close($ch);
        $httpBody = substr($response, $contentSize);

        return $httpBody;
    }

    private function getUrl($id = '')
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        $url = $kHelper->getConfigData($id,'api_base_url');
        return $url . DS . 'charge';
    }
}
