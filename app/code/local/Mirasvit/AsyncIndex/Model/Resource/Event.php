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


class Mirasvit_AsyncIndex_Model_Resource_Event extends Mage_Index_Model_Resource_Event
{
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $processIds = $object->getProcessIds();
        $ignored = Mage::getSingleton('asyncindex/config')->getIgnoredIndexes();
        foreach ($ignored as $key => $value) {
            if (isset($processIds[$value])) {
                unset($processIds[$value]);
            }
        }

        if (is_array($processIds)) {
            $processTable = $this->getTable('index/process_event');
            if (empty($processIds)) {
                $this->_getWriteAdapter()->delete($processTable);
            } else {
                foreach ($processIds as $processId => $processStatus) {
                    if (is_null($processStatus) || $processStatus == Mage_Index_Model_Process::EVENT_STATUS_DONE) {
                        $this->_getWriteAdapter()->delete($processTable, array(
                            'process_id = ?' => $processId,
                            'event_id = ?'   => $object->getId(),
                        ));
                        continue;
                    }
                    $data = array(
                        'process_id' => $processId,
                        'event_id'   => $object->getId(),
                        'status'     => $processStatus
                    );
                    $this->_getWriteAdapter()->insertOnDuplicate($processTable, $data, array('status'));
                }
            }
        }

        return $this;
    }
}