<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2014 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

abstract class Fontis_GoogleTagManager_Model_Source_Attributes_Abstract
{
    /**
     * @var string
     */
    protected $_eavCode = null;

    /**
     * @var Mage_Eav_Model_Config
     */
    protected $_eavConfigModel = null;

    /**
     * @var string[]
     */
    protected $_eavAttributes = null;

    /**
     * @var string[]
     */
    protected $_eavAttributesExclude = array();

    public function __construct()
    {
        $this->_eavConfigModel = Mage::getSingleton('eav/config');
        $this->_eavAttributes = $this->_eavConfigModel->getEntityAttributeCodes($this->_eavCode);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $attributesArray = array();
        foreach ($this->_eavAttributes as $attributeCode) {
            if (in_array($attributeCode, $this->_eavAttributesExclude)) {
                continue;
            }
            $special = $this->handleAttributeSpecially($attributeCode);
            if ($special) {
                $attributesArray[] = $special;
            } else {
                $attributeDetails = $this->_eavConfigModel->getAttribute($this->_eavCode, $attributeCode);
                $attributesArrayItem = array(
                    'position' => $attributeDetails->getPosition(),
                    'value' => $attributeCode,
                    'label' => ($attributeDetails->getFrontendLabel() ? $attributeDetails->getFrontendLabel() : $attributeCode) . " (" . $attributeCode . ")",
                );
                $attributesArray[] = $attributesArrayItem;
            }
        }

        $attributesArray = array_merge($attributesArray, $this->getExtraArrayElements());

        $position = array();
        $label = array();
        foreach ($attributesArray as $key => $row) {
            $position[$key] = $row['position'];
            $label[$key] = $row['label'];
        }
        array_multisort($position, SORT_ASC, $label, SORT_ASC, $attributesArray);
        return $attributesArray;
    }

    /**
     * @param string $attributeCode
     * @return array|null
     */
    protected function handleAttributeSpecially($attributeCode)
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getExtraArrayElements()
    {
        return array();
    }
}
