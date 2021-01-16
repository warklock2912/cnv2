<?php
class Mage_P2c2p_Block_Form_P2c2p_Onsiteinternetbanking extends Mage_Payment_Block_Form
{

    /**
     * Preparing global layout
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     *
     * @see    Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('p2c2p/onsiteinternetbanking.phtml');
        return $this;
    }
    protected function getChannels()
    {
        $channelConfigs = $this->_convertCsvToArray(Mage::getStoreConfig('payment/p2c2p_onsite_internet_banking/channels', Mage::app()->getStore()));

        $channels = array();

        foreach ($channelConfigs as $item) {
            $channels[$item['group_name']][$item['service_id']] = array(
                'service_id' => $item['service_id'],
                'group_code' => $item['group_code'],
                'group_name' => $item['group_name'],
                'service_code' => $item['service_code'],
                'service_name' => $item['service_name'],
                'service_image' => $item['service_image']
            );
        }

        $optimizedChannels = array();
        foreach ($channels as $groupName => $group) {
            $channelsForGroup = array();
            foreach ($group as $channelId => $channelData) {
                $channelsForGroup[] = $channelData;
            }
            $optimizedChannels[] = array(
                'group_name' => $groupName,
                'items' => $channelsForGroup
            );
        }

        return $optimizedChannels;
    }
    protected function _convertCsvToArray($string = '', $delimiter = ',', $addHeader = true)
    {
        $enclosure = '"';
        $escape = "\\";

        $rows = array_filter(preg_split('/\r*\n+|\r+/', $string));

        $data = array();
        if ($addHeader) {
            $header = array_shift($rows);
            $header = str_getcsv($header, $delimiter, $enclosure, $escape);

            foreach ($rows as $row) {
                $row = str_getcsv($row, $delimiter, $enclosure, $escape);
                $data[] = array_combine($header, $row);
            }
        } else {
            foreach ($rows as $row) {
                $data[] = str_getcsv($row, $delimiter, $enclosure, $escape);
            }
        }

        return $data;
    }


    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent(
            'payment_form_block_to_html_before',
            array('block' => $this)
        );

        return parent::_toHtml();
    }
}
