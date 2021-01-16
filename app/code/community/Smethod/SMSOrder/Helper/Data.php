<?php
class Smethod_SMSOrder_Helper_Data extends Mage_Core_Helper_Abstract {
	public function smsSend($phonenumber,$message){

		$username = Mage::getStoreConfig('SMSOrder/config_api/username', Mage::app()->getStore());
		$password = Mage::getStoreConfig('SMSOrder/config_api/password', Mage::app()->getStore());
		$message = iconv('UTF-8','TIS-620',$message);
		$sender = Mage::getStoreConfig('SMSOrder/config_api/sender', Mage::app()->getStore());;
		$package = Mage::getStoreConfig('SMSOrder/config_api/package', Mage::app()->getStore());;
		$ScheduledDelivery = '' ;
		$phonenumber = str_replace('-','',$phonenumber);
		$phonenumber = str_replace(' ','',$phonenumber);
		$phonenumber = trim($phonenumber);
		//$params['method']   = 'send';
        $params['username'] = $username;
        $params['password'] = $password;
 		$phonenumber = str_replace('+66', '0', $phonenumber);
        
        $params['msisdn']       = $phonenumber;
        $params['message']  = $message;
        $params['sender']     = $sender;
        $params['force']  = $package;

        if (is_null( $params['msisdn']) || is_null( $params['message']))
        {
            return FALSE;
        }

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.thaibulksms.com/sms_api.php");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $params));

		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

}
