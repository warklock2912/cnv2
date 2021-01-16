<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Helper_Data
    extends Mage_Core_Helper_Data
{
    /** @var  Varien_Io_File */
    protected $_ioFileResource;

    public function compareWithLevenshtein($label1, $label2, $correctionPercent)
    {
        $label1 = preg_replace('/\W/', '', strtolower($label1));
        $label2 = preg_replace('/\W/', '', strtolower($label2));
        if (0 == $correctionPercent) {
            if ($label1 == $label2) {
                return true;
            }
        } else {
            $levenshtein = levenshtein($label1, $label2);
            $strLength   = strlen($label1);
            if ($levenshtein <= ($strLength/(100/$correctionPercent))) {
                return  true;
            }
        }

        return false;
    }

    /**
     * @param string $paramName
     * @param string $baseDir
     * @param string $localDir
     * @param null|string $allowedFormats
     * @return string
     */
    public function saveFile($paramName, $baseDir, $localDir, $allowedFormats = null)
    {
        $absPath = $baseDir . DS . $localDir . DS;
        if (!is_dir($absPath)) {
            mkdir($absPath, 0755, true);
        }
        $uploader = new Varien_File_Uploader($paramName);
        if (is_array($allowedFormats)) {
            $uploader->setAllowedExtensions($allowedFormats);
        }
        $uploader->setAllowRenameFiles(true);
        $result = $uploader->save($absPath);
        return $localDir . DS . $result['file'];
    }

    /**
     * @return Varien_Io_File
     */
    public function getIoFileResource()
    {
        if (is_null($this->_ioFileResource)) {
            $this->_ioFileResource = new Varien_Io_File();
            $this->_ioFileResource->setAllowCreateFolders(true);
        }
        return $this->_ioFileResource;
    }

    /**
     * @param Varien_Data_Collection $collection
     * @param string $keyField
     * @param string $valueField
     * @return array
     */
    public function collectionToOptionHash(Varien_Data_Collection $collection, $keyField = 'id', $valueField = 'name')
    {
        $result = array();
        foreach ($collection as $item) {
            /** @var $item Varien_Object */
            $result[$item->getData($keyField)] = $item->getData($valueField);
        }
        return $result;
    }

    /**
     * @param array $array
     * @param string $valueField
     * @param string $keyField
     * @param bool $withEmpty
     * @return array
     */
    public function arrayToOptionHash(array $array, $keyField, $valueField, $withEmpty = true)
    {
        $result = array();
        if ($withEmpty) {
            $result[''] = $this->__('Please Select');
        }
        foreach ($array as $item) {
            /** @var $item Varien_Object */
            $result[$item[$keyField]] = $item[$valueField];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableParsers()
    {
        $result       = array();
        $path         = "ecommerceteam/dataflow/parser";
        $parserNodes = Mage::getConfig()->getXpath($path);
        if ($parserNodes && count($parserNodes)) {
            $parsers = array_shift($parserNodes);
            foreach ($parsers->children() as $adapter) {
                /** @var $adapter Mage_Core_Model_Config_Element */
                $result[] = array(
                    'name'  => (string) $adapter->{'name'},
                    'model' => (string) $adapter->{'model'},
                );
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getDefaultParserModel()
    {
        $parsers = $this->getAvailableParsers();
        $defaultParser = array_shift($parsers);
        return $defaultParser['model'];
    }

    /**
     * @param string $type
     * @return array
     */
    public function getAvailableAdapters($type)
    {
        $result       = array();
        $path         = "ecommerceteam/dataflow/adapter/import/{$type}";
        $adapterNodes = Mage::getConfig()->getXpath($path);
        if ($adapterNodes && count($adapterNodes)) {
            $adapters = array_shift($adapterNodes);
            foreach ($adapters->children() as $adapter) {
                /** @var $adapter Mage_Core_Model_Config_Element */
                $result[] = array(
                    'name'  => (string) $adapter->{'name'},
                    'model' => (string) $adapter->{'model'},
                );
            }
        }
        return $result;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return mixed
     */
    public function arrayIntersectKeys($array1, $array2)
    {
        $keys = array_keys($array2);
        foreach ($array1 as $key => $value) {
            if (!in_array($key, $keys)) {
                unset($array1[$key]);
            }
        }
        return $array1;
    }

    /**
     * @param $title
     * @return mixed
     */
    public function processTitle($title)
    {
        return str_replace('"', "'", $title);
    }
}
