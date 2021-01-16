<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Model_Observer 
{
    protected function _validateTime(){
        $validate = true;
        $cronTime = $customerAttributes = Mage::getStoreConfig("amsegments/general/time");
        
        if (!empty($cronTime)){
            $validate = false;
            
            $times = explode(",", $cronTime);
            
            $now = date("H", time()) * 60;
            
            foreach($times as $time){
                if ($now >= $time && $now < $time + 30){
                    $validate = true;
                    break;
                }
            }
        }
        return $validate;
    }
    
    function index()
    {
        if ($this->_validateTime()) {
            $process = Mage::getSingleton('index/indexer')->getProcessByCode("amsegemnts_indexer");
            $process->reindexEverything();
        }
    }   

    /**
         * Adds new conditions
         *
         * @param   Varien_Event_Observer $observer
     */
    public function handleNewConditions($observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();

        if (!is_array($cond)) {
            $cond = array();
        }

        $cond[] = array(
            'label' => Mage::helper('amsegments')->__('Customers Segmentation'),
            'value' => array(
                array(
                    'value' => 'amsegments/salesrule_segments',
                    'label' => Mage::helper('amsegments')->__('Segments'),
                )
            )
        );

        $transport->setConditions($cond);

        return $this;
    }
}