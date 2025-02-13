<?php
session_start();

require_once __DIR__ . '/../classes/ApiRequest.php';

$middleware = [
    function ($req) {
        $action = $_POST['action'];
        switch ($action) {
            case 'list-dir':
                //TODO
                break;
            case 'upload-file':
                //TODO
                break;
            case 'delete-file':
                //TODO
                break;
        }
    }
];

$apiRequest = new ApiRequest($middleware);

$result = $apiRequest->execute();
echo json_encode($result);