<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Model_Mappable_Relation
{
    protected $name;
    protected $childEntityId;
    protected $joinColumn;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getJoinColumn()
    {
        return $this->joinColumn;
    }

    public function setJoinColumn($column)
    {
        $this->joinColumn = $column;

        return $this;
    }

    public function getChildEntityId()
    {
        return $this->childEntityId;
    }

    public function setChildEntityId($entityId)
    {
        $this->childEntityId = $entityId;

        return $this;
    }
}