<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */
class Amasty_Customform_Helper_Data extends Mage_Core_Helper_Abstract
{
    const INPUT_TYPE_TEXT        = 'text';
    const INPUT_TYPE_TEXTAREA    = 'textarea';
    const INPUT_TYPE_DATE        = 'date';
    const INPUT_TYPE_MULTISELECT = 'multiselect';
    const INPUT_TYPE_SELECT      = 'select';
    const INPUT_TYPE_BOOLEAN     = 'boolean';
    const INPUT_TYPE_STATIC_TEXT = 'statictext';
    const INPUT_TYPE_FILE        = 'file';
    const INPUT_TYPE_EMAIL       = 'email';
    const INPUT_TYPE_CHECKBOXES  = 'checkbox';
    const INPUT_TYPE_RADIO       = 'radio';




    public function getInputTypes()
    {
        return array(
            array(
                'value' => self::INPUT_TYPE_TEXT,
                'label' => $this->__('Text Field')
            ),
            array(
                'value' => self::INPUT_TYPE_TEXTAREA,
                'label' => $this->__('Text Area')
            ),
/*            array(
                'value' => self::INPUT_TYPE_EMAIL,
                'label' => $this->__('Email')
            ),*/
            array(
                'value' => self::INPUT_TYPE_DATE,
                'label' => $this->__('Date')
            ),
            array(
                'value' => self::INPUT_TYPE_SELECT,
                'label' => $this->__('Dropdown')
            ),
/*            array(
                'value' => self::INPUT_TYPE_RADIO,
                'label' => $this->__('Radio Button')
            ),
            array(
                'value' => self::INPUT_TYPE_CHECKBOXES,
                'label' => $this->__('Checkboxes')
            ),*/
            array(
                'value' => self::INPUT_TYPE_MULTISELECT,
                'label' => $this->__('Multiple Select')
            ),
            array(
                'value' => self::INPUT_TYPE_BOOLEAN,
                'label' => $this->__('Yes/No')
            ),
            array(
                'value' => self::INPUT_TYPE_STATIC_TEXT,
                'label' => $this->__('Static Text')
            ),
/*            array(
                'value' => self::INPUT_TYPE_FILE,
                'label' => $this->__('Single File Upload')
            )*/
        );
    }

    public function getInputTypesWithOptions() {
        return array(
            self::INPUT_TYPE_MULTISELECT,
            self::INPUT_TYPE_SELECT,
        );
    }

    public function getValidationRules()
    {
       /* return array(
            array(
                'value' => '',
                'label' => $this->__('None')
            ),
            array(
                'value' => 'validate-number',
                'label' => $this->__('Decimal Number')
            ),
            array(
                'value' => 'validate-digits',
                'label' => $this->__('Integer Number')
            ),
            array(
                'value' => 'validate-tendigits',
                'label' => $this->__('10 Digits Integer Number')
            ),
            array(
                'value' => 'validate-aaa-0000',
                'label' => $this->__('AAA-0000')
            ),
            array(
                'value' => 'validate-email',
                'label' => $this->__('Email')
            ),
            array(
                'value' => 'validate-url',
                'label' => $this->__('Url')
            ),
            array(
                'value' => 'validate-alpha',
                'label' => $this->__('Letters')
            ),
            array(
                'value' => 'validate-alphanum',
                'label' => $this->__('Letters(a-zA-Z) or Numbers(0-9)')
            ),
        );*/
        $existsValidations = array(
            'validate-no-html-tags'         => 'HTML tags are not allowed',
            'validate-select'               => 'Please select an option.',
            'required-entry'                => 'This is a required field.',
            'validate-number'               => 'Please enter a valid number in this field.',
            'validate-number-range'         => 'The value is not within the specified range.',
            'validate-digits'               => 'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.',
            'validate-digits-range'         => 'The value is not within the specified range.',
            'validate-alpha'                => 'Please use letters only (a-z or A-Z) in this field.',
            'validate-code'                 => 'Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.',
            'validate-alphanum'             => 'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.',
            'validate-alphanum-with-spaces' => 'Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field.',
            'validate-street'               => 'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.',
            'validate-phoneStrict'          => 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.',
            'validate-phoneLax'             => 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.',
            'validate-fax'                  => 'Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.',
            'validate-date'                 => 'Please enter a valid date.',
            'validate-date-range'           => 'The From Date value should be less than or equal to the To Date value.',
            'validate-email'                => 'Please enter a valid email address. For example johndoe@domain.com.',
            'validate-emailSender'          => 'Please use only visible characters and spaces.',
            'validate-password'             => 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.',
            'validate-admin-password'       => 'Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.',
            'validate-both-passwords'       => 'Please make sure your passwords match.',
            'validate-url'                  => 'Please enter a valid URL. Protocol is required (http://, https:// or ftp://)',
            'validate-clean-url'            => 'Please enter a valid URL. For example http://www.example.com or www.example.com',
            'validate-identifier'           => 'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".',
            'validate-xml-identifier'       => 'Please enter a valid XML-identifier. For example something_1, block5, id-4.',
            'validate-ssn'                  => 'Please enter a valid social security number. For example 123-45-6789.',
            'validate-zip'                  => 'Please enter a valid zip code. For example 90602 or 90602-1234.',
            'validate-zip-international'    => 'Please enter a valid zip code.',
            'validate-date-au'              => 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.',
            'validate-currency-dollar'      => 'Please enter a valid $ amount. For example $100.00.',
            'validate-one-required'         => 'Please select one of the above options.',
            'validate-one-required-by-name' => 'Please select one of the options.',
            'validate-not-negative-number'  => 'Please enter a number 0 or greater in this field.',
            'validate-zero-or-greater'      => 'Please enter a number 0 or greater in this field.',
            'validate-greater-than-zero'    => 'Please enter a number greater than 0 in this field.',
            'validate-state'                => 'Please select State/Province.',
            'validate-new-password'         => 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.',
            'validate-cc-number'            => 'Please enter a valid credit card number.',
            'validate-cc-type'              => 'Credit card number does not match credit card type.',
            'validate-cc-type-select'       => 'Card type does not match credit card number.',
            'validate-cc-exp'               => 'Incorrect credit card expiration date.',
            'validate-cc-cvn'               => 'Please enter a valid credit card verification number.',
            'validate-data'                 => 'Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.',
            'validate-css-length'           => 'Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.',
            'validate-length'               => 'Text length does not satisfy specified text range.',
            'validate-percents'             => 'Please enter a number lower than 100.',
            'validate-cc-ukss'              => 'Please enter issue number or start date for switch/solo card type.'
        );
        $result = array();
        foreach($existsValidations as $key=>$val){
            $result[] = array(
                'value' => $key,
                'label' => $this->__($val)
            );
        }
        return $result;
    }

