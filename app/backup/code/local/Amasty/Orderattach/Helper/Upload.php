<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattach
 */
class Amasty_Orderattach_Helper_Upload extends Mage_Core_Helper_Abstract
{
    public function getUploadUrl()
    {
        $url = Mage::getBaseUrl('media') . 'attachments/';
        return $url;
    }
    
    public function getUploadDir()
    {
        $dir = Mage::getBaseDir('media') . '/attachments/';
        return $dir;
    }
    
    public function cleanFileName($fileName)
    {
        return preg_replace('/[^a-zA-Z0-9_\.]/', '', $fileName);
    }

    public function uploadFile(&$someField, $code)
    {
        $multiple = is_array($_FILES['to_upload']['error']);
        for ($i = 0; $i < sizeof($_FILES['to_upload']['error']); $i++) {
            $error = $multiple ? $_FILES['to_upload']['error'][$i] : $_FILES['to_upload']['error'];

            if ($error == UPLOAD_ERR_OK) {
                $fileName = $multiple ? $_FILES['to_upload']['name'][$i] : $_FILES['to_upload']['name'];
                $fileName = Mage::helper('amorderattach/upload')->cleanFileName($fileName);
                $uploader = new Amasty_Orderattach_Model_Uploader($multiple ? "to_upload[$i]" : 'to_upload');
                $uploader->setFilesDispersion(false);
                $uploader->setAllowRenameFiles(true);
                $uploader->save(Mage::helper('amorderattach/upload')->getUploadDir(), $fileName);
                if ('file' == Mage::app()->getRequest()->getPost('type')) // each new overwrites old one
                {
                    $someField->setData($code, $fileName);
                }
                if ('file_multiple' == Mage::app()->getRequest()->getPost('type')) {
                    $fieldData   = explode(';', $someField->getData($code));
                    $fieldData[] = $fileName;
                    $fieldData   = implode(';', $fieldData);
                    $someField->setData($code, $fieldData);
                }
            }
        }
    }
}
