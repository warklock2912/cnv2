<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


class Amasty_Customform_Validate_Processor extends Zend_Validate_Abstract
{
    private $info;

    private $lastMessage;

    public function __construct($formId)
    {
        $this->info = new Zend_Config($this->getFromFieldsInfo($formId));
        if ($this->info->count() != 2) {
            throw new Exception('Not correct validator info');
        }
    }

    public function isValid($value)
    {
        $this->lastMessage = '';
        if (!isset($value['formFieldId'])
            || !isset($value['fieldId'])
            || !isset($value['value'])
        ) {
            $this->lastMessage = "Invalid value format";
            return false;
        }

        return $this->validValue($value);
    }

    private function getFromFieldData($value){
        $data = $this->info->formData->get($value['formFieldId']);
        $this->checkCountData($data);
        return $data;
    }

    private function getFieldData($value){
        $data = $this->info->fieldData->get($value['fieldId']);
        $this->checkCountData($data);
        return $data;
    }

    private function checkCountData($data){
        if($data->count() == 0){
            throw new Exception('Bad validation form data.');
        }
    }


    private function validValue($value){
        try{
            $formFieldData = $this->getFromFieldData($value);
            $fieldData = $this->getFieldData($value);
            $maxLengthValue = (int)$fieldData->get('max_length');
            if(!empty($maxLengthValue)){
                if(!is_array($value['value'])){
                    if(strlen($value['value']) > $maxLengthValue){
                        throw new Exception('Not correct max length for field '. $fieldData->get('label'));
                    }
                }
            }
            $requireValue = $fieldData->get('required');
            if($requireValue && $value['value']== ''){
                throw new Exception('Field '.$fieldData->get('label').' is required');
            }
        }catch (Exception $e){
            $this->lastMessage = $e->getMessage();
            return false;
        }
        return true;
    }

    public function getLastMessage(){
        return $this->lastMessage;
    }

    private function getFromFieldsInfo($formId)
    {
        $result = array();
        $result['formData'] = array();
        $result['fieldData'] = array();
        /* array Amasty_Customform_Model_Form_Filed*/
        $formFields = $this->getFormFields($formId);


        /* array(id1,id2 ...) */
        $fieldIds = $this->getFieldIds($formFields);
        $storeLabels = $this->getStoreLabelsByIds($fieldIds);
        /* array Amasty_Customform_Model_Filed*/
        $fields = $this->getFields($fieldIds);
        foreach ($formFields as $formField) {
            $result['formData'][$formField->getId()] = $formField->getData();
            $result['fieldData'][$formField->getFieldId()]
                = $fields[$formField->getFieldId()]->getData();
            $result['fieldData'][$formField->getFieldId()]['label']
                = $storeLabels[$formField->getFieldId()];
        }
        return $result;

    }

    private function getStoreLabelsByIds($fieldIds){
        $result = array();
        if(empty($fieldIds)) {
            return $result;
        }
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $currentStore = Mage::app()->getStore()->getId();
        $fieldStoreTable = $resource->getTableName('amcustomform/field_store');
        $sql = 'SELECT '
                   .'admin_store.field_id AS field_id, '
                   .'IF(current_store.label = "" OR current_store.label IS NULL,admin_store.label,current_store.label) AS label '
                .'FROM '.$fieldStoreTable.' admin_store '
                .'LEFT JOIN '.$fieldStoreTable.' current_store '
                .'ON current_store.field_id = admin_store.field_id '
                .'WHERE admin_store.store_id = 0 '
                .'AND current_store.store_id = '.$currentStore
                .' AND admin_store.field_id IN ('.implode(",",$fieldIds).')';
        $source = $connection->raw_query($sql);

        while ($row = $source->fetch()) {
            $result[$row['field_id']] = $row['label'];
        }
        return $result;
    }

    private function getFieldIds($formFields)
    {
        if (empty($formFields)) {
            return array();
        }
        $ids = array();
        foreach ($formFields as $formField) {
            $fieldId = $formField->getFieldId();
            $ids[$fieldId] = $fieldId;
        }
        return $ids;
    }

    private function getFormFields($formId)
    {
        $form = Mage::getModel('amcustomform/form')->load($formId);
        if (!$form->getId()) {
            throw new Exception('Cannot find form with id ' . $formId);
        }
        /* array Amasty_Customform_Model_Form_Filed*/
        return $form->getChildrenFields();
    }

    private function getFields($fieldIds)
    {
        if (empty($fieldIds)) {
            return array();
        }
        /* array Amasty_Customform_Model_Filed*/
        $collection = Mage::getModel('amcustomform/field')->getCollection()
            ->addFieldToFilter('id', $fieldIds)->getItems();
        return $collection;
    }
}