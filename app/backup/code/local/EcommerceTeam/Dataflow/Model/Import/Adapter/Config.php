<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Config
{
    /** @var  int */
    protected $_entityTypeId;
    /** @var  array */
    protected $_attributeCodes;
    /** @var  string */
    protected $_beforeProcessCallback;
    /** @var  bool  */
    protected $_canCreateNewEntity  = true;
    /** @var  bool  */
    protected $_canCreateOptions    = true;
    /** @var  bool  */
    protected $_canCreateCategories = true;
    /** @var  bool  */
    protected $_canDownloadMedia    = true;
    /** @var  bool  */
    protected $_updateExisting = true;
    /** @var  bool  */
    protected $_skuPattern = '';
    /** @var  Varien_Data_Collection_Db */
    protected $_websiteCollection;
    /** @var  array  */
    protected $_websites;
    /** @var  array  */
    protected $_stores;
    /** @var  int */
    protected $_defaultWebsiteId;
    /** @var  int  */
    protected $_optionCorrectionFactor = 15;
    /** @var  Mage_Core_Model_Resource */
    protected $_resource;
    /** @var  Varien_Db_Adapter_Interface */
    protected $_resourceConnection;
    /** @var  string  */
    protected $_optionDelimiter = ',';
    /** @var  int */
    protected $_storeId;
    /** @var   Mage_Core_Model_Store */
    protected $_store;

    public function __construct(array $attributeCodes)
    {
        if (empty($attributeCodes)) {
            throw new EcommerceTeam_Dataflow_Exception(
                Mage::helper('ecommerceteam_dataflow')->__('Please specify attributes.'));
        }
        $this->_attributeCodes = $attributeCodes;

        /** @var $resource Mage_Core_Model_Resource */
        $resource                  = Mage::getSingleton('core/resource');
        $this->_resource           = $resource;
        $this->_resourceConnection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);

        /** @var $websiteCollection Mage_Core_Model_Resource_Website_Collection */
        $websiteCollection = Mage::getResourceModel('core/website_collection');
        $this->_websiteCollection = $websiteCollection;
        foreach ($websiteCollection as $website) {
            /** @var $website Mage_Core_Model_Website */
            $this->_websites[$website->getCode()] = $website->getId();
            if ($website->getIsDefault()) {
                $this->_defaultWebsiteId = $website->getId();
            }
        }
        /** @var $storeCollection Mage_Core_Model_Resource_Store_Collection */
        $storeCollection = Mage::getResourceModel('core/store_collection');
        foreach ($storeCollection as $store) {
            /** @var $store Mage_Core_Model_Store */
            $this->_stores[$store->getCode()] = $store->getId();
        }
    }

    /**
     * @return EcommerceTeam_Dataflow_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('ecommerceteam_dataflow');
    }

    /**
     * @return Mage_Core_Model_Resource
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    public function getResourceConnection()
    {
        return $this->_resourceConnection;
    }

    /**
     * @return array
     */
    public function getAttributeCodes()
    {
        return $this->_attributeCodes;
    }

    /**
     * @param int $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId)
    {
        $this->_entityTypeId = $entityTypeId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityTypeId()
    {
        return $this->_entityTypeId;
    }


    /**
     * Setting function which will be called before row processing
     *
     * For example: $config->setBeforeProcessCallback("Acme_Extension_Model_Entity::beforeProcessFunction");
     *
     * @param string $callBack
     * @return $this
     */
    public function setBeforeProcessCallback($callBack)
    {
        $this->_beforeProcessCallback = $callBack;

        return $this;
    }

    /**
     * @return string
     */
    public function getBeforeProcessCallback()
    {
        return $this->_beforeProcessCallback;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCanCreateNewEntity($flag)
    {
        $this->_canCreateNewEntity = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCanCreateNewEntity()
    {
        return $this->_canCreateNewEntity;
    }

     /**
     * @param bool $flag
     * @return $this
     */
    public function setUpdateExisting($flag)
    {
        $this->_updateExisting = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUpdateExisting()
    {
        return $this->_updateExisting;
    }

    /**
     * @param $pattern
     * @return $this
     */
    public function setSkuPattern($pattern)
    {
        $this->_skuPattern = $pattern;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSkuPattern()
    {
        return $this->_skuPattern;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCanCreateOptions($flag)
    {
        $this->_canCreateOptions = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCanCreateOptions()
    {
        return $this->_canCreateOptions;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCanCreateCategories($flag)
    {
        $this->_canCreateCategories = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCanCreateCategories()
    {
        return $this->_canCreateCategories;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCanDownloadMedia($flag)
    {
        $this->_canDownloadMedia = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCanDownloadMedia()
    {
        return $this->_canDownloadMedia;
    }

    /**
     * @return array
     */
    public function getWebsites()
    {
        return $this->_websites;
    }

    /**
     * @return array
     */
    public function getStores()
    {
        return $this->_stores;
    }

    /**
     * @return null|Mage_Core_Model_Website
     */
    public function getDefaultWebsite()
    {
        /** @var $website Mage_Core_Model_Website */
        foreach ($this->_websiteCollection as $website) {
            if ($website->getIsDefault()) {
                return $website;
            }
        }
    }

    /**
     * @return int|mixed
     */
    public function getDefaultWebsiteId()
    {
        return $this->_defaultWebsiteId;
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $store = Mage::getModel('core/store')->load($this->_storeId);
            if (is_null($store->getId())) {
                $store = $this->getDefaultWebsite()->getDefaultStore();
            }
            $this->_store = $store;
        }

        return $this->_store;
    }

    /**
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        return $this->getStore()->getWebsite();
    }

    /**
     * @param int $factor
     * @return $this
     */
    public function setOptionCorrectionFactor($factor)
    {
        $this->_optionCorrectionFactor = (int) $factor;

        return $this;
    }

    /**
     * @return int
     */
    public function getOptionCorrectionFactor()
    {
        return $this->_optionCorrectionFactor;
    }

    /**
     * @param string $delimiter
     * @return $this
     */
    public function setOptionDelimiter($delimiter)
    {
        if (strlen($delimiter)) {
            $this->_optionDelimiter = $delimiter;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionDelimiter()
    {
        return $this->_optionDelimiter;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }
}