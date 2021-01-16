<?php

require_once '../app/Mage.php';
require_once 'functions.php';

try {
    $result = Mage::getModel('catalog/category_attribute_source_sortby')->getAllOptions();
    http_response_code(200);
    echo json_encode(array('status_code' => 200, 'message' => 'valid', 'data' => $result));
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
}