<?php

/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Search Suite Solr extension
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @author     MageWorx Dev Team
 */
class SolrClient {

    protected $_data = array(
        'handler' => 'select',
        'select' => '',
        'filter' => array(),
        'sort' => array(),
        'df' => 'data_index',
        'fl' => array('*', 'score'),
        'wt' => 'json',
        'range' => array(0, 1000),
        'command' => 'full-import',
        'commit' => 'true',
        'entity' => 'catalogsearch_fulltext',
        'qf' => array('data_index1' => 0, 'data_index2' => 0, 'data_index3' => 0, 'data_index4' => 0, 'data_index5' => 0),
        'defType' => 'dismax'
    );
    protected $_host = '';
    protected $_timeout = 0;

    public function setServer($host, $port, $path = 'solr') {
        $this->_host = $host . ':' . $port . '/' . $path;
    }

    public function addFilter($column, $value) {
        $this->_data['filter'][$column] = $value;
    }

    public function setRange($from = 0, $to = 1000) {
        $this->_data['range'] = array($from, $to - $from);
    }

    public function query($text) {
        $this->_data['handler'] = 'select';
        $this->_data['select'] = $text;
        return $this->_sendRequest();
    }

    public function reindex($delta = false) {
        $this->_data['handler'] = 'dataimport';
        if ($delta) {
            $this->_data['command'] = 'delta-import';
        }
        $params = array(
            'command' => $this->_data['command'],
            'commit' => 'commit',
            'wt' => $this->_data['wt'],
            'indent' => 'true',
            'entity' => $this->_data['entity'],
        );
        return $this->_sendRequest(true, $params);
    }

    public function setTimeout($timeout) {
        $this->_timeout = $timeout;
    }

    protected function _sendRequest($post = false, $params = null) {
        $rc = null;
        $query = '';
        if ($this->_data['handler'] == 'select') {
            $query = $this->_querySelect();
        } else if ($this->_data['handler'] == 'dataimport') {
            $query = $this->_queryIndex();
        } else if($this->_data['handler'] == 'check'){
            $query = $this->_queryCheck();
        }
        $curl = curl_init($query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        if ($this->_timeout > 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->_timeout);
        }
        $response = curl_exec($curl);
        if ($response) {
            $rc = $this->_parseResponse($response);
        }
        curl_close($curl);
        return $rc;
    }

    protected function _querySelect() {
        $query = $this->_host . '/' . $this->_data['handler'];
        $fq = array();
        foreach ($this->_data['filter'] as $key => $value) {
            $fq[] = $key . ':' . $value;
        }
        $qf = array();
        foreach ($this->_data['qf'] as $field => $value) {
            $value = floatval($value);
            if ($value <= 0) {
                $value = 0.01;
            }
            $qf[] = $field . '^' . $value;
        }
        $params = array(
            'df' => $this->_data['df'],
            'fl' => implode(',', $this->_data['fl']),
            'wt' => $this->_data['wt'],
            'q' => $this->_data['select'],
            'fq' => implode(' ', $fq),
            'indent' => 'true',
            'start' => $this->_data['range'][0],
            'rows' => $this->_data['range'][1],
            'defType' => $this->_data['defType'],
            'qf' => implode(' ', $qf),
        );

        $query.='?' . http_build_query($params);
        return $query;
    }

    protected function _queryIndex() {
        $query = $this->_host . '/' . $this->_data['handler'];
        return $query;
    }
    protected function _queryCheck() {
        $query = $this->_host.'/query';
        return $query;
    }

    protected function _parseResponse($response) {
        $rc = null;
        $type = $this->_data['wt'];
        if ($type == 'json') {
            $rc = json_decode($response, true);
        }
        return $rc;
    }

    public function setFieldWeights($weights) {
        if (is_array($weights)) {
            foreach ($weights as $field => $value) {
                if (isset($this->_data['qf'][$field])) {
                    $this->_data['qf'][$field] = $value;
                }
            }
        }
    }
    public function status(){
        $this->_data['handler'] = 'check';
        return (bool)$this->_sendRequest();
    }

}