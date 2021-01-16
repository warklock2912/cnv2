<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

abstract class Amasty_Customform_Model_Mappable extends Mage_Core_Model_Abstract
{
    /** @var Mage_Catalog_Model_Resource_Collection_Abstract */
    protected $childEntityCollections = array();

    /** @var Amasty_Customform_Model_Mappable_Relation[] */
    protected $childRelations = array();

    protected $childKeyMap = array();

    protected function _init($resourceModel)
    {
        parent::_init($resourceModel);

        $this->setupRelations();
    }

    protected function setupRelations()
    {

    }

    protected function registerChildRelation(Amasty_Customform_Model_Mappable_Relation $relation)
    {
        $relationName = $relation->getName();

        if (isset($this->childRelations[$relationName])) {
            throw new Exception('Duplicate child relation ' . $relationName);
        }

        $this->childRelations[$relationName] = $relation;
        $this->childEntityCollections[$relationName] = null;
    }

    public function setId($id)
    {
        parent::setId($id);

        foreach ($this->childRelations as $name => $relation)
        {
            $childrenCollection = $this->childEntityCollections[$name];
            if (isset($childrenCollection)) {
                foreach ($childrenCollection as $child) {
                    /** @var Mage_Core_Model_Abstract $child */
                    $child->setData($relation->getJoinColumn(), $this->getId());
                }
            }
        }
    }

    public function realizeRelationData()
    {
        foreach ($this->childRelations as $name => $relation) {
            if ($this->hasData($name)) {
                $this->setChildrenData($relation, $this->getData($name));
            }
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        if (!$this->isDeleted()) {
            foreach ($this->childRelations as $name => $relation) {
                /** @var Mage_Catalog_Model_Resource_Collection_Abstract $childrenCollection */
                $childrenCollection = $this->childEntityCollections[$name];
                if (isset($childrenCollection)) {
                    $childrenCollection->save();
                }
            }
        }
    }

    protected function setChildrenData(Amasty_Customform_Model_Mappable_Relation $relation, array $data) {
        foreach ($data as $key => $row) {
            $child = ($key[0] == '_')
                ? $this->createChild($relation)
                : $this->getChildById($relation, $key);

            if (is_null($child)) {
                throw new Exception('Cannot set data to non-existing child entity');
            }

            $this->childKeyMap[$relation->getName()][$key] = $child;

            $child->addData($row);
            if ($child instanceof Amasty_Customform_Model_Mappable) {
                $child->realizeRelationData();
            }
        }
    }

    protected function getChildById(Amasty_Customform_Model_Mappable_Relation $relation, $id)
    {
        return $this->getChildrenCollection($relation)->getItemById($id);
    }

    protected function createChild(Amasty_Customform_Model_Mappable_Relation $relation)
    {
        $child = Mage::getModel($relation->getChildEntityId());
        $child->setData($relation->getJoinColumn(), $this->getId());

        $this->getChildrenCollection($relation)->addItem($child);

        return $child;
    }

    protected function getChildrenCollection(Amasty_Customform_Model_Mappable_Relation $relation)
    {
        /** @var Mage_Catalog_Model_Resource_Collection_Abstract $collection */
        $collection = $this->childEntityCollections[$relation->getName()];
        if (is_null($collection)) {
            $collection = Mage::getModel($relation->getChildEntityId())->getCollection();

            if ($this->getId()) {
                $collection->addFilter($relation->getJoinColumn(), $this->getId());
            } else {
                $collection->addFilter($relation->getJoinColumn(), 0);
            }
            $collection->load();

            $this->childEntityCollections[$relation->getName()] = $collection;
        }

        return $collection;
    }

    protected function getRelation($name)
    {
        if (!isset($this->childRelations[$name])) {
            throw new Exception('Requested unknown relation ' . $name . ' for ' . $this->getEntityId());
        }

        $relation = $this->childRelations[$name];
        return $relation;
    }
}