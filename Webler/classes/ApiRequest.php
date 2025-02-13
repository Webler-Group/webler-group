<?php

class ApiRequest
{
    private $middleware;

    public static function validatePostFields($requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                return false;
            }
        }
        return true;
    }

    public function __construct($middleware)
    {
        $this->middleware = $middleware;
    }

    public function execute()
    {
        $result = [
            'success' => true
        ];

        $req = new stdClass();

        foreach ($this->middleware as $fn) {
            if (!$fn($req)) {
                $result['success'] = false;
                if (isset($req->message)) {
                    $result['message'] = $req->message;
                }
                break;
            }
        }

        if (isset($req->data)) {
            $result['data'] = $req->data;
        }

        return $result;
    }
}
