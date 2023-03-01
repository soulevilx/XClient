<?php

namespace JOOservices\XClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\RedirectMiddleware;
use JOOservices\XClient\Interfaces\ClientInterface;
use JOOservices\XClient\Response\Response;


class XClient implements ClientInterface
{

    protected Client $client;
    protected Response $response;
    protected Factory $factory;
    protected array $headers;
    protected string $contentType;

    public function __construct(protected array $options = [], protected array $requestOptions = [])
    {
        $this->options = array_merge([
            'maxRetries' => 3,
            'delayInSec' => 1,
            'minErrorCode' => 500,
            'logger' => [
                'instance' => null,
                'formatter' => null
            ],
            'caching' => [
                'instance' => null
            ],
        ], $options);
        $this->requestOptions = array_merge([
            'allow_redirects' => RedirectMiddleware::$defaultSettings,
            'http_errors' => true,
            'decode_content' => true,
            'verify' => true,
            'cookies' => false,
            'idn_conversion' => false,
        ], $requestOptions);
    }

    public function init(array $options = [], array $requestOptions = []): self
    {
        $this->setOptions($options);
        $this->setRequestOptions($requestOptions);

        $this->factory = new Factory($options['isFake'] ?? null);
        $this->factory
            ->enableRetries($this->options['maxRetries'], $this->options['delayInSec'], $this->options['minErrorCode'])
            ->addOptions($this->options);

        if ($this->options['logger']['instance']) {
            $this->factory->enableLogging($this->options['logger']['formatter'] ?? MessageFormatter::SHORT);
        }

        if ($this->options['caching']['instance']) {
            $this->factory->enableCache($this->options['caching']['instance']);
        }

        /**
         * Client inited w/ options
         */
        $this->client = $this->factory->make();

        return $this;
    }

    /**
     * Get the Response
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Set the headers
     * @param  array  $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers ?? [], $headers);

        return $this;
    }

    /**
     * Set Client options
     * @param  array  $requestOptions
     * @return $this
     */
    public function setRequestOptions(array $requestOptions): self
    {
        $this->requestOptions = array_merge($this->requestOptions, $requestOptions);

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Set the content type
     *
     * @param  string  $contentType
     *
     * @return $this
     */
    public function setContentType(string $contentType = 'json'): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * GET Request
     *
     * @param  string  $endpoint
     * @param  array  $payload
     * @return Response
     */
    public function get(string $endpoint, array $payload = []): Response
    {
        return $this->request($endpoint, $payload);
    }

    /**
     * POST Request
     *
     * @param  string  $endpoint
     * @param  array  $payload
     * @return Response
     */
    public function post(string $endpoint, array $payload = []): Response
    {
        return $this->request($endpoint, $payload, 'POST');
    }

    /**
     * PUT Request
     *
     * @param  string  $endpoint
     * @param  array  $payload
     * @return Response
     */
    public function put(string $endpoint, array $payload = []): Response
    {
        return $this->request($endpoint, $payload, 'PUT');
    }

    /**
     * PATCH Request
     *
     * @param  string  $endpoint
     * @param  array  $payload
     * @return Response
     */
    public function patch(string $endpoint, array $payload = []): Response
    {
        return $this->request($endpoint, $payload, 'PATCH');
    }

    /**
     * DELETE Request
     *
     * @param  string  $endpoint
     * @param  array  $payload
     * @return Response
     */
    public function delete(string $endpoint, array $payload = []): Response
    {
        return $this->request($endpoint, $payload, 'DELETE');
    }

    /**
     * Perform the request
     *
     * @param  string  $endpoint
     * @param  array  $payload
     * @param  string  $method
     * @return Response
     */
    protected function request(string $endpoint, array $payload = [], string $method = 'GET')
    {
        /**
         * Request options
         */
        $requestOptions = array_merge($this->requestOptions, ['headers' => $this->headers ?? []]);

        $payload = $this->convertToUTF8($payload);

        if ($method == 'GET') {
            $requestOptions['query'] = $payload;
        } else {
            switch ($this->contentType) {
                case 'application/x-www-form-urlencoded':
                    $requestOptions['form_params'] = $payload;
                    break;
                default:
                case 'json':
                    $requestOptions['json'] = $payload;
                    break;
            }
        }

        $returnResponse = new Response();
        try {
            $response = $this->client->request($method, $endpoint, $requestOptions);
            $returnResponse->reset(
                $response->getStatusCode(),
                $response->getHeaders(),
                $response->getBody(),
                $response->getProtocolVersion(),
                $response->getReasonPhrase()
            );
        } catch (GuzzleException|ClientException) {
            $returnResponse->reset(0);
            $returnResponse->isSucceed = false;
        } finally {
            return $returnResponse;
        }
    }

    /**
     * Sanitize payload to UTF-8
     *
     * @param  array  $array
     *
     * @return array
     */
    protected function convertToUTF8(array $array): array
    {
        array_walk_recursive($array, function (&$item) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return $array;
    }
}