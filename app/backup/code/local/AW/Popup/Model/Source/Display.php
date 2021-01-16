<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Popup
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Popup_Model_Source_Display extends AW_Popup_Model_Source_Abstract
{
    const PAGE_ID = 0;
    const URL_ID = 1;

    const PAGE_NAME = 'Specified pages';
    const URL_NAME = 'Specified URL';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::PAGE_ID, 'label' => $helper->__(self::PAGE_NAME)),
            array('value' => self::URL_ID, 'label' => $helper->__(self::URL_NAME)),
        );
    }
}