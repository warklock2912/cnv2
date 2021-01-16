<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

abstract class Amasty_Reports_Model_Reports_Abstract
{
    protected $_orderBy = '';

    protected function _phpDateToMysqlFrom($date)
    {
        $date = strtotime(str_replace('/', '-', $date));
        $date = date('Y-m-d H:i:s', $date);
        $theTime = strtotime($date);
        $tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone'));
        $transition = $tz->getTransitions($theTime, $theTime);
        $shift = -$transition[0]['offset'];
        $date = strtotime($date . ' ' . $shift . ' SECONDS');
        $date = date('Y-m-d H:i:s', $date);
        return $date;
    }

    protected function _phpDateToMysqlTo($date)
    {
        $date = strtotime(str_replace('/', '-', $date));
        $date = date('Y-m-d', $date);
        $date .= ' 23:59:59';
        $theTime = strtotime($date);
        $tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone'));
        $transition = $tz->getTransitions($theTime, $theTime);
        $shift = -$transition[0]['offset'];
        $date = strtotime($date . ' ' . $shift . ' SECONDS');
        $date = date('Y-m-d H:i:s', $date);
        return $date;
    }


    protected function _prepareFields($filters)
    {
        if (isset($filters['multiDate'])) {
            $filters['DateTo'] = $filters['DateTo'][$filters['multiDate']];
            $filters['DateFrom'] = $filters['DateFrom'][$filters['multiDate']];
        }
        isset($filters['OrderStatus']) ? $filters['OrderStatus'] = $filters['OrderStatus'][0] : '';
        isset($filters['StoreSelect']) ?
            $filters['StoreSelect'] = implode(',', $filters['StoreSelect'])
            : '';
        return $filters;
    }

