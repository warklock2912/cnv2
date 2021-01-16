<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_QueueProcessor
{
    public function process()
    {
        $tasks = Mage::getResourceModel('amoptimization/task_collection')->load();

        foreach ($tasks as $task) {
            $success = $task->process();
            if ($success) {
                $task->delete();
            }
        }
    }
}
