<?php

namespace JOOservices\XClient\Response;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

class Dom implements \JOOservices\XClient\Interfaces\ResponseInterface
{
    private Crawler $data;

    public function __construct(private ResponseInterface $response)
    {
        $this->data = new Crawler($response->getBody()->getContents());
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