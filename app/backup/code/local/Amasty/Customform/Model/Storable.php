<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

abstract class Amasty_Customform_Model_Storable extends Amasty_Customform_Model_Mappable
{
    /** @var Mage_Core_Model_Resource_Db_Collection_Abstract */
    protected $storeDataCollection;

    protected $storeEntityId;

    protected $storeJoinField;

    public function getCurrentStorable($field)
    {
        $storeId = Mage::app()->getStore()->getId();

        $storeData = $this->getStoreData($storeId);
        if (isset($storeData) && $storeData->getData($field)) {
            return $storeData->getData($field);
        } else {
            return $this->getStoreData(0)->getData($field);
        }
    }

    protected function _init($resourceModel)
    {
        parent::_init($resourceModel);

        $this->storeEntityId = $this->getResourceName() . '_store';

        $resourceNameParts = explode('/',$this->getResourceName());
        $this->storeJoinField = $resourceNameParts[1] . '_id';
    }

    public function setId($id)
    {
        parent::setId($id);

        $this->getStoreDataCollection()->addFilter($this->storeJoinField, $this->getId());
        /** @var Mage_Core_Model_Abstract $storeData */
        foreach ($this->getStoreDataCollection() as $storeData) {
            $storeData->setData($this->storeJoinField, $id);
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        if (!$this->isDeleted()) {
            if (isset($this->storeDataCollection)) {
                $this->storeDataCollection->save();
            }
        }
    }

    protected function createStoreData($storeId)
    {
        $storeData = Mage::getModel($this->storeEntityId);
        $storeData->setData($this->storeJoinField, $this->getId());
        $storeData->setData('store_id', $storeId);

        $this->getStoreDataCollection()->addItem($storeData);

        return $storeData;
    }

    protected function getStoreData($storeId)
    {
        $storeData = $this->getId()
            ? $this->getStoreDataCollection()->getItemByColumnValue('store_id', $storeId)
            : null;
        return $storeData;
    }

    protected function getStoreDataCollection()
    {
        if (is_null($this->storeDataCollection)) {
            $this->storeDataCollection = Mage::getModel($this->storeEntityId)->getCollection();

            if ($this->getId()) {
                $this->storeDataCollection->addFilter($this->storeJoinField, $this->getId());
                $this->storeDataCollection->load();
            }
        }

        return $this->storeDataCollection;
    }
}