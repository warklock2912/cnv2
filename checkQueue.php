<?php
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(__FILE__));
$isInQueue = false;
$vistordata = fopen(BP . DS ."visitordata.csv", "r");
if ($vistordata) {
    while (($line = fgetcsv($vistordata)) !== false) {
        if(isset($line['0']) && isset($_COOKIE['queue_session_id']))
        {
            if($line['0'] == $_COOKIE['queue_session_id']) /// still in queue
            {
                $isInQueue = true;
            }
        }
    }
}else
{
    $isInQueue = true;
}
if(!isset($_COOKIE['queue_session_id']))
{
    $isInQueue = true;
}
echo json_encode(array('inQueue'=> $isInQueue));
