<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Ajaxlogin
 */

require_once 'AjaxloginController.php';

class Amasty_Ajaxlogin_FacebookController extends Amasty_Ajaxlogin_AjaxloginController
{
    private $_params = array();    
    
    public function indexAction() {
        if (isset($_GET['code'])) {
            $this->_params = array(
                'client_id'     => Mage::helper('amajaxlogin/facebook')->getAppId(),
                'redirect_uri'  => Mage::helper('amajaxlogin/facebook')->getUrl(),
                'client_secret' => Mage::helper('amajaxlogin/facebook')->getSecretId(),
                'code'          => $_GET['code']
            );

            $url = 'https://graph.facebook.com/oauth/access_token';
            $tokenInfo = null;
            // parse_str(file_get_contents($url . '?' . http_build_query($this->_params)), $tokenInfo);
            $tokenInfo = json_decode(file_get_contents($url . '?' . http_build_query($this->_params)), true);
            if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
                $token = $tokenInfo['access_token'];
                $this->_params = array('access_token' => $token);
                $url = 'https://graph.facebook.com/me';
                $userInfo = json_decode(file_get_contents($url . '?' . urldecode(http_build_query($this->_params)) . '&fields=id,name,first_name,last_name,email'), true);
                if (isset($userInfo['id'])) {
                    $this->_login($userInfo, $token, 'fb', $this->__('Facebook'));
                }

            }
        
        }
    }
    
     public function iframeAction() {
         $block = Mage::app()->getLayout()->createBlock('amajaxlogin/social_facebook', 'amajaxlogin_facebook')
                             ->setTemplate('amasty/amajaxlogin/social/facebook.phtml');
         $this->getResponse()->setBody($block->toHtml());
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
}