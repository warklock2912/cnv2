<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_ReportsController extends Mage_Core_Controller_Front_Action {
  public function impressionAction() {
    $bannerIds = $this->getRequest()->getParam("banner_ids");
    $bannerIdsArray = explode(",", $bannerIds);
    $blockId = $this->getRequest()->getParam("block_id");
    $date = date('Y-m-d');
    $report = Mage::getModel('bannerads/reports');
    if ($blockId && $date) {
      $collection = $report->getCollection();
      $collection->addFieldToFilter('date', $date)->addFieldToFilter('block_id', $blockId)->addFieldToFilter('banner_id', array('in' => $bannerIdsArray));
      if (count($collection->getData())) {
        $bannerIdsCache = array();
        foreach ($collection as $item) {
          $item->setData('impression', $item->getData('impression') + 1);
          try {
            $item->save();
            $bannerIdsCache[] = $item->getBannerId();
          } catch (Exception $e) {

          }
        }
        $otherBannerIds = array_diff($bannerIdsArray, $bannerIdsCache);
        if (count($otherBannerIds)) {

          foreach ($otherBannerIds as $bid) {

            $reportBanner = Mage::getModel('bannerads/reports');
            $reportBanner->setData('banner_id', $bid)->setData('block_id', $blockId)->setData('impression', $reportBanner->getData('impression') + 1)->setData('date', $date);
            try {
              $reportBanner->save();
            } catch (Exception $e) {

            }
          }
        }
      } else {
        if (count($bannerIds) && isset($bannerIds)) {
          foreach ($bannerIdsArray as $banner_id) {
            $reportBanner = Mage::getModel('bannerads/reports');
            $reportBanner->setData('banner_id', $banner_id)->setData('block_id', $blockId)->setData('impression', $reportBanner->getData('impression') + 1)->setData('date', $date);
            try {
              $reportBanner->save();
            } catch (Exception $e) {
            }
          }
        }
      }
      // }
    }
  }

  public function clickAction() {
    $id = $this->getRequest()->getParam('banner_id');
    $blockId = $this->getRequest()->getParam('block_id');
    $date = date('Y-m-d');
    $report = Mage::getModel('bannerads/reports');
    $collection = $report->getCollection();
    if ($id && $blockId) {
      $collection->addFieldToFilter('banner_id', $id)->addFieldToFilter('block_id', $blockId)->addFieldToFilter('date', $date);
      if (count($collection)) {
        foreach ($collection as $item) {
          $item->setData('clicks', $item->getData('clicks') + 1);
          $item->save();
        }
      } else {
        $report->setData('banner_id', $id)->setData('block_id', $blockId)->setData('date', $date)->setData('clicks', $report->getData('clicks') + 1);
        try {
          $report->save();
        } catch (Exception $e) {
        }
      }
    }
  }

  private function getUserCode($id) {
    $ipAddress = null;
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $ipAddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
      $ipAddress = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (isset($_SERVER["REMOTE_ADDR"])) {
      $ipAddress = $_SERVER["REMOTE_ADDR"];
    }

    $cookiefrontend = $_COOKIE['frontend'];
    $usercode = $ipAddress . $cookiefrontend . $id;
    return md5($usercode);
  }

}