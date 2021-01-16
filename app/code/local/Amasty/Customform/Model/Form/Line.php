<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Form_Line setFormId($formId)
 * @method int getFormId()
 * @method Amasty_Customform_Model_Form_Line setSortOrder($sortOrder)
 * @method int getSortOrder()
 * @method Amasty_Customform_Model_Form_Line setIsDeleted($isDeleted)
 * @method bool getIsDeleted()
 */
class Amasty_Customform_Model_Form_Line extends Amasty_Customform_Model_Mappable
{
    protected function _construct()
    {
        $this->_init('amcustomform/form_line');
    }

    protected function _beforeSave()
    {
        if (isset($this->childEntityCollections['form_field'])) {
            /** @var Amasty_Customform_Helper_Data $helper */
            $helper = Mage::helper('amcustomform');
            $helper->normalizeSortOrder($this->childEntityCollections['form_field']);
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        if (!$this->isDeleted()) {
            if ($this->getData('is_deleted') == 1 && $this->getOrigData('is_deleted') == 0) {
                foreach ($this->getActiveFormFields() as $formField) {
                    /** @var Amasty_Customform_Model_Form_Field $formField */
                    $formField->setIsDeleted(1);
                    $formField->save();
                }
            }
        }
    }

    protected function setupRelations()
    {
        $formFieldRelation = new Amasty_Customform_Model_Mappable_Relation('form_field');
        $formFieldRelation
            ->setChildEntityId('amcustomform/form_field')
            ->setJoinColumn('line_id')
        ;
        $this->registerChildRelation($formFieldRelation);
    }

    public function getActiveFormFields()
    {
        $result = array();
        $collection = $this->getChildrenCollection($this->getRelation('form_field'));
        foreach ($collection as $line) {
            /** @var Amasty_Customform_Model_Form_Line $line */
            if (!$line->getIsDeleted()) {
                $result[] = $line;
            }
        }

        return $result;
    }

    public function getForm()
    {
        /** @var Amasty_Customform_Model_Form $form */
        $form = Mage::getModel('amcustomform/form');
        $form->load($this->getFormId());

        return $form;
    }
}