<?php

/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Blockdatacustom extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getBannerads()
    {

        $blockId = $this->getBlockBannerId();

        $imageModel = Mage::getModel('bannerads/images');
        $blockModel = Mage::getResourceModel('bannerads/bannerads');
        $blockData = Mage::getModel('bannerads/bannerads')->load($blockId);
        if (in_array($blockId, $this->getBlockIds())) {
            $blockImage = $blockModel->lookupImagesId($blockId);
            if ($blockData->getDisplayType() == 2) {
                $images = $imageModel->load($blockImage[array_rand($blockImage, 1)]);
            } else {
                $images = $imageModel->getCollection()
                    ->addFieldToFilter('banner_id', array('in' => $blockImage))->addFieldToFilter('status', 1)
                    ->addFieldtoFilter('start_time',
                        array(
                            array('to' => Mage::getModel('core/date')->gmtDate()),
                            array('start_time', 'null' => '')
                        )
                    )
                    ->addFieldtoFilter('end_time',
                        array(
                            array('gteq' => Mage::getModel('core/date')->gmtDate()),
                            array('end_time', 'null' => '')
                        )
                    )
                    ->setOrder('sort_order', "ASC");
            }
            $blockData->setImages($images);

        } else {
            $blockData->setImages(null);

        }
        return $blockData;
    }

    public function getBlockIds()
    {
        $collection = Mage::getModel('bannerads/bannerads')->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldtoFilter('from_date',
                array(
                    array('to' => Mage::getModel('core/date')->gmtDate()),
                    array('from_date', 'null' => '')
                )
            )
            ->addFieldtoFilter('to_date',
                array(
                    array('gteq' => Mage::getModel('core/date')->gmtDate()),
                    array('to_date', 'null' => '')
                )
            );

        $blockIds = array();
        foreach ($collection as $item) {
            $blockIds[] = $item->getBlockId();
        }

        return $blockIds;

    }


}
