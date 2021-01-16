<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Form_Submit setFormId($formId)
 * @method int getFormId()
 * @method Amasty_Customform_Model_Form_Submit setStoreId($storeId)
 * @method int getStoreId()
 * @method Amasty_Customform_Model_Form_Submit setSubmitted($datetime)
 * @method DateTime getSubmitted()
 * @method Amasty_Customform_Model_Form_Submit setCustomerId($customerId)
 * @method int getCustomerId()
 */
class Amasty_Customform_Model_Form_Submit extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amcustomform/form_submit');
    }

    public function getForm()
    {
        /** @var Amasty_Customform_Model_Form $form */
        $form = Mage::getModel('amcustomform/form');
        $form->load($this->getFormId());

        return $form;
    }

    public function saveFiles(){
        $fileFields = Mage::registry('customform_file_fields');

    }


    public function getValuesData()
    {
        $data = $this->getValues();
        if(!empty($data)){
            try{
                $data = unserialize($data);
            }catch(Exception $e){
                $data = array();
            }
        }
        return $data;
    }

    private function getMatch($value,$key){
        if(isset($value[$key]) && !empty($value[$key])){
            return $value[$key];
        }
        $error = 'Bad field id ';
        if(isset($value[0])){
            $error .= $value[0];
        }
        throw new Exception($error);
    }

    public function setValues($data){
        $formIds = array();
        $values = array();
        $fieldIds = array();
        $fieldsWithOptionsIds = array();
        $inputTypesWithOptions = array(
            Amasty_Customform_Helper_Data::INPUT_TYPE_SELECT,
            Amasty_Customform_Helper_Data::INPUT_TYPE_MULTISELECT
        );
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $valuesYesNo = array('No','Yes');
        foreach ($data as $key => $value) {
            if (preg_match('/^.*_([\d]+)_([\d]+)_([a-z]+)$/', $key, $matches)) {

                $formFieldId = $this->getMatch($matches,1);
                $formIds[$formFieldId] = $formFieldId;

                $fieldId = $this->getMatch($matches,2);
                $fieldIds[$fieldId] = $fieldId;

                $inputType = $this->getMatch($matches,3);
                if(in_array($inputType,$inputTypesWithOptions)){
                    $fieldsWithOptionsIds[] = $fieldId;
                }
                if(strcmp('boolean',$inputType)==0){
                    if(in_array($value,array(0,1))){
                        $value = $valuesYesNo[$value];
                    }else{
                        $value = $valuesYesNo[0];
                    }
                }

                $values[] = array(
                    'formFieldId' => $formFieldId,
                    'fieldId' => $fieldId,
                    'value' => $value
                );
            }
        }

        $collection = Mage::getModel('amcustomform/field_store')->getCollection()
            ->addFieldToFilter('store_id',0)
            ->addFieldToFilter('field_id',$fieldIds)->getItems();

        $storeLabels = array();
        foreach ($collection as $storeField) {
            $storeLabels[$storeField->getFieldId()] = $storeField->getLabel();
        }

        $validator = new Amasty_Customform_Validate_Processor($this->getFormId());
        $optionValues = array();
        foreach($values as $value){
            if(!$validator->isValid($value)){
                return false;
            }
            if(in_array($value['fieldId'],$fieldsWithOptionsIds)){
                if(is_array($value['value'])){
                    $optionValues = array_merge($optionValues,$value['value']);
                }else{
                    array_push($optionValues,$value['value']);
                }
            }
        }

        if(!empty($optionValues)){
            $optionStoreTable = $resource->getTableName('amcustomform/field_option_store');
            $optionTable = $resource->getTableName('amcustomform/field_option');
            $sql = "SELECT options.id AS id, store_options.label as label FROM `".$optionStoreTable."` store_options LEFT JOIN `".$optionTable."` options ON options.id = store_options.field_option_id WHERE store_options.store_id = 0 AND options.field_id IN (".implode(',',$fieldsWithOptionsIds).") AND options.id in(".implode(',',$optionValues).")";
            $source = $connection->raw_query($sql);
            $optionsLabels = array();
            while ($row = $source->fetch()) {
                $optionsLabels[$row['id']] = $row['label'];
            }
        }

        foreach ($values as &$val) {
            $val['label'] = $storeLabels[$val['fieldId']];
            if(in_array($val['fieldId'],$fieldsWithOptionsIds)){
                $labels = array();
                if(is_array($val['value'])){
                    foreach($val['value'] as $value){
                        $labels[] = $optionsLabels[$value];
                    }
                    $val['value'] = implode(',',$labels);
                }else{
                    $val['value'] = $optionsLabels[$val['value']];
                }
            }
            unset($val['fieldId']);
            unset($val['formFieldId']);
        }

        $this->setData('values',serialize($values));
        return true;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

}