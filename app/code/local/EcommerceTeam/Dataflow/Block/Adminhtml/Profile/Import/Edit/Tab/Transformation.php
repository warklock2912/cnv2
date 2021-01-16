<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_Edit_Tab_Transformation
    extends Mage_Adminhtml_Block_Abstract
{
    /**
     * @return EcommerceTeam_Dataflow_Model_Profile_Import
     */
    public function getProfile()
    {
        return Mage::registry('profile');
    }

    protected function _construct()
    {
        $this->setData('template', 'ecommerceteam/dataflow/import/profile/edit/transformation.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getTransformations()
    {
        return $this->getProfile()->getTransform();
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return array(
            'uppercase'            => $this->__('Uppercase'),
            'lowercase'            => $this->__('Lowercase'),
            'lowercase_capitilize' => $this->__('Lowercase & Capitilize All'),
            'ucfirst'              => $this->__('Capitilize'),
            'replace'              => $this->__('Replace*'),
            'round'                => $this->__('Round'),
            'ceil'                 => $this->__('Round fractions up (ceil)'),
            'floor'                => $this->__('Round fractions down (floor)'),
            'add'                  => $this->__('Add'),
            'multiply'             => $this->__('Multiply by'),
        );
    }
}
