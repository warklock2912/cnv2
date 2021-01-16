<?php

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiSm9obiBEb2UiLCJlbWFpbCI6ImxpbmhAbWFpbC5jb20iLCJqdGkiOiJPam1lcktkamVqd2s0IiwiaWF0IjoxNTE2MjM5MDIyfQ.2L8sk9TbckUgDIpj0OluRLHCQd-Bccq5e_7nrwXtPT0';
http_response_code(200);
echo json_encode(array(
	'jwt' => $token
));
