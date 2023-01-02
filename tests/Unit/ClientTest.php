<?php

namespace JOOservices\XClient\Tests\Unit;

use JOOservices\XClient\Client;
use JOOservices\XClient\Response\Json;
use JOOservices\XClient\Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_client(){
        $client = new Client();
        $client->init();

        $response = $client->get('https://filesamples.com/samples/code/json/sample2.json');
    }
}