    protected function _addEmptyDayRows($array, $from, $to, $dateField = 'period', $dateFormat = 'Y-m-d')
    {
        if (!$array) {
            return $array;
        }
        $result = array();
        //we need to add first and last date
        $from = date('Y-m-d', strtotime(str_replace('/', '-', $from)));
        if ($array[0][$dateField] != $from) {
            $inserted = array();
            foreach ($array[0] as $attrKey => $attr) {
                if ($attrKey === $dateField) {
                    $inserted[$attrKey] = $from;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            array_splice($array, 0, 0, array($inserted));
        }

        $to = date('Y-m-d', strtotime(str_replace('/', '-', $to)));
        if ($array[count($array) - 1][$dateField] != $to) {
            $inserted = array();
            foreach ($array[0] as $attrKey => $attr) {
                if ($attrKey === $dateField) {
                    $inserted[$attrKey] = $to;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            $array[] = $inserted;
        }

        $array = array_values($array);
        foreach ($array as $key => $elem) {
            $nextDate = date($dateFormat, strtotime($array[$key][$dateField] . ' +1 day'));
            if (isset($array[$key + 1]) && $array[$key + 1][$dateField] != $nextDate) {
                $result[] = $elem;
                $dateRange
                    = (strtotime($array[$key + 1][$dateField]) - strtotime($array[$key][$dateField])) / 86400 - 1;
                $currentKey = $key;
                for ($i = 1; $i <= $dateRange; $i++) {
                    $inserted = array();
                    foreach ($elem as $attrKey => $attr) {
                        if ($attrKey === $dateField) {
                            $inserted[$attrKey] = $nextDate;
                        } else {
                            $inserted[$attrKey] = 0;
                        }
                    }
                    $currentKey++;
                    $nextDate = date($dateFormat, strtotime($nextDate . ' +1 day'));
                    $result[] = $inserted;
                }
            } else {
                $result[] = $elem;
            }
        }
        return $result;
    }

    protected function _addEmptyMonthRows($array, $from, $to, $dateField = 'period', $dateFormat = 'Y-m')
    {
        if (!$array) {
            return $array;
        }
        $result = array();
        //we need to add first and last date
        $from = date('Y-m', strtotime(str_replace('/', '-', $from)));
        if ($array[0][$dateField] != $from) {
            $inserted = array();
            foreach ($array[0] as $attrKey => $attr) {
                if ($attrKey === $dateField) {
                    $inserted[$attrKey] = $from;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            array_splice($array, 0, 0, array($inserted));
        }

        $to = date('Y-m', strtotime(str_replace('/', '-', $to)));
        if ($array[count($array) - 1][$dateField] != $to) {
            $inserted = array();
            foreach ($array[0] as $attrKey => $attr) {
                if ($attrKey === $dateField) {
                    $inserted[$attrKey] = $to;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            $array[] = $inserted;
        }

        foreach ($array as $key => $elem) {
            if (isset($array[$key + 1])) {
                $nextDate = date($dateFormat, strtotime($elem[$dateField] . ' +1 month'));
                $currentMonth = date('m', strtotime($elem[$dateField]));
                $currentYear = date('Y', strtotime($elem[$dateField]));
                $nextMonth = date('m', strtotime($array[$key + 1][$dateField]));
                $nextYear = date('Y', strtotime($array[$key + 1][$dateField]));
                if (($currentMonth != $nextMonth || $currentYear != $nextYear)) {
                    $result[] = $elem;
                    $dateRange = $nextMonth - $currentMonth + (12 * ($nextYear - $currentYear)) - 1;
                    $currentKey = $key;
                    for ($i = 1; $i <= $dateRange; $i++) {
                        $inserted = array();
                        foreach ($elem as $attrKey => $attr) {
                            if ($attrKey === $dateField) {
                                $inserted[$attrKey] = $nextDate;
                            } else {
                                $inserted[$attrKey] = 0;
                            }
                        }
                        $currentKey++;
                        $nextDate = date($dateFormat, strtotime($nextDate . ' +1 MONTH'));
                        $result[] = $inserted;
                    }
                } else {
                    $result[] = $elem;
                }
            }
        }
        return $result;
    }

    protected function _addEmptyYearRows($array, $from, $to, $dateField = 'period', $dateFormat = 'Y')
    {
        if (!$array) {
            return $array;
        }
        $result = array();
        //we need to add first and last date
        $from = date('Y', strtotime(str_replace('/', '-', $from)));
        if ($array[0][$dateField] != $from) {
            $inserted = array();
            foreach ($array[0] as $attrKey => $attr) {
                if ($attrKey === $dateField) {
                    $inserted[$attrKey] = $from;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            array_splice($array, 0, 0, array($inserted));
        }

        $to = date('Y', strtotime(str_replace('/', '-', $to)));
        if ($array[count($array) - 1][$dateField] != $to) {
            $inserted = array();
            foreach ($array[0] as $attrKey => $attr) {
                if ($attrKey === $dateField) {
                    $inserted[$attrKey] = $to;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            $array[] = $inserted;
        }

        foreach ($array as $key => $elem) {
            if (isset($array[$key + 1])) {
                $nextDate = date($dateFormat, strtotime($elem[$dateField] . ' +1 YEAR'));
                $currentYear = date('Y', strtotime($elem[$dateField]));
                $nextYear = date('Y', strtotime($array[$key + 1][$dateField]));
                if ($currentYear != $nextYear) {
                    $dateRange = ($nextYear - $currentYear) - 1;
                    $currentKey = $key;
                    for ($i = 1; $i <= $dateRange; $i++) {
                        $inserted = array();
                        foreach ($elem as $attrKey => $attr) {
                            if ($attrKey === $dateField) {
                                $inserted[$attrKey] = $nextDate;
                            } else {
                                $inserted[$attrKey] = 0;
                            }
                        }
                        $currentKey++;
                        $nextDate = date($dateFormat, strtotime($nextDate . ' +1 YEAR'));
                        $result[] = $inserted;
                    }
                } else {
                    $result[] = $elem;
                }
            }
            $result[] = $elem;
        }
        return $result;
    }

    protected function _getPeriod($filters)
    {
        $specialFilter = array('Salesbyhour','Salesbyweek','Newreturn');
        if (in_array($filters['report_type'], $specialFilter)) {
            $period = $this->_getSpecialPeriod($filters);
        } else {
            $offset = $this->_getOffset($filters['DateFrom']);
            $oper = $this->_getOper($offset);
            $offset = abs($offset);
            switch ($filters['Period']) {
                case 'TO_DAYS':
                    $period[] = 'DATE_FORMAT('.$oper.'(main_table.'.$this->_orderBy.', INTERVAL '.$offset.' SECOND),\'%Y-%m-%d\') as period';
                    break;
                case 'MONTH':
                    $period[] = 'DATE_FORMAT('.$oper.'(main_table.'.$this->_orderBy.', INTERVAL '.$offset.' SECOND),\'%Y-%m\') as period';
                    break;
                case 'YEAR':
                    $period[] = 'DATE_FORMAT('.$oper.'(main_table.'.$this->_orderBy.', INTERVAL '.$offset.' SECOND),\'%Y\') as period';
                    break;
                default:
                    $period[] = 'DATE_FORMAT('.$oper.'(main_table.'.$this->_orderBy.', INTERVAL '.$offset.' SECOND),\'%Y-%m-%d\') as period';
            }
        }
        return $period;
    }

    protected function _getOper($offset)
    {
        -$offset<0? $oper = 'DATE_ADD':$oper = 'DATE_SUB';
        return $oper;
    }

    protected function _getOffset($time)
    {
        $theTime =  strtotime($this->_phpDateToMysqlFrom($time));
        $tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone'));
        $transition = $tz->getTransitions($theTime, $theTime);
        $offset = $transition[0]['offset'];
        return $offset;
    }

    protected function _fixTime($filters)
    {
        $filters['DateFrom'] = date('d/m/Y',strtotime($this->_phpDateToMysqlFrom($filters['DateFrom'])));
        $filters['DateTo'] = date('d/m/Y',strtotime($this->_phpDateToMysqlFrom($filters['DateTo'])));
        return $filters;
    }

    abstract protected function _getSelectedFields($filters);
    abstract public function getReport($filter);
    abstract public function getReportFields();
}