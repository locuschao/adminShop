<?php
namespace app\api\controller;

use think\Controller;


class Index extends Controller
{
    function index()
    {
        echo time();
    }
}