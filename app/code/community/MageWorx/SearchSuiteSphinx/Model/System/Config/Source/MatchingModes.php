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

class MageWorx_SearchSuiteSphinx_Model_System_Config_Source_MatchingModes {

    public function toOptionArray() {
        return array(
            array('value' => SPH_MATCH_ALL, 'label' => 'SPH_MATCH_ALL'),
            array('value' => SPH_MATCH_ANY, 'label' => 'SPH_MATCH_ANY'),
            array('value' => SPH_MATCH_PHRASE, 'label' => 'SPH_MATCH_PHRASE'),
            array('value' => SPH_MATCH_EXTENDED, 'label' => 'SPH_MATCH_EXTENDED'),
            array('value' => SPH_MATCH_FULLSCAN, 'label' => 'SPH_MATCH_FULLSCAN'),
        );
    }

}
