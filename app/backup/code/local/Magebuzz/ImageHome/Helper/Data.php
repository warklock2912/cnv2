<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Helper_Data extends Mage_Core_Helper_Abstract {

    function getImagehome($id) {
        $Imagehomes = Mage::getModel('imagehome/imagehome')->load($id);
        return $Imagehomes;
    }

    function getImagehomes() {
        $Imagehomes = Mage::getModel('imagehome/imagehome')->getCollection()->getFirstItem();

        return $Imagehomes;
    }

}
