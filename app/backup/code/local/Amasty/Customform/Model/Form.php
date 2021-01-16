<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Form setCode($code)
 * @method string getCode()
 * @method Amasty_Customform_Model_Form setSuccessUrl($successUrl)
 * @method string getSuccessUrl()
 */
class Amasty_Customform_Model_Form extends Amasty_Customform_Model_Storable
{
    protected function _construct()
    {
        $this->_init('amcustomform/form');

        $this->setSuccessUrl('/');
    }

    public function getChildrenFields(){
        $relationLine = $this->childRelations['line'];

        $lines = $this->getChildrenCollection($relationLine);
        $fields = array();
        foreach ($lines as $line) {
            $childFields = $line->getActiveFormFields();
            $fields = array_merge($fields,$childFields);
        }

        return $fields;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->hasData('title')) {
            $this->setTitles($this->getData('title'));
        }

        if (isset($this->childEntityCollections['line'])) {
            /** @var Amasty_Customform_Helper_Data $helper */
            $helper = Mage::helper('amcustomform');
            $helper->normalizeSortOrder($this->childEntityCollections['line']);
        }
    }

    public function setTitles(array $titles)
    {
        foreach ($titles as $storeId => $title) {
            /** @var Amasty_Customform_Model_Form_Store $storeData */
            $storeData = $this->getStoreData($storeId);
            if (is_null($storeData)) {
                $storeData = $this->createStoreData($storeId);
            }

            $storeData->setTitle($title);
        }
    }

    public function getTitle($storeId = 0)
    {
        /** @var Amasty_Customform_Model_Form_Store $storeData */
        $storeData = $this->getStoreData($storeId);
        return is_object($storeData) ? $storeData->getTitle() : null;
    }

    protected function setupRelations()
    {
        $linesRelation = new Amasty_Customform_Model_Mappable_Relation('line');
        $linesRelation
            ->setChildEntityId('amcustomform/form_line')
            ->setJoinColumn('form_id')
            ;
        $this->registerChildRelation($linesRelation);
    }

    public function getActiveLines()
    {
        $result = array();
        $collection = $this->getChildrenCollection($this->getRelation('line'));
        foreach ($collection as $line) {
            /** @var Amasty_Customform_Model_Form_Line $line */
            if (!$line->getIsDeleted()) {
                $result[] = $line;
            }
        }

        return $result;
    }

    public function getCmsEmbeddingCode()
    {
        return '{{block type="amcustomform/form" name="amcustomform.form" form_id="' . $this->getId() . '"}}';
    }

    public function getTemplateEmbeddingCode()
    {
        return "<?php echo Mage::app()->getLayout()->createBlock('amcustomform/form', 'amcustomform.form', array('form_id' => {$this->getId()}))->toHtml() ?>";
    }
}