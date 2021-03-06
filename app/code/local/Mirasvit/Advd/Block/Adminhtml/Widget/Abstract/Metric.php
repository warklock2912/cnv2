<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Reports
 * @version   1.0.27
 * @build     822
 * @copyright Copyright (C) 2017 Mirasvit (http://mirasvit.com/)
 */



abstract class Mirasvit_Advd_Block_Adminhtml_Widget_Abstract_Metric extends
Mirasvit_Advd_Block_Adminhtml_Widget_Abstract
{
    public function _prepareLayout()
    {
        $this->setTemplate('mst_advd/widget/metric.phtml');

        return parent::_prepareLayout();
    }
}
