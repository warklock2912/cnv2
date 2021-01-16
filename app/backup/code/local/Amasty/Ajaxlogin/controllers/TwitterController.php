<?php /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */

require_once 'AjaxloginController.php';

class Amasty_Ajaxlogin_TwitterController extends Amasty_Ajaxlogin_AjaxloginController
{
    const URL_REQUEST_TOKEN    = 'https://api.twitter.com/oauth/request_token';
    const URL_AUTHORIZE        = 'https://api.twitter.com/oauth/authorize';
    const URL_ACCESS_TOKEN    = 'https://api.twitter.com/oauth/access_token';
    const URL_ACCOUNT_DATA    = 'https://api.twitter.com/1.1/users/show.json';

    private $_user_id = 0;
    private $_screen_name = '';
    
    public function indexAction() {
        if (isset($_GET['oauth_token'])) {
            
        $oauth_nonce = md5(uniqid(rand(), true)); 
        $oauth_timestamp = time(); 
        
        $oauth_token = $_GET['oauth_token'];
        $oauth_verifier = $_GET['oauth_verifier'];

        $oauth_token_secret = Mage::getSingleton('core/session')->getData('oauth_token_secret');

        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode(self::URL_ACCESS_TOKEN)."&";
        $oauth_base_text .= urlencode("oauth_consumer_key=".Mage::helper('amajaxlogin/twitter')->getAppId()."&");
        $oauth_base_text .= urlencode("oauth_nonce=".$oauth_nonce."&");
        $oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
        $oauth_base_text .= urlencode("oauth_token=".$oauth_token."&");
        $oauth_base_text .= urlencode("oauth_timestamp=".$oauth_timestamp."&");
        $oauth_base_text .= urlencode("oauth_verifier=".$oauth_verifier."&");
        $oauth_base_text .= urlencode("oauth_version=1.0");

        $key = Mage::helper('amajaxlogin/twitter')->getSecretId()."&".$oauth_token_secret;

        $oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
        
        $url = self::URL_ACCESS_TOKEN;
        $url .= '?oauth_nonce='.$oauth_nonce;
        $url .= '&oauth_signature_method=HMAC-SHA1';
        $url .= '&oauth_timestamp='.$oauth_timestamp;
        $url .= '&oauth_consumer_key='.Mage::helper('amajaxlogin/twitter')->getAppId();
        $url .= '&oauth_token='.urlencode($oauth_token);
        $url .= '&oauth_verifier='.urlencode($oauth_verifier);
        $url .= '&oauth_signature='.urlencode($oauth_signature);
        $url .= '&oauth_version=1.0';
        
        $response = file_get_contents($url);
        parse_str($response, $result);
        
        
        $oauth_nonce = md5(uniqid(rand(), true));

        $oauth_timestamp = time();

        $oauth_token = $result['oauth_token'];
        $oauth_token_secret = $result['oauth_token_secret'];
        $screen_name = $result['screen_name'];

        $oauth_base_text = "GET&";
        $oauth_base_text .= urlencode(self::URL_ACCOUNT_DATA).'&';
        $oauth_base_text .= urlencode('oauth_consumer_key='.Mage::helper('amajaxlogin/twitter')->getAppId().'&');
        $oauth_base_text .= urlencode('oauth_nonce='.$oauth_nonce.'&');
        $oauth_base_text .= urlencode('oauth_signature_method=HMAC-SHA1&');
        $oauth_base_text .= urlencode('oauth_timestamp='.$oauth_timestamp."&");
        $oauth_base_text .= urlencode('oauth_token='.$oauth_token."&");
        $oauth_base_text .= urlencode('oauth_version=1.0&');
        $oauth_base_text .= urlencode('screen_name=' . $screen_name);

        $key = Mage::helper('amajaxlogin/twitter')->getSecretId() . '&' . $oauth_token_secret;
        $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

        $url = self::URL_ACCOUNT_DATA;
        $url .= '?oauth_consumer_key=' . Mage::helper('amajaxlogin/twitter')->getAppId();
        $url .= '&oauth_nonce=' . $oauth_nonce;
        $url .= '&oauth_signature=' . urlencode($signature);
        $url .= '&oauth_signature_method=HMAC-SHA1';
        $url .= '&oauth_timestamp=' . $oauth_timestamp;
        $url .= '&oauth_token=' . urlencode($oauth_token);
        $url .= '&oauth_version=1.0';
        $url .= '&screen_name=' . $screen_name;

        $response = file_get_contents($url);

        $user_data = json_decode($response);

        Mage::getSingleton('core/session')->setData('amajaxlogin_twitter_data', $this->objectToArray($user_data));
        Mage::getSingleton('core/session')->setData('amajaxlogin_twitter_token', $oauth_token);
        $this->getResponse()->setBody($this->__("Window will close automatically. Now you can login using your Twitter account."));
        $this->getResponse()->setBody("<script>setTimeout(function() {
                window.close();
            }, 500);</script>");
        }
    }
    
     public function iframeAction() {
         $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_twitter', 'amajaxlogin_twitter')
                             ->setTemplate('amasty/amajaxlogin/social/twitter.phtml');
         $this->getResponse()->setBody($block->toHtml());
     }
     
     public function loginAction() {
         $data = Mage::getSingleton('core/session')->getData('amajaxlogin_twitter_data');
         $oauth_token = Mage::getSingleton('core/session')->getData('amajaxlogin_twitter_token');
         if($data && $oauth_token)
            $this->_login($data, $oauth_token, 'tw', $this->__('Twitter'));
     }
  
    public function replaceJs($result)
    {
         $arrScript = array();
         $result['script'] = '';               
         preg_match_all("@<script type=\"text/javascript\">(.*?)</script>@s",  $result['message'], $arrScript);
         $result['message'] = preg_replace("@<script type=\"text/javascript\">(.*?)</script>@s",  '', $result['message']);
         foreach($arrScript[1] as $script){ 
             $result['script'] .= $script;                 
         }
         $result['script'] =  preg_replace("@var @s",  '', $result['script']); 
         return "<plaintext>" . Zend_Json::encode($result);
    } 
    
    public function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
 
        if (is_array($d)) {
        
            return $d;
        }
    }
}