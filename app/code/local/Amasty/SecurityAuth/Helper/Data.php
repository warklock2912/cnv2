<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


class Amasty_SecurityAuth_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOCAL_IP = '127.0.0.1';

    protected $_addressPath = array(
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    );

    /**
     * @return bool
     */
    public function isActive()
    {
        $ipWhiteList = explode(',', Mage::getStoreConfig('amsecurityauth/general/ip_white_list'));
        foreach ($ipWhiteList as $k =>&$v) {
            $v = trim($v);
        }
        $isWhiteIp = in_array($this->getCurrentIp(), $ipWhiteList);
        
        return (Mage::getStoreConfigFlag('amsecurityauth/general/active') && !$isWhiteIp);
    }

    /**
     * @param Amasty_SecurityAuth_Model_Auth $userAuth
     *
     * @return bool
     */
    public function isActiveForUser(Amasty_SecurityAuth_Model_Auth $userAuth)
    {
        return $userAuth->getEnable();
    }

    public function getCurrentIp()
    {
        foreach ($this->_addressPath as $path) {
            $ip = Mage::app()->getRequest()->getServer($path);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $addresses = explode(',', $ip);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($ip != self::LOCAL_IP) {
                        return $ip;
                    }
                }
            }
        }
        return Mage::helper('core/http')->getRemoteAddr();
    }
}
