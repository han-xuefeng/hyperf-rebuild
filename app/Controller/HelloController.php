<?php


namespace App\Controller;


class HelloController
{

    public function index()
    {
        return 'this is a index';
    }

    public function test(): string
    {
        return 'test';
    }
    public function demo(): string
    {
        return 'demo';
    }
    public function test2(): string
    {
        return 'test2';
    }

}