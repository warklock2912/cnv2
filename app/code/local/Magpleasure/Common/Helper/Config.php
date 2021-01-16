<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * Reprieves Value from Path
     *
     * @param $path
     * @param $config
     * @return boolean|string
     */
    public function getValueFromPath($path, $config = null)
    {
        if (!$config){
            $config = Mage::app()->getConfig();
        }

        $data = $config->getNode($path);
        $result = (string)$data;
        if ($result){
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Retrieves Array of Values from Path
     *
     * @param $path
     * @param null $config
     * @param bool $hasParams
     * @return array|bool
     */
    public function getArrayFromPath($path, $config = null, $hasParams = false)
    {
        if (!$config){
            $config = Mage::app()->getConfig();
        }

        $data = $config->getNode($path);
        $result = (array)$data;
        if ($result && is_array($result)){
            $out = array();
            foreach ($result as $name => $attrs){
                if ($name){
                    if (!$hasParams){
                        $out[] = $name;
                    } else {
                        $value = $this->getValueFromPath($path."/".$name, $config);
                        if ($value){
                            $out[$name] = $value;
                        }
                    }
                }
            }
            return $out;
        } else {
            return false;
        }
    }

    protected function _getAttribute($path, $name, $config = null)
    {
        if (!$config){
            $config = Mage::app()->getConfig();
        }

        $node = $config->getNode($path);
        if ($attribute = $node->getAttribute($name)){
            return (string)$attribute;
        }

        return false;
    }

    public function getWhatTranslate($path, $config = null)
    {
        return $this->_getAttribute($path, 'translate', $config);
    }

    public function getWhoTranslate($path, $config = null)
    {
        return $this->_getAttribute($path, 'module', $config);
    }
}