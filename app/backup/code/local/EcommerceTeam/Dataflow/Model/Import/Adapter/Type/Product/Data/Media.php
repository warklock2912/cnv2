<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.0.4
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Media
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  string */
    protected $_galleryTable;
    /** @var  string */
    protected $_galleryValueTable;
    /** @var  Mage_Catalog_Model_Resource_Product_Attribute_Collection */
    protected $_mediaAttributeCollection;
    /** @var  Mage_Eav_Model_Attribute */
    protected $_galleryAttribute;

    protected function _construct()
    {
        /** @var Mage_Eav_Model_Config $eavConfig */
        $eavConfig = Mage::getSingleton('eav/config');
        $this->_galleryTable      = $this->_resource->getTableName('catalog/product_attribute_media_gallery');
        $this->_galleryValueTable = $this->_resource->getTableName('catalog/product_attribute_media_gallery_value');

        /** @var $attributeCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributeCollection->setItemObjectClass('catalog/resource_eav_attribute');
        $attributeCollection->addFieldToFilter('frontend_input', 'media_image');
        $attributeCollection->addFieldToSelect('*');
        $attributeCollection->setOrder('attribute_code', 'ASC');

        $this->_mediaAttributeCollection = $attributeCollection;
        $this->_galleryAttribute         = $eavConfig->getAttribute(
            $this->_config->getEntityTypeId(),
            'media_gallery'
        );
    }

    /**
     * @param array $data
     * @return $this|void
     */
    public function prepareData(array &$data)
    {
        if ($this->_config->getCanDownloadMedia()) {

        }
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        $galleryImages = array();
        $galleryLabels = array();

        foreach ($this->_mediaAttributeCollection as $attribute) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            $attributeCode = $attribute->getAttributeCode();
            if (isset($data[$attributeCode])) {
                $galleryImages[] = $data[$attributeCode];
            }
        }

        if (!$data['_is_new'] && isset($data['clear_old_gallery']) && $data['clear_old_gallery']) {
            $this->_deleteMediaAttributes($data);
            $this->_deleteMediaGallery($data);
        }

        $imagePositions = array();
        if (isset($data['images'])) {
            $images  = explode(',', $data['images']);
            $galleryImages = array_merge($galleryImages, $images);
            if (isset($data['image_positions'])) {
                $positions  = explode(',', $data['image_positions']);
                foreach ($images as $key => $image) {
                    $imagePositions[$this->_trimValue($image)] = $positions[$key];
                }
            }
        }
        if (isset($data['image_labels'])) {
            $galleryLabels  = explode(',', $data['image_labels']);
        }

        array_walk($galleryImages, array($this, '_trimValue'));
        foreach ($galleryImages as $key => $value) {
            if (empty($value)) {
                unset($galleryImages[$key]);
            }
        }
        $galleryImages = array_unique($galleryImages);

        $excludeImages = isset($data['exclude_images']) ? explode(',', $data['exclude_images']) : array();
        array_walk($excludeImages, array($this, '_trimValue'));

        $savedFileNames = array();

        if(!empty($galleryImages)){
            foreach ($galleryImages as $key => $imageFile) {
                $filePath = $imageFile;
                $isHttp = true;
                if (!$this->_isHttpImage($imageFile)) {
                    $filePath  = Mage::getBaseDir('media') . DS . 'import' . DS . $imageFile;
                    $fileExists = file_exists($filePath);
                    $isHttp = false;
                } else {
                    $fileExists = $this->_httpFileExists($imageFile);
                }

                if ($imageFile && $fileExists) {
                    $savedFileName = $this->saveImage($filePath, $isHttp);
                    $savedFileNames[$imageFile] = $savedFileName;

                    $values = array(
                        'attribute_id'  => $this->_galleryAttribute->getAttributeId(),
                        'entity_id'     => $data['product_id'],
                        'value'         => $savedFileName,
                    );

                    $this->_writeConnection->insert($this->_galleryTable, $values);
                    $this->_writeConnection->insert($this->_galleryValueTable, array(
                        'value_id' => $this->_writeConnection->lastInsertId(),
                        'store_id' => 0, //Multi store not supported for gallery
                        'label'    => isset($galleryLabels[$key]) ? $galleryLabels[$key] : '',
                        'position' => isset($imagePositions[$imageFile]) ? $imagePositions[$imageFile]: 0,
                        'disabled' => in_array($imageFile, $excludeImages),
                    ));
                }
            }
            $this->_updateMediaAttributes($data, $savedFileNames);
        }

        return $this;
    }

    /**
     * @param $imageFile
     * @return bool
     */
    protected function _isHttpImage($imageFile)
    {
        return (strpos($imageFile, 'http:') !== false) || (strpos($imageFile, 'https:') !== false);
    }

    /**
     * @param $url
     * @return bool
     */
    protected function _httpFileExists($url) {
        $headers = @get_headers($url);
        return !(strpos($headers[0], '200') === false);
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function _deleteMediaAttributes(array &$data)
    {
        foreach ($this->_mediaAttributeCollection as $attribute) {
            $where = array(
                $this->_writeConnection->quoteInto('entity_id = ?', $data['product_id']),
                $this->_writeConnection->quoteInto('attribute_id = ?', $attribute->getId()),
                $this->_writeConnection->quoteInto('entity_type_id = ?', $this->_config->getEntityTypeId()),
            );
            if ($this->_store->getId()) {
                $where[] = $this->_writeConnection->quoteInto('store_id = ?', $this->_store->getId());
            }

            $this->_writeConnection->delete($attribute->getBackendTable(), implode(' AND ', $where));

        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function _deleteMediaGallery(array &$data)
    {
        $select = $this->_writeConnection->select()->from($this->_galleryTable);
        $select->where('attribute_id = ?', $this->_galleryAttribute->getAttributeId());
        $select->where('entity_id = ?', $data['product_id']);

        /** @var Mage_Catalog_Model_Product_Media_Config $config */
        $config = Mage::getSingleton('catalog/product_media_config');

        foreach ($this->_writeConnection->fetchAll($select) as $imageData) {
            $filePath = $config->getBaseMediaPath() . DS . $imageData['value'];
            if (is_file($filePath) && is_writable($filePath)) {
                unlink($filePath);
            }
        }

        $this->_writeConnection->delete($this->_galleryTable, implode(' AND ', array(
            $this->_writeConnection->quoteInto('attribute_id = ?', $this->_galleryAttribute->getAttributeId()),
            $this->_writeConnection->quoteInto('entity_id = ?', $data['product_id']),
        )));

        return $this;
    }


    /**
     * @param array $data
     * @param array $savedFileNames
     * @return $this
     */
    protected function _updateMediaAttributes(array &$data, array &$savedFileNames)
    {
        foreach ($this->_mediaAttributeCollection as $attribute) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            $attributeCode = $attribute->getAttributeCode();

            if (isset($data[$attributeCode])) {
                $baseFileName = $this->_trimValue($data[$attributeCode]);
                if (isset($savedFileNames[$baseFileName])) {
                    $savedFileName = $savedFileNames[$baseFileName];
                    $table         = $attribute->getBackendTable();
                    $values = array(
                        'entity_id'      => $data['product_id'],
                        'attribute_id'   => $attribute->getId(),
                        'entity_type_id' => $this->_config->getEntityTypeId(),
                        'store_id'       => $this->_store->getId(),
                        'value'          => $savedFileName,
                    );
                    $this->_writeConnection->insertOnDuplicate($table, $values);
                }
            }
        }

        return $this;
    }

    /**
     * @param $file
     * @param $isHttp
     * @return mixed
     */
    public function saveImage($file, $isHttp)
    {
        /** @var Mage_Catalog_Model_Product_Media_Config $config */
        $config = Mage::getSingleton('catalog/product_media_config');
        if (!$isHttp) {
            $file   = realpath($file);
        }

        $fileName  = Varien_File_Uploader::getCorrectFileName(basename($file));
        $dispretionPath = Varien_File_Uploader::getDispretionPath($fileName);
        $localFilePath  = $dispretionPath . DS . $fileName;
        $localFilePath  = $dispretionPath . DS . Varien_File_Uploader::getNewFileName($config->getMediaPath($localFilePath));
        $globalFilePath = $config->getMediaPath($localFilePath);

        $ioAdapter      = $this->_helper->getIoFileResource();
        $ioAdapter->open(array('path' => dirname($globalFilePath)));
        $ioAdapter->cp($file, $globalFilePath);
        $ioAdapter->chmod($config->getMediaPath($localFilePath), 0755);
        $ioAdapter->close();
        $localFilePath = str_replace(DS, '/', $localFilePath);

        return $localFilePath;
    }

    /**
     * @param string $value
     * @return $this
     */
    protected function _trimValue(&$value)
    {
        $value = trim(trim($value), '/');

        return $value;
    }
}