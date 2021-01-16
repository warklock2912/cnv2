<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if($data['token']):
$url = 'https://fcm.googleapis.com/fcm/send';
$id = $data['token'];
$message = 'blog';
$fields = array(
	'registration_ids' => array(
		$id
	),
    'priority'=> "high",
    'content_available'=> true,
);

$fields['notification'] = $data['notification'];
$fields['data'] = $data['data'];
$fields = json_encode($fields);
$key = 'AAAAp09xu5s:APA91bFos6IfclepQZVk99S4nAbdwb2VuzRjocXIjUnVlSsDB4UWpjoQf7BOgp9mxBdvGKsQaQZZzneua6JJOZZiyRneTxKcycelB8RNORI93InyFqFQSYiD8h4DLjcRw34ncpoEgAzs';
$headers = array(
	'Authorization: key=' . $key,
	'Content-Type: application/json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

$result = curl_exec($ch);
dataResponse(200,'success',$result) ;
curl_close($ch);
else:
    dataResponse(400,'missing param token');
endif;