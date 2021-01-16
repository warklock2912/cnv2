<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_asyncindex
 * @version   1.1.13
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


/**
 * Переопредеяем дефолтный грид индексов, для добавления нового статуса STATUS_WAIT
 *
 * @category Mirasvit
 * @package  Mirasvit_AsyncIndex
 */
class Mirasvit_AsyncIndex_Block_Adminhtml_Process_Grid extends Mage_Index_Block_Adminhtml_Process_Grid
{
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case Mage_Index_Model_Process::STATUS_PENDING :
                $class = 'grid-severity-notice';
                break;
            case Mage_Index_Model_Process::STATUS_RUNNING :
                $class = 'grid-severity-major';
                break;
            case Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX :
                $class = 'grid-severity-critical';
                break;
            case Mirasvit_AsyncIndex_Model_Process::STATUS_WAIT :
            default:
                $class = 'grid-severity-major';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }
}