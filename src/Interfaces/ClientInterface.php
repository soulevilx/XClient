<?php

namespace JOOservices\XClient\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    public function init(array $options = [], array $requestOptions = []): self;

    public function getResponse(): ResponseInterface;

    public function setHeaders(array $headers): self;

    public function setContentType(string $contentType = 'json'): self;

    public function get(string $endpoint, array $payload = []): ResponseInterface;

    public function post(string $endpoint, array $payload = []): ResponseInterface;

    public function put(string $endpoint, array $payload = []): ResponseInterface;

    public function patch(string $endpoint, array $payload = []): ResponseInterface;

    public function delete(string $endpoint, array $payload = []): ResponseInterface;

}