<?php
namespace TestApp\Controller;
use Phly\Http\Request;

class Index
{
    public function index($request, $response)
    {
        return $response;
    }

    public function failed($request, $response)
    {
        return $response->withStatus(205);
    }
}
