<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Block_Adminhtml_Profile_Import_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        /** @var Mage_Core_Block_Text $scriptBlock */
        $scriptBlock = $this->getLayout()->createBlock('core/text', 'form_after');
        $scriptBlock->setText('<script type="text/javascript">
        function switchTabView(tabId, flag) {
            true == flag ? $(tabId).parentNode.style.display = "block" : $(tabId).parentNode.style.display = "none";
        }
        function switchScopeView(scope) {
            if (0 == scope) {
                $("can_create_new_entity").disabled = false;
                $("can_create_options").disabled = false;
            } else {
                $("can_create_new_entity").selectedIndex = 0;
                $("can_create_new_entity").disabled = true;
                $("can_create_options").selectedIndex = 0;
                $("can_create_options").disabled = true;
            }

        }
        </script>');
        $this->setChild('form_after', $scriptBlock);
        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
        $profile = Mage::registry('profile');

        $session = Mage::getSingleton('adminhtml/session');
        $values  = array();
        if ($session->hasData('profile_data')) {
            $values = $session->getData('profile_data');
            $session->unsetData('profile_data');
        } else if ($profile->getId()) {
            $values = $profile->getData();
        }
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        /** @var $helper EcommerceTeam_Dataflow_Helper_Data */
        $helper = Mage::helper('ecommerceteam_dataflow');
        /** @var Mage_Core_Model_Resource_Store_Collection $storeCollection */
        $storeCollection = Mage::getResourceModel('core/store_collection');
        $storeCollection->setLoadDefault(true);

        $scopeOptions = array();

        foreach ($storeCollection as $store) {
            /** @var Mage_Core_Model_Store $store */
            $scopeOptions[$store->getId()] = !$store->getId() ? $this->__('All Store Views') : $store->getName();
        }
        
        $fieldSet = $form->addFieldset('main_fieldset', array('legend' => $this->__('General Information')));
        
        $fieldSet->addField('name', 'text', array(
            'name'     => 'name',
            'label'    => $this->__('Profile Name'),
            'title'    => $this->__('Profile Name'),
            'required' => true,
        ));


        $adapters = $helper->arrayToOptionHash($helper->getAvailableAdapters('product'), 'model', 'name', false);
        if (count($adapters) > 1) {
            $fieldSet->addField('adapter_model', 'select', array(
                'name'     => 'adapter_model',
                'label'    => $this->__('Adapter'),
                'title'    => $this->__('Adapter'),
                'required' => true,
                'options'  => $adapters,
            ));
        } else {
            $models = array_keys($adapters);
            $fieldSet->addField('adapter_model', 'hidden', array(
                'name'     => 'adapter_model',
                'label'    => $this->__('Adapter'),
                'title'    => $this->__('Adapter'),
                'required' => true,
                'value'    => array_shift($models),
            ));
        }

        $fieldSet->addField('custom_column_mapping', 'select', array(
            'name'     => 'custom_column_mapping',
            'label'    => $this->__('Column Mapping'),
            'title'    => $this->__('Column Mapping'),
            'required' => true,
            'options'  => array(
                0 => $this->__('Attribute codes defined in first row'),
                1 => $this->__('Manually define column mapping'),
            ),
            'value'    => 0,
            'onchange' => 'switchTabView(\'ecommerceteam_dataflow_mapping\', this.value)',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldSet->addField('scope', 'select', array(
                'name'     => 'scope',
                'label'    => $this->__('Scope for Attribute Values'),
                'title'    => $this->__('Scope for Attribute Values'),
                'required' => true,
                'options'  => $scopeOptions,
                'onchange' => 'switchScopeView(this.value)',
                'value'    => 0,
            ));
        }

        $fieldSet->addField('can_create_new_entity', 'select', array(
            'name'     => 'can_create_new_entity',
            'label'    => $this->__('Create New Items'),
            'title'    => $this->__('Create New Items'),
            'required' => true,
            'options'  => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
            'value'    => 1,
            'disabled' => isset($values['scope']) && 0 != $values['scope'],
        ));

        $fieldSet->addField('update_existing', 'select', array(
            'name'     => 'update_existing',
            'label'    => $this->__('Update Existing Products'),
            'title'    => $this->__('Update Existing Products'),
            'required' => true,
            'options'  => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
            'value'    => 1,
            'disabled' => isset($values['scope']) && 0 != $values['scope'],
        ));

        $fieldSet->addField('sku_pattern', 'text', array(
            'name'     => 'sku_pattern',
            'label'    => $this->__('Sku transform pattern'),
            'title'    => $this->__('Sku transform pattern'),
            'required' => false,
            'disabled' => isset($values['scope']) && 0 != $values['scope'],
        ));

        $fieldSet->addField('can_create_options', 'select', array(
            'name'     => 'can_create_options',
            'label'    => $this->__('Create Options'),
            'title'    => $this->__('Create Options'),
            'required' => true,
            'options'  => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
            'value'    => 1,
            'disabled' => isset($values['scope']) && 0 != $values['scope'],
        ));

        $fieldSet->addField('can_create_categories', 'select', array(
            'name'     => 'can_create_categories',
            'label'    => $this->__('Create Categories'),
            'title'    => $this->__('Create Categories'),
            'required' => true,
            'options'  => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
            'value'    => 1,
        ));

        $fieldSet->addField('column_delimiter', 'text', array(
            'name'     => 'column_delimiter',
            'label'    => $this->__('Column Delimiter'),
            'title'    => $this->__('Column Delimiter'),
            'required' => true,
            'value'    => ',',
        ));

        $fieldSet->addField('option_delimiter', 'text', array(
            'note'  => $this->__('Separator for multiple select options.'),
            'name'     => 'option_delimiter',
            'label'    => $this->__('Option Delimiter'),
            'title'    => $this->__('Option Delimiter'),
            'required' => true,
            'value'    => ',',
        ));

        $fieldSet->addField('option_correction_percent', 'text', array(
            'name'     => 'option_correction_percent',
            'label'    => $this->__('Correction Factor for Options'),
            'title'    => $this->__('Correction Factor for Options'),
            'required' => true,
            'value'    => 0,
        ));

//        $fieldSet->addField('can_download_media', 'select', array(
//            'name'     => 'can_download_media',
//            'label'    => $this->__('Download Media from HTTP'),
//            'title'    => $this->__('Download Media from HTTP'),
//            'required' => true,
//            'options'  => array(
//                0 => $this->__('No'),
//                1 => $this->__('Yes'),
//            ),
//            'value'    => 0,
//        ));

        if (!empty($values)) {
            $form->setValues($values);
        }
        return parent::_prepareForm();
    }
}
