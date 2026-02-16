<?php

declare(strict_types=1);

namespace AutoApi\Tests;

use AutoApi\Client;
use AutoApi\Exception\ApiException;
use AutoApi\Exception\AuthException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ClientTest extends TestCase
{
    /** @var array<int, array{request: \Psr7\Http\Message\RequestInterface}> */
    private array $history = [];

    private function createClient(array $responses, string $apiKey = 'test-key', string $apiVersion = 'v2'): Client
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));

        $httpClient = new HttpClient([
            'handler' => $stack,
            'base_uri' => 'https://api1.auto-api.com/',
        ]);

        $client = new Client($apiKey, apiVersion: $apiVersion);

        // Replace internal HTTP client with mocked one via reflection
        $reflection = new ReflectionClass($client);
        $property = $reflection->getProperty('http');
        $property->setValue($client, $httpClient);

        return $client;
    }

    private function lastRequestUri(): string
    {
        return (string) $this->history[count($this->history) - 1]['request']->getUri();
    }

    private function lastRequest(): \Psr\Http\Message\RequestInterface
    {
        return $this->history[count($this->history) - 1]['request'];
    }

    // ── getFilters ──────────────────────────────────────────────

    public function testGetFilters(): void
    {
        $expected = [
            'brands' => ['Toyota', 'Honda'],
            'body_types' => ['sedan', 'suv'],
        ];

        $client = $this->createClient([
            new Response(200, [], json_encode($expected)),
        ]);

        $result = $client->getFilters('encar');

        $this->assertSame($expected, $result);
        $this->assertStringContainsString('/api/v2/encar/filters', $this->lastRequestUri());
    }

    public function testGetFiltersIncludesApiKey(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{}'),
        ], apiKey: 'my-secret-key');

        $client->getFilters('encar');

        $this->assertStringContainsString('api_key=my-secret-key', $this->lastRequestUri());
    }

    // ── getOffers ───────────────────────────────────────────────

    public function testGetOffers(): void
    {
        $expected = [
            'data' => [['id' => 1], ['id' => 2]],
            'total' => 100,
        ];

        $client = $this->createClient([
            new Response(200, [], json_encode($expected)),
        ]);

        $result = $client->getOffers('encar', ['page' => 1]);

        $this->assertSame($expected, $result);
        $this->assertStringContainsString('page=1', $this->lastRequestUri());
    }

    public function testGetOffersWithFilters(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{"data": []}'),
        ]);

        $client->getOffers('mobile_de', [
            'page' => 2,
            'brand' => 'BMW',
            'year_from' => 2020,
            'price_to' => 50000,
        ]);

        $uri = $this->lastRequestUri();
        $this->assertStringContainsString('brand=BMW', $uri);
        $this->assertStringContainsString('year_from=2020', $uri);
        $this->assertStringContainsString('price_to=50000', $uri);
        $this->assertStringContainsString('page=2', $uri);
    }

    // ── getOffer ────────────────────────────────────────────────

    public function testGetOffer(): void
    {
        $expected = ['inner_id' => 'abc123', 'brand' => 'Toyota', 'price' => 25000];

        $client = $this->createClient([
            new Response(200, [], json_encode($expected)),
        ]);

        $result = $client->getOffer('encar', 'abc123');

        $this->assertSame($expected, $result);
        $this->assertStringContainsString('inner_id=abc123', $this->lastRequestUri());
    }

    // ── getChangeId ─────────────────────────────────────────────

    public function testGetChangeId(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{"change_id": 42567}'),
        ]);

        $result = $client->getChangeId('encar', '2024-01-15');

        $this->assertSame(42567, $result);
        $this->assertStringContainsString('date=2024-01-15', $this->lastRequestUri());
    }

    public function testGetChangeIdReturnsInteger(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{"change_id": 0}'),
        ]);

        $result = $client->getChangeId('encar', '2024-01-01');

        $this->assertIsInt($result);
        $this->assertSame(0, $result);
    }

    // ── getChanges ──────────────────────────────────────────────

    public function testGetChanges(): void
    {
        $expected = [
            'added' => [['id' => 'new1']],
            'changed' => [['id' => 'upd1']],
            'removed' => ['del1'],
        ];

        $client = $this->createClient([
            new Response(200, [], json_encode($expected)),
        ]);

        $result = $client->getChanges('encar', 42567);

        $this->assertSame($expected, $result);
        $this->assertStringContainsString('change_id=42567', $this->lastRequestUri());
    }

    // ── getOfferByUrl ───────────────────────────────────────────

    public function testGetOfferByUrl(): void
    {
        $expected = ['brand' => 'BMW', 'model' => 'X5', 'price' => 45000];

        $client = $this->createClient([
            new Response(200, [], json_encode($expected)),
        ]);

        $result = $client->getOfferByUrl('https://www.encar.com/dc/dc_cardetailview.do?pageid=1234');

        $this->assertSame($expected, $result);
    }

    public function testGetOfferByUrlUsesPost(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{}'),
        ]);

        $client->getOfferByUrl('https://example.com/car/123');

        $request = $this->lastRequest();
        $this->assertSame('POST', $request->getMethod());
    }

    public function testGetOfferByUrlUsesV1Endpoint(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{}'),
        ]);

        $client->getOfferByUrl('https://example.com/car/123');

        $this->assertStringContainsString('/api/v1/offer/info', $this->lastRequestUri());
    }

    public function testGetOfferByUrlSendsApiKeyInHeader(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{}'),
        ], apiKey: 'header-key');

        $client->getOfferByUrl('https://example.com/car/123');

        $request = $this->lastRequest();
        $this->assertSame('header-key', $request->getHeaderLine('x-api-key'));
    }

    public function testGetOfferByUrlSendsUrlInBody(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{}'),
        ]);

        $client->getOfferByUrl('https://example.com/car/123');

        $body = json_decode($this->lastRequest()->getBody()->getContents(), true);
        $this->assertSame('https://example.com/car/123', $body['url']);
    }

    // ── API version ─────────────────────────────────────────────

    public function testCustomApiVersion(): void
    {
        $client = $this->createClient([
            new Response(200, [], '{}'),
        ], apiVersion: 'v3');

        $client->getFilters('encar');

        $this->assertStringContainsString('/api/v3/encar/filters', $this->lastRequestUri());
    }

    // ── Error handling ──────────────────────────────────────────

    public function testThrowsApiExceptionOnServerError(): void
    {
        $client = $this->createClient([
            new Response(500, [], '{"message": "Internal server error"}'),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal server error');

        $client->getFilters('encar');
    }

    public function testApiExceptionContainsStatusCode(): void
    {
        $client = $this->createClient([
            new Response(500, [], '{"message": "Server error"}'),
        ]);

        try {
            $client->getFilters('encar');
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
        }
    }

    public function testApiExceptionContainsResponseBody(): void
    {
        $client = $this->createClient([
            new Response(422, [], '{"message": "Validation failed", "errors": ["invalid page"]}'),
        ]);

        try {
            $client->getFilters('encar');
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $body = $e->getResponseBody();
            $this->assertIsArray($body);
            $this->assertSame('Validation failed', $body['message']);
        }
    }

    public function testThrowsAuthExceptionOn401(): void
    {
        $client = $this->createClient([
            new Response(401, [], '{"message": "Unauthorized"}'),
        ]);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Unauthorized');

        $client->getFilters('encar');
    }

    public function testThrowsAuthExceptionOn403(): void
    {
        $client = $this->createClient([
            new Response(403, [], '{"message": "Forbidden"}'),
        ]);

        $this->expectException(AuthException::class);

        $client->getOffers('encar');
    }

    public function testAuthExceptionIsAlsoApiException(): void
    {
        $client = $this->createClient([
            new Response(401, [], '{"message": "Bad key"}'),
        ]);

        $this->expectException(ApiException::class);

        $client->getFilters('encar');
    }

    public function testThrowsApiExceptionOnInvalidJson(): void
    {
        $client = $this->createClient([
            new Response(200, [], 'not json at all'),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $client->getFilters('encar');
    }

    public function testThrowsApiExceptionOnNotFoundError(): void
    {
        $client = $this->createClient([
            new Response(404, [], '{"message": "Source not found"}'),
        ]);

        try {
            $client->getFilters('unknown_source');
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame(404, $e->getStatusCode());
            $this->assertNotInstanceOf(AuthException::class, $e);
        }
    }
}
