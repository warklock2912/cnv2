<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function saveFile()
    {
        parent::saveFile();

        if (!Mage::getStoreConfigFlag('amoptimization/images/optimize_previews'))
            return $this;

        $fileName = $this->getNewFile();
        $realName = realpath($fileName);

        switch (substr($realName, -4)) {
            case '.png':
                $command = Mage::getStoreConfig('amoptimization/images/png_cmd');
                break;
            case '.jpg':
            case '.jpeg':
                $command = Mage::getStoreConfig('amoptimization/images/jpeg_cmd');
                break;
            case '.gif':
                $command = Mage::getStoreConfig('amoptimization/images/gif_cmd');
                break;
            default:
                $command = false;
        }

        if ($command) {
            $command = str_replace('%f', "'$realName'", $command, $count);

            if ($count > 0) {
                exec($command);
            }
        }

        return $this;
    }
}
