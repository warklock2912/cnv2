<?php

class Tigren_Kpayment_Model_Kpayment
{
    protected $_publicKey;

    protected $_secretKey;

    protected $_apiBaseUrl;

    protected $_paymentMethod;

    protected $_helper;

    protected $_quote;

    public function __construct()
    {
        /** @var Tigren_Kpayment_Helper_Data $helper **/
        $helper = Mage::helper('kpayment');
        $this->_helper = $helper;
        $this->_publicKey = $this->_helper->getPublicKey();
        $this->_secretKey = $this->_helper->getSecretKey();
        $this->_quote = Mage::getModel('checkout/session')->getQuote();
    }

    public function create($params, $url, $isPrivate = false)
    {
        $response = $this->requestHTTP($params, $url, $isPrivate);
        return $response;
    }

    public function requestHTTP($params, $url, $private = false)
    {
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

    public function getHeaderPublic()
    {
        return array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
            'x-api-key: ' . $this->_publicKey,
        );
    }

    public function getHeaderPrivate()
    {
        return array(
            'Content-Type: ' . 'application/json; charset=UTF-8',
            'x-api-key: ' . $this->_secretKey,
        );
    }
}