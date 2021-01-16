<?php
//define('DS', DIRECTORY_SEPARATOR);
//define('PS', PATH_SEPARATOR);
//chdir(__DIR__);
//define('BP', __DIR__ );
//
//$vistordata = fopen(BP . DS . "visitordata.csv", "a");
//$vistordata_temp = fopen(BP . DS ."visitordata_temp.csv", "w+");
//
//$maxLines = 1;
//
//if ($vistordata) {
//    $i = 0;
//    while (($line = fgetcsv($vistordata)) !== false) {
//        if($i < $maxLines)
//        {
//        }else
//        {
//            fputcsv($vistordata_temp, ($line));
//        }
//    }
//} else {
//}
//fclose($vistordata_temp);fclose($vistordata);
//unlink(BP . DS.'visitordata.csv');
//rename(BP . DS .'visitordata_temp.csv','visitordata.csv');