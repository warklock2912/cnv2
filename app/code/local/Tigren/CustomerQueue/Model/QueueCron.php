<?php
define('DS', DIRECTORY_SEPARATOR);
define('BP', __DIR__ );
class Tigren_CustomerQueue_Model_QueueCron
{
    public function queueCron()
    {

        try {
            $vistordata = fopen( BP . DS ."visitordata.csv", "a+");
            $vistordata_temp = fopen(BP . DS ."visitordata_temp.csv", "a+");
            $limit  = $configValue = Mage::getStoreConfig('customerqueue/general/limit');

            $maxLines = $limit ? $limit : 60;
            if ($vistordata) {
                $i = 0;
                while (($line = fgetcsv($vistordata)) !== false) {
                    if($i < $maxLines)
                    {
                    }else
                    {
                        fputcsv($vistordata_temp, ($line));
                    }
                    $i++;
                }
            } else {
            }
            fclose($vistordata_temp);fclose($vistordata);
            unlink(BP . DS .'visitordata.csv');
            rename(BP . DS .'visitordata_temp.csv',BP . DS .'visitordata.csv');
        }catch (\Exception $e)
        {
            var_dump($e->getMessage());
        }
    }
}