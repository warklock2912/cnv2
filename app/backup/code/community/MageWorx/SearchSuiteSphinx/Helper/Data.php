<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSphinx_Helper_Data extends MageWorx_SearchSuite_Helper_Data {

    protected $_instance = null;

    public function getSphinxPort() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/port');
    }

    public function getSphinxHost() {
        $host = Mage::getStoreConfig('mageworx_searchsuite/sphinx/host');
        if (empty($host)) {
            $host = '127.0.0.1';
        }
        $port = $this->getSphinxPort();
        if (empty($port)) {
            $port = 9312;
        }
        return array('host' => $host, 'port' => $port);
    }

    public function getSphinxTimeout() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/sphinx/timeout');
    }

    public function getSqlPort() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/sql_port');
    }

    public function getSphinxIndexName() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/index_name');
    }

    public function getSphinxIndexPath() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/index_path');
    }

    public function getSphinxDeltaindexPath() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/deltaindex_path');
    }

    public function getSphinxPidFilePath() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/pid_file_path');
    }

    public function getSphinxLogFilesPath() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/log_files_path');
    }

    public function getSphinxBinlogFilesPath() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/binlog_files_path');
    }

    public function getRankingMode() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/ranker');
    }

    public function getMatchMode() {
        return Mage::getStoreConfig('mageworx_searchsuite/sphinx/matchingmode');
    }

    public function getInstance() {
        if (!$this->_instance) {
            include_once Mage::getBaseDir('lib') . DS . 'Sphinx' . DS . 'sphinxapi.php';
            $this->_instance = new SphinxClient();
            $host = $this->getSphinxHost();
            $this->_instance->SetServer($host['host'], $host['port']);
            $this->_instance->SetConnectTimeout($this->getSphinxTimeout());
            $this->_instance->SetArrayResult(true);
        }
        return $this->_instance;
    }

}
