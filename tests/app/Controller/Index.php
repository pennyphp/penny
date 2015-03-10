<?php
namespace TestApp\Controller;

class Index
{
    public function index()
    {
        return json_encode(["ping" => "yes"]);
    }
}
