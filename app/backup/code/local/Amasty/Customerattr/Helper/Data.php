<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_memberAlias = array();

    public function fields($fields = array(), $deleteAsterisk = false)
    {
        $html = Mage::app()->getLayout()->createBlock(
            'amcustomerattr/customer_fields'
        )
            ->setData('fields', $fields)
            ->toHtml();
        if ($deleteAsterisk) {
            $html = str_replace(
                '<span class="required">*</span>',
                '<span class="required"></span>', $html
            );
        }
        return $html;
    }

    public function getAttributesHash()
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();


        $filters = array(
            "is_user_defined = 1",
            "frontend_input != 'file' ",
            "frontend_input != 'multiselect' "
        );
        $collection = $this->addFilters(
            $collection, 'eav_attribute', $filters

        );

        $filters = array(
            array(
                "key" => "OR",
                "type_internal = 'statictext' ",
                array(
                    'cond'  => 'backend_type =',
                    'value' => "'varchar'",
                    'table' => 'eav_attribute'
                )
            )
        );
        $collection = $this->addFilters(
            $collection, 'customer_eav_attribute', $filters
        );

        $attributes = $collection->load();
        $hash = array();
        foreach ($attributes as $attribute) {
            $hash[$attribute->getAttributeCode()]
                = $attribute->getFrontendLabel();
        }
        return $hash;
    }

    public function checkInterface($value)
    {
        if (!isset($value['cond']) || !isset($value['value'])) {
            Mage::throwException(
                Mage::helper('amcustomerattr')->__(
                    'Amasty error. Bad filter for select'
                )
            );
        }
    }

    public function addFilters($collection, $tableName, $filters = array(),
        $sorting = null
    ) {
        $aliasDefault = $this->getProperAlias(
            $collection->getSelect()->getPart('from'), $tableName
        );
        $select = $collection->getSelect();
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $key = '';
                $where = '';
                if (is_array($filter) && isset($filter['key'])) {
                    $key = $filter['key'];
                    unset($filter['key']);
                    $len = count($filter);
                    $i = 1;
                    foreach ($filter as $val) {
                        $rKey = '';
                        if ($len > $i++) {
                            $rKey = $key;
                        }
                        if (is_array($val)) {
                            $this->checkInterface($val);

                            if (isset($val['table'])) {
                                $alias = $this->getProperAlias(
                                    $collection->getSelect()->getPart('from'),
                                    $val['table']
                                );
                            } else {
                                $alias = $aliasDefault;
                            }
                            $where .= ' ' . $alias . $val['cond']
                                . $val['value']
                                . ' ' . $rKey;
                        } else {
                            $alias = $aliasDefault;
                            $where .= ' ' . $alias . $val . ' ' . $rKey;
                        }
                    }
                } else if (is_array($filter)) {
                    $this->checkInterface($filter);
                    if (isset($filter['table'])) {
                        $alias = $this->getProperAlias(
                            $collection->getSelect()->getPart('from'),
                            $filter['table']
                        );
                    } else {
                        $alias = $aliasDefault;
                    }
                    $where = ' ' . $alias . $filter['cond'] . $filter['value'];

                } else {
                    $where = $aliasDefault . $filter;
                }
                if (!empty($where)) {
                    $select->where($where);
                }
            }
        }

        if ($sorting) {
            $select->order($aliasDefault . $sorting);
        }

        return $collection;
    }

    public function getProperAlias($from, $needTableName)
    {
        $needTableName = Mage::getConfig()->getTablePrefix() . $needTableName;
        $key = serialize($from) . $needTableName;
        if (isset($this->_memberAlias[$key])) {
            return $this->_memberAlias[$key];
        }

        foreach ($from as $key => $table) {
            $fullTableName = explode('.', $table['tableName']);
            if (isset($fullTableName[1])) {
                $tableName = $fullTableName[1];
            } else {
                $tableName = $fullTableName[0];
            }
            if ($needTableName == $tableName) {
                return $key . '.';
            }
        }
        return '';
    }

    public function getAttributeImageUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR .
            'amcustomerattr' . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg')) {
            return Mage::getBaseUrl('media') . '/' . 'amcustomerattr' . '/'
            . 'images' . '/' . $optionId . '.jpg';
        }
        return '';
    }

    public function deleteFile($fileName)
    {
        $fileName = str_replace('/', DS, $fileName);
        @unlink($this->getAttributeFileUrl($fileName) . $fileName);
    }

    public function getAttributeFileUrl($fileName, $download = false,
        $front = false, $customerId = null
    ) {
        // files directory
        $fileDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 'customer';
        $this->checkAndCreateDir($fileDir);
        if (false === strpos($fileName, DIRECTORY_SEPARATOR)) {
            $fileDir .= DIRECTORY_SEPARATOR . $fileName[0];
            $this->checkAndCreateDir($fileDir);
            $fileDir .= DIRECTORY_SEPARATOR . $fileName[1];
            $this->checkAndCreateDir($fileDir);
        } else {
            $temp = $this->cleanFileName($fileName);
            $tempFileDir = $fileDir . DIRECTORY_SEPARATOR . $temp[1];
            $this->checkAndCreateDir($tempFileDir);
            $tempFileDir = $tempFileDir . DIRECTORY_SEPARATOR . $temp[2];
            $this->checkAndCreateDir($tempFileDir);
        }

        if ($download) { // URL for download
            if (file_exists($fileDir . DIRECTORY_SEPARATOR . $fileName)) {
                if ($front) {
                    return Mage::getModel('core/url')->getUrl(
                        'amcustomerattrfront/attachment/download',
                        array('customer' => $customerId,
                              'file'     => Mage::helper('core')->urlEncode(
                                  $fileName
                              ))
                    );
                } else {
                    return Mage::helper('adminhtml')->getUrl(
                        'adminhtml/customer/viewfile',
                        array('file' => Mage::helper('core')->urlEncode(
                            $fileName
                        ))
                    );
                }
            }
            return '';
        } else { // Path for upload/download
            return $fileDir . DIRECTORY_SEPARATOR;
        }
    }

    public function checkAndCreateDir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    public function cleanFileName($fileName)
    {
        return explode(DS, $fileName);
    }

    public function getCorrectFileName($fileName)
    {
        $fileName = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $fileName);

        if (preg_match('/^_+$/', $fileName)) {
            $fileName = uniqid(date('ihs'));
        }
        return $fileName;
    }

    public function getFolderName($f)
    {
        $alp = array('p', 'o', 'i', 'u', 'y', 't', 'r', 'e', 'w', 'q', 'm', 'n',
                     'b', 'v', 'c', 'x', 'z', 'a', 's', 'd', 'f', 'g', 'h', 'j',
                     'k', 'l',
                     'P', 'O', 'I', 'U', 'Y', 'T', 'R', 'E', 'W', 'Q', 'M', 'N',
                     'B', 'V', 'C', 'X', 'Z', 'A', 'S', 'D', 'F', 'G', 'H', 'J',
                     'K', 'L');
        if (in_array($f, $alp)) {
            return $f;
        }
        return $alp[mt_rand(0, 51)];
    }

    public function getFileAttributes($column = '')
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();

        $collection = $this->addFilters(
            $collection, 'eav_attribute',
            array(
                "is_user_defined = 1"
            )
        );

        $filters = array(
            "type_internal = 'file' "
        );

        if ($column) {
            $filters[] = $column . " = 1";
        }
        $collection = $this->addFilters(
            $collection, 'customer_eav_attribute', $filters
        );

        return $collection;
    }

    public function getAttributeTypes($asHash = false)
    {
        if ($asHash) {
            return array('text'           => $this->__('Text Field'),
                         'textarea'       => $this->__('Text Area'),
                         'date'           => $this->__('Date'),
                         'multiselect'    => $this->__('Multiple Select'),
                         'multiselectimg' => $this->__(
                             'Multiple Checkbox Select with Images'
                         ),
                         'select'         => $this->__('Dropdown'),
                         'boolean'        => $this->__('Yes/No'),
                         'selectimg'      => $this->__(
                             'Single Radio Select with Images'
                         ),
                         'selectgroup'    => $this->__(
                             'Customer Group Selector'
                         ),
                         'statictext'     => $this->__('Static Text'),
                         'file'           => $this->__('Single File Upload'),
            );
        }
        return array(
            array(
                'value' => 'text',
                'label' => $this->__('Text Field')
            ),
            array(
                'value' => 'textarea',
                'label' => $this->__('Text Area')
            ),
            array(
                'value' => 'date',
                'label' => $this->__('Date')
            ),
            array(
                'value' => 'multiselect',
                'label' => $this->__('Multiple Select')
            ),
            array(
                'value' => 'multiselectimg',
                'label' => $this->__('Multiple Checkbox Select with Images')
            ),
            array(
                'value' => 'select',
                'label' => $this->__('Dropdown')
            ),
            array(
                'value' => 'boolean',
                'label' => $this->__('Yes/No')
            ),
            array(
                'value' => 'selectimg',
                'label' => $this->__('Single Radio Select with Images')
            ),
            array(
                'value' => 'selectgroup',
                'label' => $this->__('Customer Group Selector')
            ),
            array(
                'value' => 'statictext',
                'label' => $this->__('Static Text')
            ),
            array(
                'value' => 'file',
                'label' => $this->__('Single File Upload')
            ),
        );
    }

    public function getCustomerAccountData($customerId, $storeId)
    {
        if (null == $customerId) {
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            $storage = Mage::getModel('amcustomerattr/guest')->load(
                $orderId, 'order_id'
            );
        } else {
            $storage = Mage::getModel('customer/customer')->load($customerId);
        }

        $attributes = Mage::getModel('customer/attribute')->getCollection();

        $filters = array(
            "is_user_defined = 1",
            "entity_type_id = " . Mage::getModel('eav/entity')
                ->setType('customer')->getTypeId()
        );

        $attributes = $this->addFilters($attributes, 'eav_attribute', $filters);

        $filters = array(
            "on_order_view = 1"
        );
        $sorting = 'sorting_order';
        $attributes = $this->addFilters(
            $attributes, 'customer_eav_attribute', $filters, $sorting
        );


        $accountData = array();
        foreach ($attributes as $attribute) {
            $label = $this->__($attribute->getFrontend()->getLabel());
            $value = '';
            $currentData = '';
            if ($inputType = $attribute->getFrontend()->getInputType()) {
                $currentData = $storage->getData(
                    $attribute->getAttributeCode()
                );
            }

            if ($inputType == 'select' || $inputType == 'selectimg'
                || $inputType == 'multiselect'
                || $inputType == 'multiselectimg'
            ) {
                // getting values translations
                $valuesCollection = Mage::getResourceModel(
                    'eav/entity_attribute_option_collection'
                )
                    ->setAttributeFilter($attribute->getId())
                    ->setStoreFilter($storeId, false)
                    ->load();
                foreach ($valuesCollection as $item) {
                    $values[$item->getId()] = $item->getValue();
                }

                // applying translations
                $options = $attribute->getSource()->getAllOptions(false, true);
                foreach ($options as $i => $option) {
                    if (isset($values[$option['value']])) {
                        $options[$i]['label'] = $values[$option['value']];
                    }
                }
                // applying translations

                if (false !== strpos($inputType, 'multi')) {
                    $currentData = explode(',', $currentData);
                    foreach ($options as $option) {
                        if (in_array($option['value'], $currentData)) {
                            $value .= $option['label'] . ', ';
                        }
                    }
                    if ($value) {
                        $value = substr($value, 0, -2);
                    }
                } else {
                    foreach ($options as $option) {
                        if ($option['value'] == $currentData) {
                            $value = $option['label'];
                        }
                    }
                }

            } elseif ($inputType == 'date') {
                $format = Mage::app()->getLocale()->getDateFormat(
                    Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                );
                $value = Mage::getSingleton('core/locale')->date(
                    $currentData, Zend_Date::ISO_8601, null, false
                )->toString($format);
            } elseif ($inputType == 'boolean') {
                $value = $currentData ? 'Yes' : 'No';
            } elseif ('file' == $attribute->getTypeInternal()) {
                if ($currentData) {
                    $downloadUrl = $this->getAttributeFileUrl(
                        $currentData, true
                    );
                    $fileName = $this->cleanFileName($currentData);
                    $value = '<a href="' . $downloadUrl . '">' . $fileName[3]
                        . '</a>';
                } else {
                    $value = 'No Uploaded File';
                }
            } else {
                $value = $currentData;
            }

            if ($value) {
                $accountData[] = array('label' => $label, 'value' => $value);
            }
        }

        return $accountData;
    }

    /**
     * Get Elements relation.
     * Returns:
     * option_id | parent_code | dependent_code
     *
     */
    public function getElementsRelation()
    {
        if (!Mage::registry('amcustomerattr_attributes_relation')) {
            $relation = Mage::getModel('amcustomerattr/relation')
                ->getElementsRelation();
            Mage::register('amcustomerattr_attributes_relation', $relation);
        }
        return Mage::registry('amcustomerattr_attributes_relation');
    }

    public function processingValue(&$obj, $attributeCode)
    {
        $data = $obj->getData('_custom_attributes');
        if (!$data) {
            $customAttributes = array();
            $attributeCollection = Mage::getModel('customer/attribute')
                ->getCollection();
            $filters = array(
                "is_user_defined = 1",
                "entity_type_id  = " . Mage::getModel('eav/entity')
                    ->setType('customer')
                    ->getTypeId()
            );
            $attributeCollection = $this->addFilters(
                $attributeCollection, 'eav_attribute', $filters
            );

            foreach ($attributeCollection as $attribute) {
                if ($inputType = $attribute->getFrontend()->getInputType()) {
                    switch ($inputType) {
                        case 'date':
                            if ('0000-00-00' == $obj->getData(
                                    $attribute->getAttributeCode()
                                )
                            ) {
                                $customAttributes[$attribute->getAttributeCode(
                                )]
                                    = '';
                            } else {
                                // need to make something with date
                                $customAttributes[$attribute->getAttributeCode(
                                )]
                                    = $obj->getData(
                                    $attribute->getAttributeCode()
                                );
                            }
                            break;
                        case 'text':
                        case 'textarea':
                            $customAttributes[$attribute->getAttributeCode()]
                                = nl2br(
                                $obj->getData($attribute->getAttributeCode())
                            );
                            break;
                        case 'select':
                        case 'boolean':
                            $value = $obj->getData(
                                $attribute->getAttributeCode()
                            );
                            if (isset($value)) {
                                if ('boolean' == $inputType) {

                                    $customAttributes[$attribute->getAttributeCode(
                                    )]
                                        = $obj->getData(
                                        $attribute->getAttributeCode()
                                    ) ? $this->__('Yes') : $this->__('No');
                                } else {
                                    $customAttributes[$attribute->getAttributeCode(
                                    )]
                                        = $this->getLabelForOption(
                                        $attribute, $value, $obj
                                    );
                                }
                            } else {
                                $customAttributes[$attribute->getAttributeCode(
                                )]
                                    = '';
                            }
                            break;
                        case 'multiselect':
                            $columnData = '';
                            $values = explode(
                                ',',
                                $obj->getData($attribute->getAttributeCode())
                            );
                            foreach ($values as $value) {
                                $columnData .= $this->getLabelForOption(
                                        $attribute, $value, $obj
                                    ) . ', ';
                            }
                            if ($columnData) {
                                $columnData = substr($columnData, 0, -2);
                            }
                            $customAttributes[$attribute->getAttributeCode()]
                                = $columnData;
                            break;
                        case 'file':
                            $value = $obj->getData(
                                $attribute->getAttributeCode()
                            );
                            $customAttributes[$attribute->getAttributeCode()]
                                = $value;
                            break;
                    }
                }
            }
            $obj->_customAttributes = $customAttributes;
            $data = $obj->getData('_custom_attributes');
        }
        return (isset($data[$attributeCode]) ? $data[$attributeCode] : '');
    }

    public function getLabelForOption($attribute, $value, $obj)
    {
        $optionId = $attribute->getSource()->getOptionId($value);
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName(
            'eav/attribute_option_value'
        );
        $select = $db->select()->from($table)->where(
            'option_id = ?', $optionId
        );
        $labels = $db->fetchAll($select);
        $labelForOption = '';

        foreach ($labels as $label) {
            if (($obj->getStoreId() == $label['store_id'])
                && ('' !== $label['value'])
            ) {
                $labelForOption = $label['value'];
            } elseif ((0 == $label['store_id']) && ('' === $labelForOption)) {
                $labelForOption = $label['value'];
            }
        }
        return $labelForOption;
    }

    public function getCollectionAttributes($filter=null)
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $filters = array(
            "is_user_defined = 1",
            "entity_type_id  = " . Mage::getModel('eav/entity')
                ->setType('customer')
                ->getTypeId()
        );
        $collection = $this->addFilters(
            $collection, 'eav_attribute', $filters
        );
        if($filter){
            $filters = array(
                $filter . " = 1"
            );
        }

        $sorting = 'sorting_order';
        $collection = $this->addFilters(
            $collection, 'customer_eav_attribute', $filters, $sorting
        );

        $attributes = $collection->load();
        return $attributes;
    }

    public function getActionName()
    {
        $realAction = Mage::app()->getRequest()->getBeforeForwardInfo();
        if (isset($realAction['action_name'])) {
            $actionName = $realAction['action_name'];
        } else {
            $actionName = Mage::app()->getRequest()->getActionName();
        }
        return $actionName;
    }
}