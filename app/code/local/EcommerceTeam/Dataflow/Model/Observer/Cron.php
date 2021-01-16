<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Observer_Cron
    extends Mage_Cron_Model_Observer
{
    const CACHE_KEY_LAST_SCHEDULE_GENERATE_AT   = 'dataflow_cron_last_schedule_generate_at';
    const CACHE_KEY_LAST_HISTORY_CLEANUP_AT     = 'dataflow_cron_last_history_cleanup_at';

    /**
     * Process cron queue
     * Geterate tasks schedule
     * Cleanup tasks schedule
     *
     */
    public function dispatch($observer)
    {
        $schedules        = $this->getPendingSchedules();
        $scheduleLifetime = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_LIFETIME) * 60;
        $now              = time();

        foreach ($schedules->getIterator() as $schedule) {
            /** @var EcommerceTeam_Dataflow_Model_Profile_Schedule $schedule */
            $time = strtotime($schedule->getScheduledAt());
            if ($time > $now) {
                continue;
            }

            $errorStatus  = Mage_Cron_Model_Schedule::STATUS_ERROR;
            try {
                if ($time < $now - $scheduleLifetime) {
                    $errorStatus = Mage_Cron_Model_Schedule::STATUS_MISSED;
                    Mage::throwException(Mage::helper('cron')->__('Too late for the schedule.'));
                }

                if (!$schedule->tryLockJob()) {
                    // another cron started this job intermittently, so skip it
                    continue;
                }
                /**
                though running status is set in tryLockJob we must set it here because the object
                was loaded with a pending status and will set it back to pending if we don't set it here
                 */
                $schedule
                    ->setStatus(Mage_Cron_Model_Schedule::STATUS_RUNNING)
                    ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                    ->save();

                $this->_execute($schedule);

                $schedule
                    ->setStatus(Mage_Cron_Model_Schedule::STATUS_SUCCESS)
                    ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()));

            } catch (Exception $e) {
                $schedule->setStatus($errorStatus);
                $schedule->setMessages($e->__toString());
            }
            $schedule->save();
        }

        $this->generate();
        $this->cleanup();
    }

    /**
     * Generate cron schedule
     *
     * @return Mage_Cron_Model_Observer
     */
    public function generate()
    {
        /**
         * check if schedule generation is needed
         */
        $lastRun = Mage::app()->loadCache(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT);
//        if ($lastRun > time() - Mage::getStoreConfig(self::XML_PATH_SCHEDULE_GENERATE_EVERY)*60) {
//            return $this;
//        }

        $pendingSchedules = $this->getPendingSchedules();
        $exists           = array();
        foreach ($pendingSchedules as $schedule) {
            $exists[$schedule->getProfileId().'/'.$schedule->getScheduledAt()] = 1;
        }

        $scheduleAheadFor = Mage::getStoreConfig(self::XML_PATH_SCHEDULE_AHEAD_FOR)*60;
        /** @var EcommerceTeam_Dataflow_Model_Profile_Schedule $schedule */
        $schedule = Mage::getModel('ecommerceteam_dataflow/profile_schedule');

        foreach ($this->getAvailableProfiles() as $profile) {
            /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
            $cronExpr = $profile->getData('schedule');

            $now       = time();
            $timeAhead = $now + $scheduleAheadFor;

            $schedule->clearInstance();
            $schedule->setData(
                array(
                    'profile_id' => $profile->getId(),
                    'status'     => Mage_Cron_Model_Schedule::STATUS_PENDING,
                )
            );
            $schedule->setCronExpr($cronExpr);

            for ($time = $now; $time < $timeAhead; $time += 60) {
                $ts = strftime('%Y-%m-%d %H:%M:00', $time);
                if (!empty($exists[$profile->getId().'/'.$ts])) {
                    // already scheduled
                    continue;
                }
                if (!$schedule->trySchedule($time)) {
                    // time does not match cron expression
                    continue;
                }
                $schedule->save();
            }
        }

        /**
         * save time schedules generation was ran with no expiration
         */
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT, array('crontab'), null);

        return $this;
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Resource_Profile_Import_Collection
     */
    public function getAvailableProfiles()
    {
        /** @var EcommerceTeam_Dataflow_Model_Resource_Profile_Import_Collection $profileCollection */
        $profileCollection = Mage::getResourceModel('ecommerceteam_dataflow/profile_import_collection');
        $profileCollection->addFieldToFilter('schedule', array('neq' => ''));

        return $profileCollection;
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Resource_Profile_Schedule_Collection
     */
    public function getPendingSchedules()
    {
        if (!$this->_pendingSchedules) {
            $this->_pendingSchedules = Mage::getResourceModel('ecommerceteam_dataflow/profile_schedule_collection');
            $this->_pendingSchedules->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING);
        }
        return $this->_pendingSchedules;
    }

    public function cleanup()
    {
        // check if history cleanup is needed
        $lastCleanup = Mage::app()->loadCache(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT);
        if ($lastCleanup > time() - Mage::getStoreConfig(self::XML_PATH_HISTORY_CLEANUP_EVERY)*60) {
            return $this;
        }

        /** @var EcommerceTeam_Dataflow_Model_Resource_Profile_Schedule_Collection $collection */
        $collection = Mage::getResourceModel('ecommerceteam_dataflow/profile_schedule_collection')
            ->addFieldToFilter('status', array('in'=>array(
                Mage_Cron_Model_Schedule::STATUS_SUCCESS,
                Mage_Cron_Model_Schedule::STATUS_MISSED,
                Mage_Cron_Model_Schedule::STATUS_ERROR,
            )))->load();

        $historyLifetimes = array(
            Mage_Cron_Model_Schedule::STATUS_SUCCESS => Mage::getStoreConfig(self::XML_PATH_HISTORY_SUCCESS)*60,
            Mage_Cron_Model_Schedule::STATUS_MISSED => Mage::getStoreConfig(self::XML_PATH_HISTORY_FAILURE)*60,
            Mage_Cron_Model_Schedule::STATUS_ERROR => Mage::getStoreConfig(self::XML_PATH_HISTORY_FAILURE)*60,
        );

        $now = time();
        foreach ($collection as $record) {
            if (strtotime($record->getExecutedAt()) < $now-$historyLifetimes[$record->getStatus()]) {
                $record->delete();
            }
        }

        // save time history cleanup was ran with no expiration
        Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT, array('crontab'), null);

        return $this;
    }

    /**
     * @param EcommerceTeam_Dataflow_Model_Profile_Schedule $schedule
     */
    protected function _execute(EcommerceTeam_Dataflow_Model_Profile_Schedule $schedule)
    {
        /** @var EcommerceTeam_Dataflow_Model_Profile_Import $profile */
        $profile  = Mage::getModel('ecommerceteam_dataflow/profile_import')->load($schedule->getData('profile_id'));
        $config   = $profile->getScheduleConfig();
        $dataFile = Mage::getBaseDir() . DS . $config->getData('file');

        if (is_readable($dataFile)) {
            $profile->run($dataFile);
            if ($config->getData('delete_file_after_process')) {
                unlink($dataFile);
            }
        }
    }
}
