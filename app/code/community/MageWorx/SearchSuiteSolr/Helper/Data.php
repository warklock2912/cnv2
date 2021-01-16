<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSolr_Helper_Data extends MageWorx_SearchSuite_Helper_Data {

    protected $_instance = null;

    public function getSolrHost() {
        $host = Mage::getStoreConfig('mageworx_searchsuite/solr/host');
        if (empty($host)) {
            $host = '127.0.0.1';
        }
        $port = Mage::getStoreConfig('mageworx_searchsuite/solr/port');
        if (empty($port)) {
            $port = 9312;
        }
        $path = Mage::getStoreConfig('mageworx_searchsuite/solr/path');
        if (empty($port)) {
            $path = 'solr';
        }
        return array('host' => $host, 'port' => $port, 'path' => $path);
    }

    public function getSolrTimeout() {
        return (int) Mage::getStoreConfig('mageworx_searchsuite/solr/timeout');
    }

    public function getInstance() {
        if (!$this->_instance) {
            include_once Mage::getBaseDir('lib') . DS . 'Solr' . DS . 'solrapi.php';
            $this->_instance = new SolrClient();
            $host = $this->getSolrHost();
            $this->_instance->SetServer($host['host'], $host['port'], $host['path']);
            $this->_instance->setTimeout($this->getSolrTimeout());
        }
        return $this->_instance;
    }

}
