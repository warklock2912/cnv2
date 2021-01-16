<?php

class EcommerceTeam_Dataflow_Model_Import_Parser_Csv
    extends EcommerceTeam_Dataflow_Model_Import_Parser_Abstract
{
    protected $_file;
    protected $_firstRowPosition;
    protected $_primaryColumn;
    protected $_primaryColumnIndex = array();
    protected $_columnKeyToId = array();
    protected $_columnIdToKey = array();

    // CSV format configuration
    protected $_delimiter;
    protected $_enclosure;
    protected $_escape;
    protected $_fallbacks;
    protected $_transform;

    /**
     * @return EcommerceTeam_Dataflow_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('ecommerceteam_dataflow');
    }

    /**
     * @param EcommerceTeam_Dataflow_Model_Import_Parser_Config_Csv $config
     * @throws EcommerceTeam_Dataflow_Model_Import_Parser_Exception
     */
    public function __construct(EcommerceTeam_Dataflow_Model_Import_Parser_Config_Csv $config)
    {
        ini_set("auto_detect_line_endings", true);

        $dataFilePath     = $config->getDataFilePath();
        $columnMapping    = $config->getColumnMapping();
        $primaryKey       = false;

        if (!is_file($dataFilePath) || !is_readable($dataFilePath)) {
            $this->_throwException($this->getHelper()->__('CSV file not found or not readable: %s', $dataFilePath));
        }

        $this->_file      = fopen($dataFilePath, 'r');
        $this->_delimiter = $config->getDelimiter();
        $this->_enclosure = $config->getEnclosure();
        $this->_escape    = $config->getEscape();
        $this->_fallbacks   = $config->getFallbacks();
        $this->_transform   = $config->getTransform();

        if (is_array($columnMapping)) {
            $this->_setColumnMapping($columnMapping);
        } else {
            $this->_prepareHeaders();
        }
        $this->_firstRowPosition = ftell($this->_file);
        if ($primaryKey) {
            $this->_prepareIndexMap($primaryKey);
        }
    }

    /**
     * Check is correct data structure
     *
     * @throws EcommerceTeam_Dataflow_Model_Import_Parser_Exception
     */
    public function validate()
    {
        if (empty($this->_columnIdToKey) || empty($this->_columnKeyToId)) {
            $this->_throwException(Mage::helper('ecommerceteam_dataflow')->__("Can't find column headers."));
        }
    }

    /**
     * Read next row from csv file
     *
     * @param bool $hash
     * @return array|bool
     */
    public function getData($hash = true)
    {
        $i = 0;
        if (false !== ($data = fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure))) {
            $i++;
            $data = $this->_addFallbacks($data, $i);
            $data = $this->_applyTransformations($data);
            if ($hash) {
                return $this->_getRowHash($data);
            } else {
                return $data;
            }
        }
        return false;
    }

    /**
     * Select row by primary column value
     *
     * @param string $primaryColumnValue
     * @return array|null
     */
    public function getRow($primaryColumnValue)
    {
        if (isset($this->_primaryColumnIndex[$primaryColumnValue])) {
            fseek($this->_file, $this->_primaryColumnIndex[$primaryColumnValue]);
            $result = $this->_getRowHash(
                fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure));
                //fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure, $this->_escape)); // Php 5.2.x not supported escape arg
            if (!empty($result)) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Return to first data row
     *
     * @return EcommerceTeam_Dataflow_Model_Import_Parser_Csv
     */
    public function rewind()
    {
        fseek($this->_file, $this->_firstRowPosition);
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeCodes()
    {
        return $this->_columnIdToKey;
    }

    /**
     * @param array $data
     * @param int $row
     * @return array
     */
    protected function _addFallbacks($data, $row)
    {
        if (!empty($this->_fallbacks)) {
            foreach ($this->_fallbacks as $fallback) {
                $code = ($fallback['code'] === '' ? false : $fallback['code']);
                if ($code) {
                    if (isset($this->_columnKeyToId[$code])) {
                        $id = $this->_columnKeyToId[$code];
                    } else {
                        $id = count($data); //add value to the end
                        $this->_addColumnMapping($code, $id);
                    }
                    $expression = $fallback['condition'];
                    $value = str_replace('$row', $row, $fallback['value']);
                    switch ($expression) {
                        case "all":
                            $data[$id] = $value;
                            break;
                        case "empty_only":
                            if (empty($data[$id])) {
                                $data[$id] = $value;
                            }
                            break;
                        case "not_empty":
                            if (!empty($data[$id])) {
                                $data[$id] = $value;
                            }
                            break;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $data
     */
    protected function _applyTransformations($data)
    {
        if (!empty($this->_transform)) {
            foreach ($this->_transform as $transform) {
                $id = ($transform['code'] === '' ? false : $transform['code']);
                $expression = $transform['type'];
                $value = $transform['value'];
                if ($id !== false && isset($data[$id])) {
                    switch ($expression) {
                        case "uppercase":
                            $data[$id] = mb_strtoupper($data[$id], 'UTF-8');
                            break;
                        case "lowercase":
                            $data[$id] = mb_strtolower($data[$id], 'UTF-8');
                            break;
                        case "ucfirst":
                            $data[$id] = $this->_mbUcfirst($data[$id], 'UTF-8');
                            break;
                        case "lowercase_capitilize":
                            $data[$id] = mb_convert_case($data[$id], MB_CASE_TITLE, "UTF-8");
                            break;
                        case "round":
                            $data[$id] = round($data[$id], $value ? $value : 0);
                            break;
                        case "ceil":
                            $data[$id] = ceil($data[$id]);
                            break;
                        case "floor":
                            $data[$id] = floor($data[$id]);
                            break;
                        case "replace":
                            $value = explode('|', $value);
                            if (count($value) == 2) {
                                $from = explode(',', $value[0]);
                                $to   = explode(',', $value[1]);
                                $data[$id] = str_replace($from, $to, $data[$id]);
                            }
                            break;
                        case "add":
                            $data[$id] = $data[$id] + $value;
                            break;
                        case "multiply":
                            $data[$id] = $data[$id] * $value;
                            break;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $str
     * @return string
     */
    protected function _mbUcfirst($str) {
        $fc = mb_strtoupper(mb_substr($str, 0, 1));

        return $fc . mb_substr($str, 1);
    }

    /**
     * Get cell value for column by column name
     *
     * @param string $columnName
     * @param array $row
     * @return string|null
     */
    protected function _getColumnValue($columnName, $row)
    {
        if (isset($this->_columnKeyToId[$columnName], $row[$this->_columnKeyToId[$columnName]])) {
            return $row[$this->_columnKeyToId[$columnName]];
        }
        return null;
    }

    /**
     * @param array $map
     * @return $this
     */
    protected function _setColumnMapping(array $map)
    {
        foreach ($map as $id => $key)
        {
            $this->_addColumnMapping($key, $id);
        }
        
        return $this;
    }

    protected function _addColumnMapping($key, $id)
    {
        $this->_columnKeyToId[$key] = $id;
        $this->_columnIdToKey[$id] = $key;
    }

    /**
     * Create headers map
     *
     * @return EcommerceTeam_Dataflow_Model_Import_Parser_Csv
     */
    protected function _prepareHeaders()
    {
        return $this->_setColumnMapping(
            fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure));
            //fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure, $this->_escape)); // Php 5.2.x not supported escape arg
    }

    /**
     * Create hash map for primary column
     *
     * @param $primaryKey
     * @return EcommerceTeam_Dataflow_Model_Import_Parser_Csv
     */
    protected function _prepareIndexMap($primaryKey)
    {
        $this->_primaryColumn = $primaryKey;
        $current = ftell($this->_file);
        //while (false !== ($row = fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure, $this->_escape))) { // Php 5.2.x not supported escape arg
        while (false !== ($row = fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure))) {
            $value = $this->_getColumnValue($this->_primaryColumn, $row);
            if ($value) {
                $this->_primaryColumnIndex[$value] = $current;
            }
            $current = ftell($this->_file);
        }
        $this->rewind();
        return $this;
    }

    /**
     * Convert array to hash, when use headers for keys
     *
     * @param array $rowData
     * @return array
     */
    protected function _getRowHash($rowData)
    {
        $result = array();
        if (is_array($rowData) && !empty($rowData)) {
            foreach ($rowData as $id => $value) {
                if (isset($this->_columnIdToKey[$id])) {
                    $result[$this->_columnIdToKey[$id]] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Close file when destroy object
     */
    public function __destruct()
    {
        fclose($this->_file);
    }
}
