<?php

namespace JOOservices\XClient\Interfaces;

interface ResponseInterface
{
    public function isSuccessful(): bool;
    public function getData();
}