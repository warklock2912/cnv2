<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
require_once Mage::getBaseDir('lib') . DS . 'Sphinx' . DS . 'sphinxapi.php';

class MageWorx_SearchSuiteSphinx_Model_System_Config_Source_Ranker {

    public function toOptionArray() {
        return array(
            array('value' => SPH_RANK_PROXIMITY_BM25, 'label' => 'SPH_RANK_PROXIMITY_BM25'),
            array('value' => SPH_RANK_BM25, 'label' => 'SPH_RANK_BM25'),
            array('value' => SPH_RANK_NONE, 'label' => 'SPH_RANK_NONE'),
            array('value' => SPH_RANK_WORDCOUNT, 'label' => 'SPH_RANK_WORDCOUNT'),
            array('value' => SPH_RANK_PROXIMITY, 'label' => 'SPH_RANK_PROXIMITY'),
            array('value' => SPH_RANK_MATCHANY, 'label' => 'SPH_RANK_MATCHANY'),
            array('value' => SPH_RANK_FIELDMASK, 'label' => 'SPH_RANK_FIELDMASK'),
            array('value' => SPH_RANK_SPH04, 'label' => 'SPH_RANK_SPH04'),
        );
    }

}
