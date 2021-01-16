<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */
class Amasty_Ajaxlogin_Block_Social_Twitter extends Mage_Core_Block_Template
{
    
    const URL_REQUEST_TOKEN    = 'https://api.twitter.com/oauth/request_token';
    const URL_AUTHORIZE        = 'https://api.twitter.com/oauth/authorize';
    const URL_ACCESS_TOKEN    = 'https://api.twitter.com/oauth/access_token';
    const URL_ACCOUNT_DATA    = 'https://api.twitter.com/1.1/users/show.json';
    
    // Секретные ключи и строка возврата
    private $_consumer_key = '';
    private $_consumer_secret = '';
    private $_url_callback = '';
    // Масив некоторых данных oauth
    private $_oauth = array();
    
   
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getTwUrl()
    {
        return Mage::helper('amajaxlogin/twitter')->getUrl();
    }
    
    public function getTwitterConnectUrl()
    {
        $url = Mage::getUrl('amajaxlogin/twitter/login');
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getUrlParaml() 
    {
        $this->_url_callback = Mage::helper('amajaxlogin/twitter')->getUrl();
        $this->_consumer_key = Mage::helper('amajaxlogin/twitter')->getAppId();
        $this->_consumer_secret = Mage::helper('amajaxlogin/twitter')->getSecretId();
        $this->request_token();
        $url = self::URL_AUTHORIZE;
        $url .= '?oauth_token='.$this->_oauth['token'];

        return  $url;    
    }
    
    public function request_token()
    {
        $this->_init_oauth();
        
        // ПОРЯДОК ПАРАМЕТРОВ ДОЛЖЕН БЫТЬ ИМЕННО ТАКОЙ!
        // Т.е. сперва oauth_callback -> oauth_consumer_key -> ... -> oauth_version.
        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode(self::URL_REQUEST_TOKEN)."&";
        $oauth_base_text .= urlencode("oauth_callback=".urlencode($this->_url_callback)."&");
        $oauth_base_text .= urlencode("oauth_consumer_key=".$this->_consumer_key."&");
        $oauth_base_text .= urlencode("oauth_nonce=".$this->_oauth['nonce']."&");
        $oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
        $oauth_base_text .= urlencode("oauth_timestamp=".$this->_oauth['timestamp']."&");
        $oauth_base_text .= urlencode("oauth_version=1.0");
        
        // Формируем ключ
        // На конце строки-ключа должен быть амперсанд & !!!
        $key = $this->_consumer_secret."&";
        
        // Формируем oauth_signature
        $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
        
        // Формируем GET-запрос
        $url = self::URL_REQUEST_TOKEN;
        $url .= '?oauth_callback='.urlencode($this->_url_callback);
        $url .= '&oauth_consumer_key='.$this->_consumer_key;
        $url .= '&oauth_nonce='.$this->_oauth['nonce'];
        $url .= '&oauth_signature='.urlencode($signature);
        $url .= '&oauth_signature_method=HMAC-SHA1';
        $url .= '&oauth_timestamp='.$this->_oauth['timestamp'];
        $url .= '&oauth_version=1.0';
        
        // Выполняем запрос
        $response = file_get_contents($url);
        
        // Парсим строку ответа
        parse_str($response, $result);
        
        $this->_oauth['token'] = $result['oauth_token'];
        $this->_oauth['token_secret'] = $result['oauth_token_secret'];
        Mage::getSingleton('core/session')->setData('oauth_token', $this->_oauth['token']);
        Mage::getSingleton('core/session')->setData('oauth_token_secret', $this->_oauth['token_secret']);
        
    }
    
    private function _init_oauth()
    {
        // Формируем oauth_nonce
        $this->_oauth['nonce'] = md5(uniqid(rand(), true));
        
        // Получаем текущее время в секундах
        $this->_oauth['timestamp'] = time();
    }
    
}
 