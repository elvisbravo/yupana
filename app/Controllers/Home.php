<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home/home');
    }

    public function webPage(): string
    {
        return view('web/webpage');
    }
}
