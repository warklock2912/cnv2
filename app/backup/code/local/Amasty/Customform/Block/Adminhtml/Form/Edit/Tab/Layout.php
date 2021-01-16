<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit_Tab_Layout extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amcustomform/form/layout.phtml');
    }

    protected function _prepareLayout()
    {
        $lineDeleteButton = $this->getLayout()->createBlock('adminhtml/widget_button');
        $lineDeleteButton->setData(array(
            'label' => Mage::helper('catalog')->__('Delete Line'),
            'class' => 'delete delete-line'
        ));
        $this->setChild('line_delete_button', $lineDeleteButton);

        $lineAddButton = $this->getLayout()->createBlock('adminhtml/widget_button');
        $lineAddButton->setData(array(
            'label' => Mage::helper('catalog')->__('Add Line'),
            'class' => 'add',
            'id'    => 'add_new_line_button'
        ));
        $this->setChild('line_add_button', $lineAddButton);

        return parent::_prepareLayout();
    }

    protected function getLinesData()
    {
        $result = array();

        foreach ($this->getFormEntity()->getActiveLines() as $line) {
            /** @var Amasty_Customform_Model_Form_Line $line */
            $lineResult = $line->getData();

            $formFieldsResult = array();
            $activeFormFields = $line->getActiveFormFields();
            foreach ($activeFormFields as $id => $formField) {
                /** @var Amasty_Customform_Model_Form_Field $formField */
                $formFieldsResult[$id] = $formField->getData();
            }
            $lineResult['form_fields'] = $formFieldsResult;

            $result[] = json_encode($lineResult);
        }

        return $result;
    }
    protected function getFormatDate(){
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    protected function getFieldTypes()
    {
        $result = array();

        $collection = Mage::getModel('amcustomform/field')
            ->getCollection()
        ->addFieldToFilter('is_deleted','0');
        $collection->load();

        foreach($collection as $field) {
            /** @var Amasty_Customform_Model_Field $field */
            $fieldData = $field->getData();

            $fieldData['options'] = array();
            foreach ($field->getFieldOptions() as $option) {
                /** @var Amasty_Customform_Model_Field_Option $option */
                $fieldData['options'][$option->getId()] = $option->getLabel();
                if($option->getIsDefault()){
                    $fieldData['default_value'] = $option->getId();
                }
            }

            $result[$field->getId()] = $fieldData;
        }

        return $result;
    }

    protected function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }

    /**
     * @return Amasty_Customform_Model_Form
     */
    protected function getFormEntity()
    {
        return Mage::registry('amcustomform_current_form');
    }
}