<?php

namespace JOOservices\XClient\Response;

use Psr\Http\Message\ResponseInterface;

class Json implements \JOOservices\XClient\Interfaces\ResponseInterface
{
    public function __construct(private ResponseInterface $response)
    {
        $this->data = json_decode($response->getBody()->getContents());
    }

    public function isSuccessful(): bool
    {
        return $this->response->isSuccessful();
    }

    public function getData()
    {
        return $this->data;
    }
}