    public function deleteDatedData()
    {
        $this->deleteDatedFormFields();
        $this->deleteDatedFields();
        $this->deleteDatedFormLines();
    }

    protected function deleteDatedFormFields()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $formFieldTable = $resource->getTableName('amcustomform/form_field');
        $alias = 'form_field';
        $sql = "DELETE FROM $formFieldTable AS `".$alias."` WHERE `".$alias."`.is_deleted = 1";

       /* foreach (Amasty_Customform_Model_Form_Submit_Value_Abstract::getTypes() as $type) {
            $resourceModel = 'amcustomform/form_submit_value_' . $type;
            $valueTable = $resource->getTableName($resourceModel);
            $as = $type;
            $sql .= " AND (SELECT COUNT(*) FROM $valueTable AS `".$type."` WHERE `".$type."`.form_field_id = `".$alias."`.id) = 0";
        }
        $a  =1;*/
        //$connection->query($sql);
    }

    protected function deleteDatedFormLines()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $lineTable = $resource->getTableName('amcustomform/form_line');
        $formFieldTable = $resource->getTableName('amcustomform/form_field');
        $lineAlias = 'form_line';
        $formAlias = 'form_field';
        $sql = "DELETE FROM $lineTable AS `{$lineAlias}` WHERE `{$lineAlias}`.is_deleted = 1
        AND (SELECT COUNT(*) FROM $formFieldTable as `{$formAlias}` WHERE `{$formAlias}`.line_id = `{$lineAlias}`.id) = 0";
        $a = 1;
        //$connection->query($sql);
    }

    protected function deleteDatedFields()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $fieldTable = $resource->getTableName('amcustomform/field');
        $formFieldTable = $resource->getTableName('amcustomform/form_field');
        $fieldAlias = 'field';
        $formAlias = 'form_field';
        $sql = "DELETE FROM $fieldTable AS `{$fieldAlias}` WHERE `{$fieldAlias}`.is_deleted = 1
        AND (SELECT COUNT(*) FROM $formFieldTable AS `{$formAlias}` WHERE `{$formAlias}`.field_id = `{$fieldAlias}`.id) = 0";
        $a = 1;
        //$connection->query($sql);
    }

    public function normalizeSortOrder($collection, $fieldName = 'sort_order')
    {
        /** @var Varien_Object[] $result */
        $result = array();
        foreach ($collection as $item) {
            $result[] = $item;
        }

        for ($i=0; $i<count($result); $i++) {
            for ($j=$i+1; $j<count($result); $j++) {
                if ($result[$i]->getData($fieldName) > $result[$j]->getData($fieldName)) {
                    $t = $result[$i];
                    $result[$i] = $result[$j];
                    $result[$j] = $t;
                }
            }
        }

        foreach ($result as $i => $item) {
            $item->setData($fieldName, ($i+1)*10);
        }
    }
}