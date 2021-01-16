<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$campaignId = $_GET["campaignId"];
$status = $_GET["status"];

if (!empty($campaignId) && is_numeric($campaignId)) {
    $status = $status ? : true;
    $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

    if (!empty($campaign)) {
        $campaign->setIsWaiting($status)->save();

        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'change campaign status successfully!', "campaignId" => $campaignId, "status" => $status));
        return;
    }
}

http_response_code(400);
echo json_encode(array('status_code' => 400, 'message' => 'bad request, campaignId is not valid', "campaignId" => $campaignId, "status" => $status));
return;
