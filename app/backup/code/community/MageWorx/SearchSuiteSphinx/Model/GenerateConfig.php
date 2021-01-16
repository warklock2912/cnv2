<?php
/**
 * MageWorx
 * Search Suite Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SearchSuiteSphinx_Model_GenerateConfig {

    /**
     * SearchSuiteSphinx Data helper
     *
     * @var MageWorx_SearchSuiteSphinx_Helper_Data
     */
    private $helper = null;

    /**
     * Default settings array used in sphinx.conf
     *
     * @var array
     */
    private $defaultSettings = array(
        '{$sql_port}' => 3306,
        '{$index_name}' => MageWorx_SearchSuiteSphinx_Model_Resource_CatalogSearch_Fulltext_Engine::DEFAULT_INDEX_NAME,
        '{$index_files_path}' => '/var/lib/sphinx/index/',
        '{$delta_index_files_path}' => '/var/lib/sphinx/deltaindex/',
        '{$sphinx_port}' => 9312,
        '{$pid_file_path}' => '/etc/sphinx/',
        '{$log_files_path}' => '/var/log/sphinx/',
        '{$binlog_files_path}' => '/var/lib/sphinx/'
    );

    /**
     * Configured settings array used in sphinx.conf
     *
     * @var array
     */
    private $configuredSettings = array();

    /**
     * Fill configuredSettings array with data
     */
    public function __construct()
    {
        $this->helper =  Mage::helper('mageworx_searchsuitesphinx');

        $userSettings = array_merge(
            $this->getDbConnectionConfig(),
            $this->getIndexConfig(),
            $this->getSearchdConfig()
        );
        $userSettings = $this->optimizeArray($userSettings);

        $this->configuredSettings = array_merge(
            $this->defaultSettings,
            $userSettings
        );
    }

    /**
     * Get configuredSettings array
     *
     * @return array
     */
    public function getConfigData()
    {
        return $this->configuredSettings;
    }

    /**
     * Get settings used in database connection source
     *
     * @return array
     */
    private function getDbConnectionConfig()
    {
        $config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");

        $dbInfo = array(
            '{$sql_host}' => $config->host->__toString(),
            '{$sql_user}' => $config->username->__toString(),
            '{$sql_password}' => $config->password->__toString(),
            '{$sql_database_name}' => $config->dbname->__toString(),
            '{$sql_port}' => $this->helper->getSqlPort()
        );

        return $dbInfo;
    }

    /**
     * Get settings used in index section
     *
     * @return array
     */
    private function getIndexConfig()
    {
        $indexInfo = array(
            '{$index_name}' => $this->helper->getSphinxIndexName(),
            '{$index_files_path}' => $this->helper->getSphinxIndexPath(),
            '{$delta_index_files_path}' => $this->helper->getSphinxDeltaindexPath()
        );

        return $indexInfo;
    }

    /**
     * Get settings used in searchd section
     *
     * @return array
     */
    private function getSearchdConfig()
    {
        $searchdInfo = array(
            '{$sphinx_port}' => $this->helper->getSphinxPort(),
            '{$pid_file_path}' => $this->helper->getSphinxPidFilePath(),
            '{$log_files_path}' => $this->helper->getSphinxLogFilesPath(),
            '{$binlog_files_path}' => $this->helper->getSphinxBinlogFilesPath()
        );

        return $searchdInfo;
    }

    /**
     * Return array without empty elements
     *
     * @param $array
     * @return array
     */
    private function optimizeArray($array)
    {
        $array = array_map('trim', $array);
        $array = array_filter($array);

        return $array;
    }
}
