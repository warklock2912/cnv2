<?php

class Tigren_CustomerQueue_Model_Observer extends Varien_Object
{
    public function checkCustomerQueue()
    {
        $resource = Mage::getSingleton('core/resource');
        $coreTable = $resource->getTableName('core_config_data');
        $readConnection = $resource->getConnection('core_read');
        $isEnabled = $readConnection->fetchOne("select value from $coreTable where path='customerqueue/general/enable'");
        if(!$isEnabled)
        {
            return;
        }
        if(isset($_COOKIE['queue_valid']) && $_COOKIE['queue_valid'] == 1)
        {
            return;
        }
       if(isset($_COOKIE['queue_session_id']) && !$this->checkIfInQueue())
       {
           setcookie('queue_valid', 1, time() + (86400 * 30), "/");
           return;
       }else
       {
           Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl().'landing.php?ref_link='.urlencode(Mage::helper('core/url')->getCurrentUrl()));
           return;
       }
    }
    function checkIfInQueue()
    {
        $isInQueue = false;
        $vistordata = fopen("visitordata.csv", "a");
        if ($vistordata) {
            while (($line = fgetcsv($vistordata)) !== false) {
                if(isset($line['0']))
                {
                    if($line['0'] == $_COOKIE['queue_session_id']) /// still in queue
                    {
                        $isInQueue = true;
                    }
                }
            }
        }else
        {
            $isInQueue = true;
        }
        return $isInQueue;
    }
}