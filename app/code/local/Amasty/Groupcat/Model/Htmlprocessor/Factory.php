<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


class Amasty_Groupcat_Model_Htmlprocessor_Factory
{

    const MODEL_CLASS_PROCESSOR = 'amgroupcat/htmlprocessor';
    const PROCESSOR_NAME        = 'Zendquery';

    /**
     * @return false|Amasty_Groupcat_Model_Htmlprocessor_Interface
     * @throws Exception
     */
    public function createProcessor()
    {
        $processorName = $this->getProcessorName();
        $modelClass = self::MODEL_CLASS_PROCESSOR . '_' . $processorName;
        $model = Mage::getModel($modelClass);
        if($model === false){
            throw new Exception('Undefined processor model');
        }
        return $model;
    }

    /**
     * @return Amasty_Groupcat_Helper_Abstract
     */
    private function _getHelper()
    {
        return Mage::helper('Amasty_Groupcat');
    }

    public function getProcessorName()
    {
        return self::PROCESSOR_NAME;
    }

}
