<?php
    require_once '../../app/Mage.php';
    require_once '../functions.php';
    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $device = Mage::getModel('pushnotification/device')->load($data['id']);
    try {
        $device->delete();
        dataResponse(200, 'valid');

    } catch (Exception $e) {
        dataResponse(400, $e->getMessage());
    }
