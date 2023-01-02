# XClient
Wrapped for GuzzleHttp Client

`Factory` used for making Client with wrapped middlewares
- Cache
- Mocking for Tests

`XCrawlerClient` is `Factory` wrapper with advanced thing
- Parse `response` ( `ResponseInterface` )

Here are few implementation of `ResponseInterface`
- `DomResponse` automatically concert to DomCrawler
- `JsonResponse` automatically convert json to array
- `FlickrResponse` specific for Flickr json
- `Now` specific for NowService

## How to work with cache

        $local = new FlysystemStorage(new Local(__DIR__ . '/cache'));
        $url = $this->faker->url;
        $cache = new CacheMiddleware(
            new PrivateCacheStrategy(
                $local
            )
        );

        $factory = new Factory($this->logger, 200);

## How to work with mocks

Provide 4xx - 5xx for Error case

`$factory = new Factory($this->logger, 200);`

## TODO
- Support OAuth 1/2
- Support Flickr

