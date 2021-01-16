<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit_Tab_Titles extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        /** @var Amasty_Customform_Helper_Data $helper */
        $helper = Mage::helper('amcustomform');

        $form = new Varien_Data_Form();

        $formModel = $this->getFormModel();

        $fieldset = $form->addFieldset('titles_fieldset', array(
            'legend'    => $helper->__('Manage Titles'),
            'class'     => 'fieldset-wide',
        ));

        foreach ($this->getStores() as $store) {
            /** @var Mage_Core_Model_Store $store */

            $titleStoreFieldData = array(
                'name'      => 'title[' . $store->getId() . ']',
                'label'     => $store->getName(),
                'title'     => $store->getName(),
                'required'  => $store->getId() == 0,
                'value'     => $formModel->getTitle($store->getId()),
            );

            $fieldset->addField('title-' . $store->getId(), 'text', $titleStoreFieldData);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getStores()
    {
        /** @var Mage_Core_Model_Resource_Store_Collection $stores */
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }

    /**
     * @return Amasty_Customform_Model_Form
     */
    protected function getFormModel()
    {
        return Mage::registry('amcustomform_current_form');
    }

}