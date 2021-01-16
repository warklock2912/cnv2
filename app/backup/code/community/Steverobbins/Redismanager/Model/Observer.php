<?php
/*
* Copyright (c) 2014 www.magebuzz.com
*/

class Steverobbins_Redismanager_Model_Observer
{
    /**
     * Cached helper
     *
     * @var Steverobbins_Redismanager_Helper_Data
     */
    protected $_helper;

    public function flushAllRedisCache()
    {
        $flushThis = null;
        $hp = Mage::helper('redismanager');
        $config = Mage::getStoreConfig('redismanager/settings/includename');
        $includeName = array();
        if($config!=''){
            $includeName = explode(',', $config);
        }
        if(count($includeName)>0){
            foreach ($hp->getServices() as $service) {
                $serviceMatch = $service['host'] . ':' . $service['port'];
                if (in_array($service['name'], $includeName)
                    || in_array($serviceMatch, $flushed)
                    || (!is_null($flushThis) && $flushThis != $serviceMatch)
                ) {
                    continue;
                }
                try {
                    $hp->getRedisInstance(
                        $service['host'],
                        $service['port'],
                        $service['password'],
                        $service['db']
                    )->getRedis()->flushAll();
                    $flushed[] = $serviceMatch;
                    // Mage::getSingleton('core/session')->addSuccess($this->__('%s flushed', $serviceMatch));
                } catch (Exception $e) {
                    // Mage::getSingleton('core/session')->addError($e->getMessage());
                }
            }
        }
    }

    /**
     * Get helper
     *
     * @return Steverobbins_Redismanager_Helper_Data
     */
    protected function _getHelper()
    {
        if (is_null($this->_helper)) {
            $this->_helper = Mage::helper('redismanager');
        }
        return $this->_helper;
    }
}